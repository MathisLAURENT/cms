<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Connection;

class RevisionController extends Controller
{
    /**
     * @Route("/revision/{slug}", name="revisions")
     */
    public function afficherRevisions(Connection $connection, $slug)
    {
        // On va chercher dans la base de données la révision la plus récente de la page demandée
        $sql ='SELECT pr.date_created, pr.title, pr.id, p.slug
               FROM page_revision AS pr
               INNER JOIN page AS p ON  pr.page_id = p.id
               AND p.slug = ?
               ORDER BY pr.date_created DESC';

        $stmt = $connection->prepare($sql);
        $stmt->bindValue(1, $slug);
        $stmt->execute();
        $revisions = $stmt->fetchAll();

        // On envoie toutes les révisions dans la vue pour les afficher
        return $this->render('revision/revisionsPage.html.twig', [
            'revisions' => $revisions,
        ]);
    }


    /**
     * @Route("/revision/{slug}/{id}", name="revisionPage")
     */
    public function afficherRevisionPage(Connection $connection, $slug, $id)
    {

        // On va chercher dans la BDD la révision demandée.
        $sql ='SELECT *
               FROM page
               LEFT JOIN page_revision ON page.id = page_revision.page_id
               AND page.slug = ?
               AND page_revision.id = ?
               ORDER BY page_revision.date_created DESC
               LIMIT 1;';

        $stmt = $connection->prepare($sql);
        $stmt->bindValue(1, $slug);
        $stmt->bindValue(2, $id);
        $stmt->execute();
        $revision = $stmt->fetchAll();

        // On envoie la révision dans la vue
        return $this->render('revision/revisionPage.html.twig', [
            'revision' => $revision,
            'slug' => $slug
        ]);
    }






    /**
     * @Route("/revision/{slug}/{id}/restaurer", name="restaurerRevision")
     */
    public function restaurerRevision(Connection $connection, $slug, $id)
    {

        // Permet de restaurer une révision choisie. (On update juste la date de l'ancienne révision à celle de maintenant)
        $sql ='UPDATE page_revision SET date_created = CURRENT_TIMESTAMP WHERE id = ?';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(1, $id);
        $stmt->execute();

        // On redirige vers la page d'édition
        return $this->redirectToRoute('edition');

    }




}
