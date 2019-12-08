<?php


namespace App\Controller;

use App\Entity\Actor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/actor")
 */
class ActorController extends AbstractController
{

    /**
     * @Route("/{name}", name="actor_show", methods={"GET"})
     */
    public function show(string $name): Response
    {
        $name = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($name)), "-")
        );
        $actor = $this->getDoctrine()
            ->getRepository(Actor::class)
            ->findOneBy(['name' => mb_strtolower($name)]);
        if (!$name) {
            throw $this->createNotFoundException(
                'No actor with '.$name.' name, found in actor\'s table.'
            );
        }
        return $this->render('actor/show.html.twig', [
            'actor' => $actor,
        ]);
    }
}
