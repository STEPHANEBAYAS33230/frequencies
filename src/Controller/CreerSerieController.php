<?php

namespace App\Controller;

use App\Entity\Serie;
use App\Form\SerieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CreerSerieController extends AbstractController
{
    /**
     * @Route("/admin/creer/serie", name="creer_serie")
     */
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $serie = new Serie();
        $form = $this->createForm(SerieType::class, $serie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {


            $serie->setDateCreation(new \DateTime('now'));
            $entityManager->persist($serie);
            $entityManager->flush();
            return $this->redirectToRoute('admin', [ ]);

        }
        return $this->render('creer_serie/index.html.twig', [
            'serieForm' => $form->createView(),
        ]);
    }
}
