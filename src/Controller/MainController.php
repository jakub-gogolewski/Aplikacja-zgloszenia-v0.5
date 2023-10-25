<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(#[CurrentUser] $user, MailerInterface $mailer): Response
    {
		if ($user?->isVerified())
			return $this->render("task/task-calendar.html.twig", ['destination' => 'created']);
		return $this->render("main/index.html.twig");
    }
}
