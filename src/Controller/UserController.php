<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginFormType;
use App\Form\RegistrationFormType;
use App\Security\LoginAuthenticator;
use App\Form\ChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;


class UserController extends AbstractController
{

	private $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getCurrentRequest()->getSession();
    }

	#[Route('/moj-profil', name: 'app_my_profile')]
	public function my_profile(#[CurrentUser] $user, Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
	{	

		$form = $this->createForm(ChangePasswordFormType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$user->setPassword(
				$passwordHasher->hashPassword(
					$user,
					$form->get('plainPassword')->getData()
				)
			);

			$em->persist($user);
			$em->flush();

			$this->session->getFlashBag()->add('notice', 'Hasło pomyślnie zmieniono!');

			return $this->render('user/my-profile.html.twig', [
				'resetForm' => $form
			]);
		}

		return $this->render('user/my-profile.html.twig', [
			'resetForm' => $form
        ]);
	}

	#[Route('/lista-klientow', name: 'app_client_list')]
	public function clientList(EntityManagerInterface $em): Response
	{
        $query = $em
			->getRepository(User::Class)
			->createQueryBuilder('u')
			->where('u.roles LIKE :role')
			->setParameter('role', '%"Klient"%')
			->getQuery();

		$users = $query->getResult();

		return $this->render('user/client-list.html.twig', [
            'users' => $users
        ]);
	}

	#[Route('/dodaj-klienta', name: 'app_client_add')]
	#[Route('/dodaj-pracownika', name: 'app_employee_add')]
	public function addUser(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, LoginAuthenticator $authenticator, EntityManagerInterface $entityManager, AuthenticationUtils $authenticationUtils, MailerInterface $mailer): Response
    {
		$response = false;
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);

		$form->remove('plainPassword'); //setRequired(false);
		$form->remove('agreeTerms');

		if ($request->getPathInfo() == '/dodaj-klienta')
			$form->remove('roles');

        $form->handleRequest($request);

		$random = new \Random\Randomizer();
		$password = base64_encode($random->getBytes(12));
        
		if ($form->isSubmitted() && $form->isValid())
		{
            $user->setIsVerified(true);
			$user->setRoles(
				$form->has('roles')
				? [$form->get('roles')->getData()]
				: ['Klient']
			);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $password
                )
            );

            $entityManager->persist($user);

			$response = 'Dodano użytkownika';
			try {
            	$entityManager->flush();
			}
			catch (\Throwable $e)
			{
				$response = 'Nie udało się dodać użytkownika';
			}

			$email = (new Email())
				->to($user->getEmail())
				->subject('Nowe konto')
				->html('Na Twój email zostało założone konto w serwisie LB Solutions. Twoje losowo wygenerewone hasło to: <b>' . $password);

			$mailer->send($email);
		}

        return $this->render(
			'user/add-employee-client.html.twig',
			[
				'registrationForm' => $form->createView(),
				'destination' => '/register',
				'who' => match($request->getPathInfo()){
					'/dodaj-klienta' => 'klienta',
					'/dodaj-pracownika' => 'pracownika',
					default => 'użytkownika',
				},
				'response' => $response
			]
		);
    }

	#[Route('/lista-pracownikow', name: 'app_employee_list')]
	public function employeeList(EntityManagerInterface $em): Response
	{
        $query = $em
			->getRepository(User::Class)
			->createQueryBuilder('u')
			->where('u.roles NOT LIKE :role')
			->setParameter('role', '%"Klient"%')
			->getQuery();

		$users = $query->getResult();

		return $this->render('user/client-list.html.twig', [
            'users' => $users
        ]);
	}

	#[Route('/api/user/edit/{id}')]
	public function editUserApi($id,Request $request, EntityManagerInterface $em)
	{
		$post = $request->request;
		$user = $em->getRepository(User::Class)->find($id);

		if ($post->get('name', false))
			$user->setName($post->get('name'));
		
		if ($post->get('surname', false))
            $user->setSurname($post->get('surname'));

		if ($post->get('email', false))
            $user->setEmail($post->get('email'));

		if ($post->get('phoneNumber', false))
            $user->setPhoneNumber($post->get('phoneNumber'));
		
		$em->persist($user);
		$em->flush();

		return new Response($id, 200);
	}

	#[Route('/api/user/remove/{id}')]
    public function removeUserApi(int $id, Request $request, EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
		$user = $em->getRepository(User::Class)->find($id);
		
		if ($user === null)
			return new Response('error', 200);


			$em->remove($user);
			$em->flush();


		return new Response($id, 200);
	}
}

