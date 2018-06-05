<?php
/**
 * Created by PhpStorm.
 * User: P-OPTI-3
 * Date: 02/05/2018
 * Time: 09:44
 */

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use App\Entity\Utilisateur;

// Ce controlleur permet juste de creer un utilisateur statique. La création d'utilisateurs sera possiblement un fonctionnalité future,
// mais cela ne m'a pas été demandé. J'en ai eu besoin pour la base de données.

class CreerUtilisateurController extends AbstractController
{

    /**
     * @Route("/utilisateur", name="utilisateur")
     */
    function creerUtilisateur(){


        $utilisateur = new User();
        $utilisateur->setFirstName("Mathis");
        $utilisateur->setLastName("Laurent");

        $em = $this->getDoctrine()->getManager();
        $em->persist($utilisateur);
        $em->flush();

        return $this->redirectToRoute('edition');

    }





}