<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Service\IntervalleDeDate;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ValidationLienController extends AbstractController
{
    /**
     * @Route("/validation_de_compte/{token}", name="validation_compte")
     */
    public function validation_compte($token, EntityManagerInterface $em,  IntervalleDeDate $ecartDate): Response
    {
        $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
        $utilisateur = $utilisateurRepo->trouverUtilisateurParToken($token);
        if (sizeof($utilisateur)==0) {
            return $this->render('validation_lien/impasse.html.twig', [
            ]);
        }
        if (sizeof($utilisateur)==1) {
            $ecar=$ecartDate->dateDiff($utilisateur[0]->getDateTimeValidcompte());
            if ($ecar<15) {
                $utilisateur[0]->setKeyCode("");
                $utilisateur[0]->setCompteValidate("true");
                $utilisateur[0]->setDateTimeValidcompte(null);
                //* save
                $em->persist($utilisateur[0]);
                $em->flush();
            } elseif ($ecar>=15) {
                //* suppression
                $em->remove($utilisateur[0]);
                $em->flush();
                return $this->render('validation_lien/impasse.html.twig', [
                    ]);
            }

        }


        return $this->render('validation_lien/validate_compte.html.twig', [

        ]);
    }
}
