<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/category", name="category_")
 */
class CategoryController extends AbstractController
{

    /**
     *
     * @Route("/index", name="index")
     * @return Response
     */
    public function index(): Response
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        if (!$categories) {
                throw $this->createNotFoundException('No category found.');
        }
        return $this->render(
            'category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     *
     * @Route("/add", name="add")
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function add(EntityManagerInterface $entityManager, Request $request):Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category, ['method' => Request::METHOD_GET]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $data = $form->getData();
            // $data contient les données du $_POST
            // TODO : Faire une recherche dans la BDD avec les infos de $data...
            $category->setName($data->getName());
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('category_index');
        }
        return $this->render(
            'category/add.html.twig', [
            'form' => $form->createView(),
        ]);

    }
}
