<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use App\Security\UtilisateurAuthenticator;
use App\Service\EnvoiDeMail;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

/**
 * @Route ("/admin")
 *
 */
class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(MailerInterface $mailer, Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, UtilisateurAuthenticator $authenticator, EntityManagerInterface $entityManager, EnvoiDeMail $sender): Response
    {
        //$this->denyAccessUnlessGranted('ROLE_ADMIN');
        $user = new Utilisateur();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setPassword2(
               $user->getPassword()
            );
            $user->setRoledeux($user->getRoles());
            $user->setCompteValidate("false");
            // creer key +envoi de mail
            $user->setKeyCode(md5(uniqid()));
            $user->setDateTimeValidcompte(new \DateTime('now'));
            $entityManager->persist($user);
            $entityManager->flush();
            //envoi mail pour confirme du compte
            $from="contact@frequencies.fr";
            $subject="Validation de votre compte";
            $message="voici le lien pour valider votre compte: (valable 15min)";
            $code= "";
            $bouton="true";
            $textBouton="Valider son compte";
            $hrefBouton="http://127.0.0.1:8000/validation_de_compte/".$user->getKeyCode();
            $sender->SendEmailCode($mailer,$user,$from,$subject,$message,$code,$bouton,$textBouton,$hrefBouton);
            //************************************************

            return $this->redirectToRoute('home_controller', [ ]);
            /*return $userAuthenticator->authenticateUser(//*********si on veut creer le compte et etre authentifier
                $user,
                $authenticator,
                $request
            );*/
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
