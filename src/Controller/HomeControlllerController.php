<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Service\IntervalleDeDate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeControlllerController extends AbstractController
{
    /**
     * @Route("/", name="home_controller")
     */
    public function index(IntervalleDeDate $ecartDate, EntityManagerInterface $em): Response
    {
        // ICI ON EFFACE LES COMPTES NON VALIDEES PAR L EMAIL RECU DE L UTILISATEUR
        $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
        $utilisateur = $utilisateurRepo->trouverDateCompteValidate();
        foreach ($utilisateur as $utili) {
            $ecar=$ecartDate->dateDiff($utili->getDateTimeValidcompte());
            if ($ecar>14 && ($utili->getCompteValidate())=="false") {
                $em->remove($utili);
                $em->flush();
            }

        }// FIN *******************************************************************

            return $this->redirectToRoute('home_controller1', []);

        //return $this->render('home_controlller/index.html.twig', [

        //]);
    }

    /**
     * @Route("/home", name="home_controller1")
     */
    public function indexUn(): Response
    {


        return $this->render('home_controlller/index.html.twig', [

        ]);
    }
}
