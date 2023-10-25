<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class TaskController extends AbstractController
{
    #[Route('/moje-zlecenia', name: 'app_created_tasks')]
    public function createdTasks(#[CurrentUser] $user): Response
    {
        return $this->render(
            'task/task-calendar.html.twig',
            [
                'destination' => 'created',
                'adminTasks' => []
            ]
        );
    }

    #[Route('/lista-zlecen', name: 'app_responsible_tasks')]
    public function responsibleTasks(#[CurrentUser] $user, EntityManagerInterface $em): Response
    {
        $qb = $em->getRepository(Task::class)->createQueryBuilder('t');

        $adminTasks = $qb
            ->leftJoin('t.responsible', 'u')
            ->where($qb->expr()->isNotNull('u.id'))
            ->getQuery()
            ->getResult();
        
        return $this->render(
            'task/task-calendar.html.twig',
            [
                'destination' => 'responsible',
                'adminTasks' => $adminTasks
            ]
        );
    }

    #[Route('/dodaj-zlecenie', name: 'app_add_task')]
    public function addTask(#[CurrentUser] $user, Request $request, EntityManagerInterface $em): Response
    {
        $usersFormatted = [];
        $users = $em->getRepository(User::Class)->findAll();

        foreach ($users as $user)
        {
            $usersFormatted[$user->getId()] = $user->getName() . ' ' . $user->getSurname();
        }

        $task = new Task();
        $form = $this->createForm(
            TaskFormType::class,
            $task
        );
        $form->remove('duration');
        $form->remove('responsible');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $task->setCreator($user);
            $em->persist($task);
            $em->flush();
        }

        return $this->render(
            'task/add-task.html.twig', [
                'taskForm' => $form
            ]
        );
    }

    #[Route('/zmiana-czasu-zlecenia/{id}/{start}', name: 'app_delete_task')]
    public function changeTime(int $id, int $start, #[CurrentUser] $user, Request $request, EntityManagerInterface $em): Response
    {
        $task = $em->getRepository(Task::class)->find($id);
        $task->setStartTime(new \DateTime('@' . $start/1000));
        $em->persist($task);
        $em->flush();

        return new Response($id, 200);
    }
}
