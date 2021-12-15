<?php


namespace App\Service;


use App\Entity\Utilisateur;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EnvoiDeMail
{

    public function SendEmailCode(MailerInterface $mailer,Utilisateur $user,$from,$subject,$message,$code) {
        $email=$user->getEmail();
        $prenomUtilisateur=$user->getPrenom();
        $nomUtilisateur=$user->getNom();
        //******************************************************************************************************
        $email = (new TemplatedEmail())
            ->from($from)
            ->to( $email)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            ->priority(Email::PRIORITY_HIGH)
            ->subject($subject)
            ->text('bonjour '.$prenomUtilisateur." ".$nomUtilisateur." :".$message."-".$code)
            ->htmlTemplate( 'mail/mailCode.html.twig')
            //->attachFromPath( $publicDir.'/public/assets/images/slide-01.jpg')
            //->attachFromPath( $publicDir.'/public/assets/images/seatraderBIG.png')
            ->context([
                'subject' => $subject,
                'from' => $from,
                'message' => $message,
                'nom' => $nomUtilisateur,
                'prenom'=> $prenomUtilisateur,
                'code'=> $code,
            ]);
        $mailer->send($email);
        //***************************
    }



}