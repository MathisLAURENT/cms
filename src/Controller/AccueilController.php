<?php
/**
 * Created by PhpStorm.
 * User: P-OPTI-3
 * Date: 03/05/2018
 * Time: 10:36
 */

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Page;

class AccueilController extends AbstractController
{

    /**
     * @Route("/accueil", name="accueil")
     */
    public function afficherAccueil()
    {
        return $this->render('accueil/accueil.html.twig');
    }



}