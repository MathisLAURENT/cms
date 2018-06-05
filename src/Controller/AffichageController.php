<?php
/**
 * Created by PhpStorm.
 * User: P-OPTI-3
 * Date: 19/04/2018
 * Time: 11:59
 */

namespace App\Controller;


use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Page;

class AffichageController extends AbstractController
{

    /**
     * @Route("/page/{slug}", name="page")
     */
    public function affichage(Connection $connection, $slug)
    {

         // On va chercher dans la base de données la révision la plus récente de la page demandée
         $sql ='SELECT  pr.title, p.slug, pr.content, p.published
                FROM page AS p
                LEFT JOIN page_revision AS pr  ON p.id = pr.page_id
                LEFT JOIN page_revision AS pr2  ON pr.page_id = pr2.page_id AND pr.date_created < pr2.date_created
                WHERE  pr2.id IS NULL
                AND p.slug = ?';

        $stmt = $connection->prepare($sql);
        $stmt->bindValue(1, $slug);
        $stmt->execute();
        $page = $stmt->fetchAll();

        // Si on essaye d'acceder à la page alors qu'elle n'a pas l'attribut visible ou qu'elle n'existe pas, on redirige vers une page 404
        if(count($page)==0 || $page[0]['published']==0){  return $this->render('bundles/TwigBundle/Exception/error404.html.twig'); }

        // On affiche la vue avec la page en paramètre
        return $this->render('affichage/affichage.html.twig',[
            'page' => $page,
        ]);

    }

}