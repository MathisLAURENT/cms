<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Page;

class EditionController extends AbstractController
{
    /**
     * @Route("/edition", name="accueil")
     */
    public function edition(Connection $connection)
    {
        // On va chercher dans la base de données toutes les pages ...

        $pages = $connection->fetchAll('SELECT  pr.title, p.published, p.slug, p.id
                                             FROM page AS p
                                             LEFT JOIN page_revision AS pr  ON p.id = pr.page_id  
                                             LEFT JOIN page_revision AS pr2  ON pr.page_id = pr2.page_id AND pr.date_created < pr2.date_created
                                             WHERE  pr2.id IS NULL  
                                             ORDER BY p.id');

        // ... et on les envoie dans le twig pour les afficher.
        return $this->render('edition/edition.html.twig',[
                        'pages' => $pages,
                    ]);
    }

}


