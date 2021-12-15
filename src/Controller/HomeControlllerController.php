<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeControlllerController extends AbstractController
{
    /**
     * @Route("/", name="home_controller")
     */
    public function index(): Response
    {


        return $this->render('home_controlller/index.html.twig', [

        ]);
    }

}
