<?php

namespace App\Controller;

use App\Entity\FiltreUtilisateurs;
use App\Entity\Utilisateur;
use App\Form\FiltreUtilisateursType;
use App\Form\LoginMailType;
use App\Security\UtilisateurAuthenticator;
use App\Service\EnvoiDeMail;
use App\Service\IntervalleDeDate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class SecurityController extends AbstractController
{

    /**
     * @Route("/login", name="app_login")
     *
     */
    public function login(AuthenticationUtils $authenticationUtils, Request $request,EntityManagerInterface $em, IntervalleDeDate $ecartDate): Response
    {

           //if ($this->getUser()) {
         // return $this->redirectToRoute('/');
         //}

        $messageD="";
        $user=$this->getUser();
        if ($user==null) {
            $placeholder="code reçu/votre email";
            $codePassw="code : ";
        } elseif ($user!=null and $user->getRoles()==['ROLE_INTER']) {
            $ecar=$ecartDate->dateDiff($user->getMoment());
            if ($ecar>=3) {//code valable uniq 3min

                $messageD="Le code saisi n'est plus valable (3minutes maximum).";
                $this->addFlash('error', "Le code saisi n'est plus valable (3minutes maximum).");
                return $this->render('security/retour.html.twig', ['message'=> $messageD]);
            }
            $placeholder="password";
            $codePassw="mot de passe : ";
            $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
            $utilisateur = $utilisateurRepo->trouverUtilisateur($user->getEmail());
            //CHANGER CODE PAR MP
            $utilisateur[0]->setPassword(
                    $user->getPassword2()
                );
            $utilisateur[0]->setRoles($utilisateur[0]->getRoledeux());
            $em->persist($utilisateur[0]);
            $em->flush();

        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
       $lastUsername = $authenticationUtils->getLastUsername();
         return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error, 'placeholder'=>$placeholder,'codePassw'=>$codePassw,'messageD'=>$messageD]);

    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route ("/login/email", name="app_loginEmail")
     *
     */
    public function loginEmail(UserPasswordHasherInterface $userPasswordHasher,Request $request,EnvoiDeMail $sender, MailerInterface $mailer, EntityManagerInterface $em): Response
    {
        $messageD="";
        $user= new FiltreUtilisateurs();
        $registerForm2 = $this->createForm(FiltreUtilisateursType::class, $user);
        $registerForm2->handleRequest($request);

        if ($registerForm2->isSubmitted() and $registerForm2->isValid()) {
            $email=$user->getEmail();
            $utilisateurRepo = $this->getDoctrine()->getRepository(Utilisateur::class);
            $utilisateur = $utilisateurRepo->trouverUtilisateur($email);

            if (sizeof($utilisateur)==1) {
                // SI COMPTE NON VALIDE PAR LE LIEN DE L'email
                if ($utilisateur[0]->getCompteValidate()=='false') {
                    return $this->render('security/compteInvalide.html.twig', [

                    ]);
                }
                //mail +code service
                $from="contact@frequencies.fr";
                $subject="code secret pour connexion";
                $message="voici votre code pour votre idenditifiation";
                $code= strval(rand(100000000, 999999999));
                $utilisateur[0]->setCode($code);
                $bouton="false";
                $textBouton="";
                $hrefBouton="";
                $datetime=new \DateTime('now');
                $utilisateur[0]->setMoment($datetime);
                $utilisateur[0]->setRoles(['ROLE_INTER']);
                $utilisateur[0]->setPassword(
                    $userPasswordHasher->hashPassword(
                        $utilisateur[0],
                        $code
                    ));
                //* save code+datetime
                $em->persist($utilisateur[0]);
                $em->flush();

                // envoi mail
                $sender->SendEmailCode($mailer,$utilisateur[0],$from,$subject,$message,$code,$bouton,$textBouton,$hrefBouton);


                return $this->redirectToRoute('app_login', []);

            }
            else if (sizeof($utilisateur)>1) {
                die();
            }
            else if (sizeof($utilisateur)==0)
            {
                $this->addFlash('error', 'Email invalide...');
            }




        }

        return $this->render('security/loginEmail.html.twig', [
            "registrationForm"=>$registerForm2->createView(), 'message'=>$messageD
        ]);
    }
    /**
     * @Route ("/monApplI/after", name="app_inter")
     *
     */
    public function loginAfter(){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user=$this->getUser();
        if ($user->getRoles()==['ROLE_ADMIN']) {
            if ($user->getCompteValidate()!="true") {
                $this->addFlash('error', 'Compte non validé par le lien reçu par mail...');
                return $this->redirectToRoute('app_logout', []);
            }
            return $this->redirectToRoute('admin', []);
        }
        if ($user->getRoles()==['ROLE_USER']) {
            if ($user->getCompteValidate()!="true") {
                $this->addFlash('error', 'Compte non validé par le lien reçu par mail...');
                return $this->redirectToRoute('app_logout', []);
            }
            return $this->redirectToRoute('home_controller', []);
        }
        if ($user->getRoles()==['ROLE_INTER']) {
            return $this->redirectToRoute('app_login', []);
        }


    }
}
