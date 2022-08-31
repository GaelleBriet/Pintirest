<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Form\PinType;
use App\Repository\PinRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
class PinsController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: 'GET')]
    public function index(PinRepository $pinRepository): Response
    {
        $pins = $pinRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('pins/index.html.twig', compact('pins'));
        // return $this->render('pins/index.html.twig', ['pins' => $pins]);
    }

    #[Route('/pins/create', name: 'app_pins_create', methods: "GET|POST")]
    #[Security("is_granted('ROLE_USER') and user.isVerified()")]
    // #[Security("is_granted('ROLE_USER')")]
    public function create(Request $request, EntityManagerInterface $em, UserRepository $userRepo): Response
    {

        $pin = new Pin;

        $form = $this->createForm(PinType::class, $pin);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $janeDoe = $userRepo->findOneBy(['email' => 'janedoe@example.com']);
            $pin->setUser($this->getUser());
            $em->persist($pin);
            $em->flush();

            $this->addFlash('success', 'Pin successfully created!');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('pins/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/pins/{id<[0-9]+>}', name: 'app_pins_show', methods: "GET")]
    public function show(Pin $pin): Response
    {
        return $this->render('pins/show.html.twig', compact('pin'));
    }

    #[Route('/pins/{id<[0-9]+>}/edit', name: 'app_pins_edit', methods: "GET|PUT")]
    #[IsGranted('PIN_EDIT', subject: 'pin')]
    public function edit(Request $request, Pin $pin, EntityManagerInterface $em): Response
    {
        // on ne vérifie plus ici si l'utilisateur est connecté mais on le fait dans les attributs (annotation) Security qui renvoie vers le PinVoter
        // if ($pin->getUser() != $this->getUser()) {
        //     throw $this->createAccessDeniedException();
        // }

        $form = $this->createForm(PinType::class, $pin, [
            'method' => 'PUT'
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Pin successfully updated!');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('pins/edit.html.twig', [
            'pin' => $pin,
            'form' => $form->createView()
        ]);
    }

    #[Route('/pins/{id<[0-9]+>}', name: 'app_pins_delete', methods: "DELETE")]
    // #[Security("is_granted('PIN_DELETE', pin)")]
    #[IsGranted('PIN_DELETE', subject: 'pin')]
    public function delete(Request $request, Pin $pin, EntityManagerInterface $em): Response
    {
        // if ($pin->getUser() != $this->getUser()) {
        //     throw $this->createAccessDeniedException();
        // }

        if ($this->isCsrfTokenValid('pin_deletion_' . $pin->getId(), $request->request->get('csrf_token'))) {
            $em->remove($pin);
            $em->flush();

            $this->addFlash('info', 'Pin successfully deleted!');
        }

        return $this->redirectToRoute('app_home');
    }
}
