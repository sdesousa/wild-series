<?php
// src/Controller/WildController.php
namespace App\Controller;

use App\Entity\Actor;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use App\Form\CategoryType;
use App\Form\CommentType;
use App\Form\ProgramSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wild", name="wild_")
 */
Class WildController extends AbstractController
{
    /**
     * @Route("/program/index", name="index_program")
     */
    public function indexProgram(): Response
    {

        $programs = $this->getDoctrine()->getRepository(Program::class)->findAll();

        if (!$programs) {
            throw $this->createNotFoundException('No program found in program\'s table.');
        }
        $form = $this->createForm(
            ProgramSearchType::class,
            null,
            ['method' => Request::METHOD_GET]
        );
        return $this->render(
            'wild/program/index.html.twig', [
                'programs' => $programs,
                'form' => $form->createView(),
            ]);
    }

    /**
     * @Route("/actor/index", name="index_actor")
     */
    public function indexActor(): Response
    {

        $actors = $this->getDoctrine()->getRepository(Actor::class)->findAll();

        if (!$actors) {
            throw $this->createNotFoundException('No actor found in actor\'s table.');
        }
        return $this->render(
            'wild/actor/index.html.twig', [
            'actors' => $actors,
        ]);
    }

    /**
     *
     * @Route("/program/show/{slug}", name="show_program", methods={"GET"})
     * @return Response
     */
    public function showProgram(Program $program):Response
    {
        return $this->render('wild/program/show.html.twig', [
            'program' => $program,
        ]);
    }

    /**
     *
     * @Route("/actor/show/{slug}", name="show_actor", methods={"GET"})
     * @return Response
     */
    public function showActor(Actor $actor):Response
    {
        return $this->render('wild/actor/show.html.twig', [
            'actor' => $actor,
        ]);
    }

    /**
     * @Route("/season/show/{id}", name="show_season", methods={"GET"})
     */
    public function showSeason(Season $season): Response
    {
        return $this->render('wild/season/show.html.twig', [
            'season' => $season,
        ]);
    }

    /**
     * @Route("/episode/show/{slug}", name="show_episode", methods={"GET", "POST"})
     */
    public function showEpisode(Episode $episode, Request $request): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment, ['method' => Request::METHOD_POST]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $comment->setAuthor($this->getUser());
            $comment->setEpisode($episode);
            $comment->setRate($data->getRate());
            $comment->setComment($data->getComment());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('wild_show_episode', ['slug' => $episode->getSlug()]);
        }
        return $this->render('wild/episode/show.html.twig', [
            'episode' => $episode,
            'form' => $form->createView(),
        ]);
    }


    /**
     *
     * @param string $categoryName
     * @Route("/category/{categoryName}", name="show_category")
     * @return Response
     */
    public function showByCategory(string $categoryName):Response
    {
        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findOneByName($categoryName);
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findBy(['category' => $category->getId()],
                    ['id' => 'desc'],
                    3
                    );

        return $this->render('wild/category.html.twig', [ 'programs' => $programs,
                                                                'category' => $category
                                                                ]);
    }

    /**
     *
     * @param string $programName
     * @Route("/program/{programName<^[a-zA-Z0-9-]+$>}", name="show_by_program")
     * @return Response
     */
    public function showByProgram(string $programName): Response
    {
        if (!$programName) {
            throw $this
                ->createNotFoundException('No program has been sent to find a program in program\'s table.');
        }
        $programName = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($programName)), "-")
        );
        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(['title' => $programName]);
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with '.$programName.' title, found in program\'s table.'
            );
        }

        $seasons = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findBy(['program' => $program]);

        return $this->render('wild/program.html.twig', [
            'program' => $program,
            'seasons' => $seasons,
        ]);
    }

    /**
     *
     * @param int $id
     * @Route("/season/{id<^\d+>}", name="show_by_season")
     * @return Response
     */
    public function showBySeason(int $id):Response
    {
        if (!$id) {
            throw $this
                ->createNotFoundException('No season found with this id.');
        }
        $season = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findOneBy(['id' => $id]);
        if (!$season) {
            throw $this->createNotFoundException(
                'No season with '.$id.' id, found in season\'s table.'
            );
        }

        $program = $season->getProgram();
        $episodes = $season->getEpisodes();

        return $this->render('wild/season.html.twig', [
            'program' => $program,
            'episodes' => $episodes,
            'season' => $season,
        ]);
    }

    /**
     *
     * @param int $id
     * @Route("/episode/{id<^\d+>}", name="show_by_episode")
     * @return Response
     */
    public function showByEpisode(Episode $episode):Response
    {
        return $this->render('wild/episode.html.twig', [
            'episode' => $episode,
        ]);

    }
}
