<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
	private $em;

	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
	}

    #[Route('/registration', name: 'App_Registration')]
    public function userRegistration(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
		$user = new User();
		$user_form = $this->createForm(UserType::class, $user);
		$user_form->handleRequest($request);;
		if ($user_form->isSubmitted() && $user_form->isValid())
		{
			$user->setRoles(['ROLE_USER']);
			$plaintextPassword = $user_form->get('password')->getData();
			$hashedPassword = $passwordHasher->hashPassword(
				$user,
				$plaintextPassword
			);
			$user->setPassword($hashedPassword);
			$this->em->persist($user);
			$this->em->flush();
			return $this->redirectToRoute('App_Registration');
		}

        return $this->render('user/index.html.twig', ['userForm' => $user_form->createView()
		]);
    }
}
