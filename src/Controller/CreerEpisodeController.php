<?php

namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Serie;
use App\Form\EpisodeType;
use App\Form\KlserieType;
use App\Form\ModifEpisodeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class CreerEpisodeController extends AbstractController
{
    /**
     * @Route("/admin/creer/serie-episode", name="creer_serie_episode")
     */
    public function klSerie(Request $request, SluggerInterface $slugger, EntityManagerInterface $em): Response
    {
        //**********recup de toute les serie/episode
        $serieRepo = $this->getDoctrine()->getRepository(Serie::class);
        $seri = $serieRepo->findAll();
        $episodeRepo=$this->getDoctrine()->getRepository(Episode::class);
        $episod=$episodeRepo->trouverEpiOrdre();


        return $this->renderForm('creer_episode/klserie.html.twig', [
             'series' =>$seri, 'episodes' =>$episod
        ]);
    }




    /**
     * @Route("/admin/creer/episode/{seri}", name="creer_episode")
     */
    public function index($seri, Request $request, SluggerInterface $slugger, EntityManagerInterface $em): Response
    {
        //**********recup de toute les serie/episode
        //$serieRepo = $this->getDoctrine()->getRepository(Serie::class);
        $seriRepo=$this->getDoctrine()->getRepository(Serie::class);
        $serii=$seriRepo->find($seri);
        $episodeRepo=$this->getDoctrine()->getRepository(Episode::class);
        $episod=$episodeRepo->trouverEpiOrdre();
        $episode=new Episode();
        $episode->setSerie($serii);
        //$episode = new Episode();
        $form = $this->createForm(EpisodeType::class, $episode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                //verifier si numero dans la serie existe
                $laSerieChoisie=$episode->getSerie();
                $numerochoisi=$episode->getNumero();
                $numRepo = $this->getDoctrine()->getRepository(Episode::class);
                $SerieNumeroIdentiq=$numRepo->findSinumberUtiliser($laSerieChoisie,$numerochoisi);
                if (sizeof($SerieNumeroIdentiq)>0) { //si numero deja utilisé
                    $this->addFlash('error', 'Le numero d\'ordre est déja utilisé...Veillez à le changer ! (N°choisi :'.$numerochoisi.')');
                    return $this->renderForm('creer_episode/index.html.twig', [
                        'episodeForm' => $form, 'series' =>$serii, 'episodes' =>$episod
                    ]);
                }
                //**************************************
                /** @var UploadedFile $brochureFile */
                $brochureFile = $form->get('brochure')->getData();

                // this condition is needed because the 'brochure' field is not required
                // so the PDF file must be processed only when a file is uploaded
                if ($brochureFile) {
                    $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

                    // Move the file to the directory where brochures are stored
                    try {
                        $brochureFile->move(
                            $this->getParameter('brochures_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }

                    // updates the 'brochureFilename' property to store the PDF file name
                    // instead of its contents
                    $episode->setBrochureFilename($newFilename);
                }

                // ... persist the $product variable or any other work
                $em->persist($episode);
                $em->flush();
                $serie = $episode->getSerie();
                $serieRepo = $this->getDoctrine()->getRepository(Serie::class);
                $sery = $serieRepo->find($serie);
                $sery->addEpisode($episode);
                $em->persist($sery);
                $em->flush();

                return $this->redirectToRoute('admin');

        }

        return $this->renderForm('creer_episode/index.html.twig', [
            'episodeForm' => $form, 'series' =>$serii, 'episodes' =>$episod
        ]);

    }


    /**
     * @Route("/admin/modifier-episode/{episodId}", name="modifier_episode")
     */
    public function modifEpisode($episodId, Request $request, SluggerInterface $slugger, EntityManagerInterface $em): Response
    {
        //recup de l episode à modifier
        $lepisodeRepo=$this->getDoctrine()->getRepository(Episode::class);
        $episode=$lepisodeRepo->find($episodId);
        if ($episode==null) {//si nul probleme deconnexion
            return $this->redirectToRoute('app_logout');
        }
        $oldName=$episode->getBrochureFilename();
        //**********recup de toute les serie/episode
        //$serieRepo = $this->getDoctrine()->getRepository(Serie::class);
        $seriRepo=$this->getDoctrine()->getRepository(Serie::class);
        $serii=$seriRepo->find($episode->getSerie());
        $episodeRepo=$this->getDoctrine()->getRepository(Episode::class);
        $episod=$episodeRepo->trouverEpiOrdre();
        //$episode=new Episode();
        //$episode->setSerie($serii);
        //$episode = new Episode();
        $form = $this->createForm(EpisodeType::class, $episode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //*****efface aussi le fichier audio ancien
            $nameAudio = $this->getParameter("brochures_directory") . "/" . $oldName;
            //********************
            if (file_exists($nameAudio)) {
                unlink($nameAudio);
            }
            $episode->setBrochureFilename($oldName);
            //verifier si numero dans la serie existe
            $laSerieChoisie=$episode->getSerie();
            $numerochoisi=$episode->getNumero();
            $numRepo = $this->getDoctrine()->getRepository(Episode::class);
            $SerieNumeroIdentiq=$numRepo->findSinumberUtiliser($laSerieChoisie,$numerochoisi);
            if (sizeof($SerieNumeroIdentiq)>0) { //si numero deja utilisé
                $this->addFlash('error', 'Le numero d\'ordre est déja utilisé...Veillez à le changer ! (N°choisi :'.$numerochoisi.')');
                return $this->renderForm('creer_episode/index.html.twig', [
                    'episodeForm' => $form, 'series' =>$serii, 'episodes' =>$episod
                ]);
            }

            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('brochure')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('brochures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $episode->setBrochureFilename($newFilename);
            }

            // ... persist the $product variable or any other work
            $em->persist($episode);
            $em->flush();
            $serie = $episode->getSerie();
            $serieRepo = $this->getDoctrine()->getRepository(Serie::class);
            $sery = $serieRepo->find($serie);
            $sery->addEpisode($episode);
            $em->persist($sery);
            $em->flush();
            //$epi=$sery->getEpisodes();

            //var_dump($epi[1]->getNomEpisode());
            //die();

            return $this->redirectToRoute('admin');

        }

        return $this->renderForm('creer_episode/modifEpisode.html.twig', [
            'episodeForm' => $form, 'series' =>$serii, 'episodes' =>$episod, 'episodeModif' =>$episode
        ]);

    }

    /**
     * @Route("/admin/delete-episode/{episodId}", name="delete_episode")
     */
    public function deleteEpisode($episodId, Request $request, SluggerInterface $slugger, EntityManagerInterface $em): Response
    {
        //recup de l episode à modifier
        $lepisodeRepo=$this->getDoctrine()->getRepository(Episode::class);
        $episode=$lepisodeRepo->find($episodId);
        if ($episode==null) {//si nul probleme deconnexion
            return $this->redirectToRoute('app_logout');
        }
        $oldName=$episode->getBrochureFilename();

            //*****efface aussi le fichier audio ancien
            $nameAudio = $this->getParameter("brochures_directory") . "/" . $oldName;
            //********************
            if (file_exists($nameAudio)) {
                unlink($nameAudio);
            }

            // ... persist the $product variable or any other work
            $serie = $episode->getSerie();
            $serieRepo = $this->getDoctrine()->getRepository(Serie::class);
            $sery = $serieRepo->find($serie);
            $sery->removeEpisode($episode);
            $em->persist($sery);

            $em->flush();
            $em->remove($episode);
            $em->flush();


            return $this->redirectToRoute('creer_serie_episode');



    }

}
