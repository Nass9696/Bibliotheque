<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormTypeInterface;
use App\Entity\Livre;
use App\Entity\Category;
use App\Entity\Emprunteur;
use App\Form\AddLivreType;
use App\Form\SortByType;
use App\Repository\CategoryRepository;
use App\Repository\LivreRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


class LivreController extends AbstractController
{

  /**
   * @Route("", name="livre")
   */
  public function home()
  {
      return $this->render('home.html.twig');
  }

    /**
     * @Route("/livre/{id}", name="app_getLivre{id}")
     */
    public function getLivre($id)
    {
        $repository = $this->getDoctrine()->getRepository(Livre::class);
        $livre = $repository->findOneBy(array('id' => $id));
        if(!$livre) {
          throw $this->createNotFoundException("Ce livre n'existe pas");
        }
        // $form = $this->createForm(Emprunteur::class);
        // $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {
        //   $data = $form->getData();
        //   $user = $this->getDoctrine()->getRepository(Emprunteur::class)->findOneBy(["code" => $data["code"]]);
        //   if(!$user) {
        //     $this->addFlash("danger", "Ce code utilisateur n'est pas valide");
        //   }
        //   else {
        //     $livre->setBorrower($user);
        //     $entityManager = $this->getDoctrine()->getManager();
        //     $entityManager->persist($livre);
        //     $entityManager->flush();
        //     $this->addFlash("success", "Le livre a été emprunté");
        //   }
        // }
        return $this->render('livre/singleLivre.html.twig', [
            'id' => $id, 'livre' => $livre
        ]);
    }

    /**
     * @Route("/ajout/livre", name="app_addLivre")
     * @IsGranted("ROLE_BIBLIOTHECAIRE")
     */
    public function addLivre(Request $request): Response
    {
      //On instance l'objet//
      $livre = new Livre();
      //On crée le form et on fait passer le nom du form::class pour qu'il hydrate l'objet qu'on a instancié avant//
      $form = $this->createForm(AddLivreType::class, $livre);
      $form->handleRequest($request);

      //Vérification du formulaire (rempli + validation)//
      if ($form->isSubmitted() && $form->isValid())
     {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($livre);
        $entityManager->flush();
        return $this->redirectToRoute('livre');
      }
      //Sinon on crée une vue pour afficher le form//
        return $this->render('livre/addLivre.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("livre/{id}/emprunt", name="app_emprunt")
     */
    public function emprunterLivre($id)
    {

        return $this->render('livre/emprunterLivre.html.twig', [
          'id' => $id
        ]);
    }

    /**
    * @Route("/livres", name="livre_index", methods={"GET","POST"})
    */
    public function trieLivre(LivreRepository $LivreRepository, Request $request): Response
   {
       $form = $this->createForm(SortByType::class);
       $form->handleRequest($request);

       if ($form->isSubmitted() && $form->isValid()) {
         $trieCategorie = $form->getData()['name'];
         $livres = $LivreRepository->getCategorywithLivre($trieCategorie);
         var_dump($trieCategorie);
        }
       else {
         $livres = $LivreRepository->findAll();
       }
       return $this->render('livre/index.html.twig', [
           'livres' => $livres,
           'form' => $form->createView()
        ]);

    }
}
