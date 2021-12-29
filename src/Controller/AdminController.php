<?php

namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Serie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/menu", name="admin")
     */
    public function index(): Response
    {
        //**********recup de toute les serie/episode
        $serieRepo = $this->getDoctrine()->getRepository(Serie::class);
        $serie = $serieRepo->findAll();
        $episodeRepo=$this->getDoctrine()->getRepository(Episode::class);
        $episode=$episodeRepo->trouverEpiOrdre();
        return $this->render('admin/index.html.twig', [
            'series' =>$serie, 'episodes' =>$episode
        ]);
    }
}
