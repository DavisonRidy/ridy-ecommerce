<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Livraison;
use App\Form\CommandeType;
use App\Repository\LivraisonRepository;
use App\Repository\ProduitRepository;
use Doctrine\DBAL\Driver\SQLSrv\LastInsertId;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PanierController extends AbstractController
{
    /**
     * @Route("/panier", name="eco_panier")
     */
    public function index(SessionInterface $session, ProduitRepository $produitRepository): Response
    {
        $panier = $session->get('panier', []);
        $info = $session->get('info', []);
        $panierData = [];
        foreach ($panier as $id => $n) {
            $panierData[] = [
                'produit' => $produitRepository->find($id),
                'n' => $n
            ];
        }
        $total = 0;
        foreach ($panier as $id => $n) {
            $prix = $produitRepository->find($id)->getPrix() * (int)$n;
            $total += $prix;
        }
        $info['total'] = $total;
        $session->set('info', $info);
        return $this->render('panier/index.html.twig', compact('panierData', 'total'));
    }
    
    /** 
    * @Route("/panier/livraison/{id?}", name="eco_panier_livraison")
    */
    public function livre($id, SessionInterface $session, LivraisonRepository $livraisonRepository): Response
    {        
        $id = $id ?? 1;
        $livraison = $livraisonRepository->findOneBy(['id' => $id]);

        $info = $session->get('info', []);
        $total = $info['total'];

        return $this->render('panier/livraison.html.twig', compact('total', 'livraison'));
    }

    /** 
    * @Route("/ajax/livraison/{id?}", name="eco_ajax_livraison")
    */
    public function livreAjax($id, LivraisonRepository $livraisonRepository){
        $livraison = $livraisonRepository->findOneBy(['id' => $id]);
        return $this->json([
            'code' => 200,
            'id' => $livraison->getId(),
            'choix' => $livraison->getChoix(),
            'prix' => $livraison->getPrix()
        ], 200);
    }

    /** 
    * @Route("/panier/commande/{id}", name="eco_panier_commande")
    */
    public function commande($id, Request $request, SessionInterface $session, LivraisonRepository $livraisonRepository, EntityManagerInterface $em, ProduitRepository $produitRepository): Response
    {
        $livraison = $livraisonRepository->findOneBy(['id' => $id]);
        $info = $session->get('info', []);
        $panier = $session->get('panier');
        $commande = new Commande();
        $commande ->setTotalAPayer(1)
                    ->setLivraison($livraison)
                    ->addProduit($produitRepository->findOneBy([], ['id' => 'DESC']));
        $user = $this->getUser();
        if($user){
            $commande->setNom($user->getNom())
                    ->setPrenom($user->getPrenom())
                    ->setEmail($user->getEmail());
        }
        
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            $commande->setTotalAPayer($info['total'])
                        ->setProduitQnt($panier)
                        ->removeProduit($produitRepository->findOneBy([], ['id' => 'DESC']))
                        ->setLivraison($livraison);
            foreach ($panier as $pId => $n) {
                $produit = $produitRepository->find($pId);
                $produit->setStock($produit->getStock() - (int)$n);
                $commande->addProduit($produit);
            }

            $em->persist($commande);
            $em->flush();

            $session->set('panier', []);
            $session->set('info', []);

            $this->addFlash("success", "Votre commande a été passée!"); 
            return $this->redirectToRoute("eco_panier");
        }
        return $this->render('panier/commande.html.twig',[
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/panier/add/{id}/{n}", name="eco_panier_add")
     */
    public function add($id, $n, ProduitRepository $produitRepository, SessionInterface $session): Response
    {
        $produit = $produitRepository->find($id);
        if($n > $produit->getStock()){
            $this->addFlash("danger", "la quatitée est invalide!");
            return $this->redirectToRoute('eco_produit_view', ['type' => $produit->getType()->getId(),  'id' => $id]);
        }

        $panier = $session->get('panier', []);
        if ($produit){
            $panier[$id] = $n ;
            $session->set('panier', $panier);
        }
        return $this->redirectToRoute('eco_panier');
    }

    /**
     * @Route("/panier/view/{id}", name="eco_panier_view")
     */
    public function view($id, ProduitRepository $produitRepository, SessionInterface $session): Response
    {
        $produit = $produitRepository->find($id);
        if(!$produit){
            $this->addFlash('danger', 'le produit que vous aviez edité n\'exist pas!');
            return $this->redirectToRoute('eco_panier');
        }
        
        return $this->render('panier/view.html.twig', compact('produit'));
    }

    /**
     * @Route("/panier/edit/{id}", name="eco_panier_edit")
     */
    public function edit($id, ProduitRepository $produitRepository, SessionInterface $session): Response
    {
        $produit = $produitRepository->find($id);
        if(!$produit){
            $this->addFlash('danger', 'le produit que vous aviez edité n\'exist pas!');
            return $this->redirectToRoute('eco_panier');
        }else{
            $panier = $session->get('panier', []);

            $n = $panier[$id];
        }
        
        return $this->render('panier/edit.html.twig', compact('produit', 'n'));
    }

    /**
     * @Route("/panier/remove/{id}", name="eco_panier_remove")
     */
    public function remove($id, SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);
        if( !empty($panier[$id])){
            unset($panier[$id]);
        }
        $session->set('panier', $panier);
        return $this->redirectToRoute('eco_panier');
    }
}
