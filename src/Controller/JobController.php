<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Job;
use App\Entity\Candidature;


class JobController extends AbstractController
{
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
}
