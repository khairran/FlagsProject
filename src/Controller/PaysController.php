<?php

namespace App\Controller;

use App\Entity\Pays;
use App\Form\PaysType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PaysController extends AbstractController
{
    /**
     * @Route("/pays", name="pays")
     */
    public function index(Request $request)
{
    //on récupère le service de la DB (doctrine)    
    $em = $this->getDoctrine()->getManager();

    $un_pays = new Pays();
    $form = $this->createForm(PaysType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted()){
        
        $files = $form->get('drapeauUpload')->getData();

        // this condition is needed because the 'drapeauUpload' field is not required
        // so the image file must be processed only when a file is uploaded
        if ($files) {

            $newFilename = uniqid().'.'.$files->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $files->move(
                    $this->getParameter('upload_directory'),
                    $newFilename
                );
            } catch (FileException   $e) {
                // ... handle exception if something happens during file upload
                $this->addFlash('error', " Impossible d'upload le file ");

            }

            // updates the 'brochureFilename' property to store the PDF file name
            // instead of its contents
            $un_pays->setDrapeau($newFilename);
        }


    $em->persist($un_pays);
    $em->flush();

    $this->addFlash('success', " Pays Ajouté ");


    }

    //ici on récupère la table pays
    $pays = $em->getRepository(Pays::class)->findAll();

    
        return $this->render('pays/index.html.twig', [
            'pays' => $pays,
            'ajout' => $form->createView()

        ]);
    }

    /**
     * @Route("/pays/edit/{id}", name="edit_pays")
     */
    //cet id on le conertis en un pays
    public function edit(Pays $pays = null, Request $request){

        if($pays == null){
            //msg flash avant la redirection sinon pas pris en compte
            $this->addFlash('error', 'Pays introuvable');
            //si on en n'en trouve alors on fait une redirection
            return $this->redirectToRoute('pays');
        }

        //mon form
        $form = $this->createForm(PaysType::class, $pays);
        $form->handleRequest($request);

        //si le form est submite alors je le save
        if($form->isSubmitted()){
           $em = $this->getDoctrine()->getManager();
           $em->persist($pays);
           $em->flush();

           $this->addFlash('success', 'pays modifié');
        }
        //puis on retourne une vue qui va recevoir mon pays et mon form

        return $this->render('pays/edit.html.twig', [
            'pays' => $pays,
            'edit' => $form->createView()
        ]);

    }
}