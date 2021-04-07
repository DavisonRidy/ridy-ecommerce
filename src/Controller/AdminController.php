<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ProduitType;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends AbstractController
{
    /**
     * @Route("/new", name="eco_new")
     * @Route("/edit/{id}", name="eco_edit")
     */
    public function new(produit $produit = null, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->getUser()) {
            $this->addFlash("danger", "Vous devriez vous connecter!");
            return $this->redirectToRoute('eco_login');
        }else{
            $role = $this->getUser()->getRoles();
            if($role[0] == "ROLE_USER"){
                $this->addFlash("danger", "Vous devriez vous connecter en tant que personnel!");
                return $this->redirectToRoute('eco_home');
            }
        }

        $produit = $produit ?? new Produit();
        
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            
            if(!$produit ->getId()){
                $produit->setCreation(new \Datetime)
                        ->setAJour(new \Datetime);

                $this->addFlash("success", "Le produit a été crréé !");
            }else{
                $produit->setAJour(new \Datetime);

                $this->addFlash("info", "Le produit a été Mis à jour !");
            }


            $em->persist($produit);
            $em->flush();

            return $this->redirectToRoute('eco_produit_view', [
                'id' => $produit->getId(),
                'type' => $produit->getType()->getId()
            ]);

        }


        return $this->render('admin/new.html.twig',[
            'form' => $form->createView(), 'mode' => $produit->getId()
        ]);
    }

    /**
     * @Route("/delete/produit/{id}", name="eco_produit_del", methods={"DELETE"})
     */
    public function delete(Produit $produit, EntityManagerInterface $em, Request $request)
    {
        $data = $request->request->get('token');

        if($this->isCsrfTokenValid('suppr-valide', $data)){
            $em->remove($produit);
            $em->flush();
        }

        $this->addFlash("success", "Le produit a été supprimée !"); 

        return $this->redirectToRoute('eco_produit');
    }

    /**
     * @Route("/registration/user", name="eco_inscription_user")
     */
    public function register(EntityManagerInterface $em, Request $request,UserPasswordEncoderInterface $encoder)
    {
        if (!$this->getUser()) {
            $this->addFlash("danger", "Vous devriez vous connecter!");
            return $this->redirectToRoute('eco_login');
        }else{
            $role = $this->getUser()->getRoles();
            if($role[0] != "ROLE_ADMIN"){
                $this->addFlash("danger", "Vous devriez vous connecter en tant qu'admin!");
                return $this->redirectToRoute('eco_home');
            }
        }
        $user = new User();

        $form = $this->createForm(RegisterType::class, $user);
        $form ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $em->persist($user);
            $em->flush();
            $this->addFlash("success","Compte créé avec success");
            return $this->redirectToRoute("eco_home");
        }

        return $this->render("admin/registration.html.twig", [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/commandes/{pg?1}", name="eco_commande_list")
     */
    public function commandes($pg, CommandeRepository $commandeRepository)
    {
        if (!$this->getUser()) {
            $this->addFlash("danger", "Vous devriez vous connecter!");
            return $this->redirectToRoute('eco_login');
        }else{
            $role = $this->getUser()->getRoles();
            if($role[0] == "ROLE_USER"){
                $this->addFlash("danger", "Vous devriez vous connecter en tant que personnel!");
                return $this->redirectToRoute('eco_home');
            }
        }
        $l = 5;
        $pgs = ceil(count( $commandeRepository->findAll())/$l);
        $pg = $pg ?? 1;
        $offset = ($pg - 1) * $l;
        $commandes = $commandeRepository->findBy([], ['id' => 'DESC'], $l, $offset);
        return $this->render("admin/commande.html.twig", compact('commandes', 'pgs', 'pg'));
    }

    /**
     * @Route("/commande/{id}", name="eco_commande_detail")
     */
    public function commande($id, CommandeRepository $commandeRepository)
    {
        if (!$this->getUser()) {
            $this->addFlash("danger", "Vous devriez vous connecter!");
            return $this->redirectToRoute('eco_login');
        }else{
            $role = $this->getUser()->getRoles();
            if($role[0] == "ROLE_USER"){
                $this->addFlash("danger", "Vous devriez vous connecter en tant que personnel!");
                return $this->redirectToRoute('eco_home');
            }
        }
        
        $commande = $commandeRepository->find($id);
        $produits = $commande->getProduit();
        return $this->render("admin/commandeDetail.html.twig", compact('commande', 'produits'));
    }
}
