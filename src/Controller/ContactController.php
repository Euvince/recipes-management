<?php

namespace App\Controller;

use App\DTO\ContactDTO;
use App\Event\ContactRequestEvent;
use App\Form\ContactType;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{

    private $request;

    function __construct(
        private readonly Security $security,
        private readonly RequestStack $requestStack,
        private readonly FormFactoryInterface $formFactory
    )
    {
        $this->request = $this->requestStack->getCurrentRequest();
    }

    #[Route(
        '/contact',
        name: 'contact',
        methods: ['GET', 'POST'],
        host: 'localhost'
    )]
    public function contact(EventDispatcherInterface $dispatcher): Response
    {
        /**
         * @var User $user
         */
        $user = $this->security->getUser();
        $data = $user === NULL 
            ? (new ContactDTO())
                ->setName('John DOE')
                ->setEmail('john@doe.fr')
                ->setMessage('Superbe application') 
            : (new ContactDTO())
                ->setName($user->getUsername())
                ->setEmail($user->getEmail())
                ->setMessage('Superbe application')
        ;
        $form = $this->formFactory->create(ContactType::class, $data);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $dispatcher->dispatch(new ContactRequestEvent($data));
                $this->addFlash('success', "Votre message a bien été envoyé");
                return $this->redirectToRoute('contact');
            }catch (\Exception $e) {
                $this->addFlash('danger', "Une erreur es survenue et le mail n'a pas pu être envoyé");
                return $this->redirectToRoute('contact');
            }

            /* try {
                $mail = (new TemplatedEmail())
                    ->to($data->getService())
                    ->from($data->getEmail())
                    ->subject('Nouvelle demande de contact')
                    ->context(['data' => $data])
                    ->htmlTemplate('emails/contact.html.twig')
                ;
                $mailer->send($mail);
                $this->addFlash('success', "Votre message a bien été envoyé");
                return $this->redirectToRoute('contact');
            }catch (\Exception $e) {
                $this->addFlash('danger', "Une erreur es survenue et le mail n'a pas pu être envoyé");
                return $this->redirectToRoute('contact');
            } */
        }

        return $this->render('contact/contact.html.twig', [
            'form' => $form,
            'data' => $data
        ]);
    }
}
