<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class PostController extends AbstractController
{
	private $em;

	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
	}


    #[Route('/', name: 'app_post')]
    public function index(Request $request, SluggerInterface $slugger, string $files_directoy = 'files_directoy'): Response
    {
		$post = new Post();
		$form = $this->createForm(PostType::class, $post);
		$form->handleRequest($request);
		$all_posts = $this->em->getRepository(Post::class)->findAllPost();
		if ($form->isSubmitted() && $form->isValid())
		{
			$user = $this->em->getRepository(User::class)->find(1);
			$post->setUser($user);
			$file = $form->get('file')->getData();
			if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $file->move($files_directoy, $newFilename);
                } catch (FileException $e) {
                    throw new \Exception('Ups There is a problem with your file');
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $post->setFile($newFilename);
            }
			$this->em->persist($post);
			$this->em->flush();
			return $this->redirectToRoute('app_post');
		}
        return $this->render('post/index.html.twig', [
			'form' => $form->createView(),
			'posts' => $all_posts
        ]);
    }
}
