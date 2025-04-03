<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;


final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(Request $request,EntityManagerInterface $entityManager,MailerInterface $mailer): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contact->setCreatedAt(new \DateTimeImmutable());

            // Envoi de l'email
            $email = (new Email())
                ->from('mailtrap@demomailtrap.com')  // Adresse spéciale Mailtrap
                ->replyTo($contact->getEmail())  // Permet de répondre à l'expéditeur réel
                ->to('votre_email@example.com')  // Votre email de réception
                ->html(
                    '<p>Nom: ' . $contact->getName() . '</p>' .
                    '<p>Email: ' . $contact->getEmail() . '</p>' .
                    '<p>Message: ' . $contact->getMessage() . '</p>'
                );

            // Envoi de l'email
            try {
                $mailer->send($email);
                $this->addFlash('success', 'E-mail envoyé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de l\'email.');
            }


            $entityManager->persist($contact);
            $entityManager->flush();

            $this->addFlash('success', 'Votre message a bien été envoyé !');

            return $this->redirectToRoute('contact');

        }
        return $this->render('contact/index.html.twig',['contactForm'=>$form->createView()]);
    }
}
