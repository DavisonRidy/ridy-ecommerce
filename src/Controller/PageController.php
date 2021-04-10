<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\User;
use App\Form\RegisterType;
use App\Repository\ProduitRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PageController extends AbstractController
{
    /**
     * @Route("/", name="eco_home")
     */
    public function home(UserRepository $repo): Response
    {
        return $this->render('page/index.html.twig');
    }

    /**
     * @Route("/produit", name="eco_produit")
     */
    public function produit(ProduitRepository $repo): Response
    {
        $pcs = $repo->findBy(['type' => 1], [],4);
        $phones = $repo->findBy(['type' => 2], [],4);
        $tablettes = $repo->findBy(['type' => 3], [],4);
        $accessoires = $repo->findBy(['type' => 4], [],4);
        return $this->render('page/produit.html.twig', compact('pcs','phones', 'tablettes', 'accessoires'));
    }

    /**
     * @Route("/view/produit/{type}/{id}/{pg?}", name="eco_produit_view")
     */
    public function view($type, $pg, Produit $produit, ProduitRepository $repo, Request $request): Response
    {
        $pgs = ceil(count( $repo->findBy(['type' => $type]))/5);
        $pg = $pg ?? 1;
        $offset = ($pg - 1) * 5;
        $produits = $repo->findBy(['type' => $type], [], 5, $offset);
        $data = $request->attributes;
        return $this->render('page/view.html.twig', compact('pgs', 'pg', 'produit', 'produits'));
    }


    /**
     * @Route("/registration", name="eco_inscription")
     */
    public function registerUser(EntityManagerInterface $em, Request $request,UserPasswordEncoderInterface $encoder)
    {
        if ($this->getUser()) {
            $this->addFlash("danger", "Vous déjà connecter!");
            return $this->redirectToRoute('eco_home');
        }
        $user = new User();

        $form = $this->createForm(RegisterType::class, $user);
        $form ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $user->setRoles(["ROLE_USER"]);
            $em->persist($user);
            $em->flush();
            $this->addFlash("success","Compte créé avec success");
            return $this->redirectToRoute("eco_login");
        }

        return $this->render("admin/registration.html.twig", [
            'form' => $form->createView()
        ]);
    }
}
