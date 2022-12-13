<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Job;
use App\Entity\Candidature;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Session\Session;

class JobController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(Candidature::class);
        $lesCandidats = $repo->findAll();

        $data = array(
            'lesCandidats'=>$lesCandidats,
        );
        return $this->render('job/home.html.twig', $data);
    }
    /**
     * @Route("/job", name="job_add")
     */
    public function index(): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $job = new Job();
        $job->setType('Architecte');
        $job->setCompany('OffShoreBox');
        $job->setDescription('Genie logiciel');
        $job->setExpiresAt(new \DateTimeImmutable());
        $job->setEmail('haykel@gmail.com');
        
        // Ajout des condidat
        $candidature1 = new Candidature();
        $candidature1->setJob($job);
        $candidature1->setCandidat("Rhaiem");
        $candidature1->setContenu("Formation J2EE");
        $candidature1->setDate(new \DateTime());

        $candidature2 = new Candidature();
        $candidature2->setJob($job);
        $candidature2->setCandidat("Salime");
        $candidature2->setContenu("Formation Symfony");
        $candidature2->setDate(new \DateTime());

        $entityManager->persist($job);
        $entityManager->persist($candidature1);
        $entityManager->persist($candidature2);
        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();
        
        return $this->render('job/index.html.twig', [
            'controller_name' => 'JobController',
            'id' => $job->getId(),
        ]);
    }

    /**
    * @Route("/job/ajouter", name="ajouter")
    */
    public function ajouter(Request $request)
    {
        $candidat = new Candidature();
        
        $fb = $this->createFormBuilder($candidat)
        ->add('candidat', TextType::class)
        ->add('contenu', TextType::class, array("label" => "Contenu"))
        ->add('date', DateType::class)
        ->add('job', EntityType::class, array(
            'class' => Job::class,
            'choice_label' => 'type'
        ))
        ->add('Valider', SubmitType::class);
        
        // generer le formulaire a partir du FormBuilder
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($candidat);
            $em->flush();
            
            return $this->redirectToRoute('ajouter');
        }

        // Utiliser le methode createView() pour que l'objet soit exploitable par la vue
        $data = array(
            'form' => $form->createView()  
        );

        return $this->render('job/ajouter.html.twig', $data);
    }

    /**
    * @Route("/job/{id}", name="job_show")
    */
    public function show($id)
    {
        $job = $this->getDoctrine()
            ->getRepository(Job::class)
            ->find($id);
        $em=$this->getDoctrine()->getManager();
        $listCandidatures=$em->getRepository(Candidature::class)->findBy(['Job'=>$job]);
        if (!$job) {
            throw $this->createNotFoundException(
                'No job found for id '.$id
            );
        }
        return $this->render('job/show.html.twig', [
            'listCandidatures' => $listCandidatures,
            'job' => $job
        ]);
    }

    /**
    * @Route("/editU/{id}", name="edit_user")
    * Method({"GET","POST"})
    */
    public function edit(Request $request, $id){ 
        $candidat = new Candidature();
        $candidat = $this->getDoctrine()
        ->getRepository(Candidature::class)
        ->find($id);
        if (!$candidat) {
            throw $this->createNotFoundException(
                'No candidat found for id '.$id
            );
        }
        $fb = $this->createFormBuilder($candidat)
        ->add('candidat', TextType::class)
        ->add('contenu', TextType::class, array("label" => "Contenu"))
        ->add('date', DateType::class)
        ->add('job', EntityType::class, [
        'class' => Job::class,
        'choice_label' => 'type',
        ])
        ->add('Valider', SubmitType::class);

        // générer le formulaire à partir du FormBuilder
        $form = $fb->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('home');
        }

        $data = array(
            'form' => $form->createView()
        );

        return $this->render('job/ajouter.html.twig', $data);
    }

    /**
    * @Route("/supp/{id}", name="cand_delete")
    */
    public function delete(Request $request, $id): Response {
        $c = $this->getDoctrine()
            ->getRepository(Candidature::class)
            ->find($id);
        
        if (!$c) {
            throw $this->createNotFoundException(
                'No candidature found for id '.$id
            );
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($c);
        
        $entityManager->flush();

        return $this->redirectToRoute('home');
    }
}
