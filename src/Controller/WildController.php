<?php
// src/Controller/WildController.php
namespace App\Controller;

use App\Entity\Category;
use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use App\Form\CategoryType;
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
     * @Route("/index", name="index")
     */
    public function index(): Response
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
            'wild/index.html.twig', [
                'programs' => $programs,
                'form' => $form->createView(),
            ]);
    }

    /**
     * Getting a program with a formatted slug for title
     *
     * @param string $slug The slugger
     * @Route("/show/{slug<^[a-z0-9-]+$>}", defaults={"slug" = null}, name="show")
     * @return Response
     */
    public function show(?string $slug):Response
    {
        if (!$slug) {
            throw $this
                ->createNotFoundException('No slug has been sent to find a program in program\'s table.');
        }
        $slug = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($slug)), "-")
        );
        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(['title' => mb_strtolower($slug)]);
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with '.$slug.' title, found in program\'s table.'
            );
        }

        return $this->render('wild/show.html.twig', [
            'program' => $program,
            'slug'  => $slug,
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
     * @Route("/program/{programName<^[a-zA-Z0-9-]+$>}", name="show_program")
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
     * @Route("/season/{id<^\d+>}", name="show_season")
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
     * @Route("/episode/{id<^\d+>}", name="show_episode")
     * @return Response
     */
    public function showEpisode(Episode $episode):Response
    {
        return $this->render('wild/episode.html.twig', [
            'episode' => $episode,
        ]);

    }
}