<?php
/**
 * Created by PhpStorm.
 * User: P-OPTI-3
 * Date: 17/04/2018
 * Time: 16:12
 */

namespace App\Controller;

use App\Entity\PageRevision;
use Doctrine\DBAL\Connection;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Page;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Doctrine\ORM\Mapping as ORM;

class ModificationController extends AbstractController
{

    /**
     * @Route("/ajout", name="ajout")
     */
    public function nouvellePage(Request $request, Connection $connection)
    {

        // Description de la fonctionnalité
        $descFonction = "Ajout d'une nouvelle page";

        // Création du formulaire
        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            ->add('Titre', TextType::class)
            ->add('Contenu', CKEditorType::class)
            ->add('URL', TextType::class)
            ->add('MetaTitle', TextareaType::class)
            ->add('MetaDescription', TextareaType::class)
            ->add('Visible', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('envoi', SubmitType::class, array('label' => 'Creer Page'))
            ->getForm();

        $form->handleRequest($request);

        // A la confirmation du formulaire
        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            // Création de la page
            $page = new Page();
            $page->setSlug($form->getData()['URL']);
            $page->setPublished($form->getData()['Visible']);

            $em->persist($page);
            $em->flush();


            // Création de la révision
            $revision_page = new PageRevision();

            $revision_page->setTitle($form->getData()['Titre']);
            $revision_page->setContent($form->getData()['Contenu']);
            $revision_page->setMetaTitle($form->getData()['MetaTitle']);
            $revision_page->setMetaDescription($form->getData()['MetaDescription']);
            $revision_page->setUserId(1);
            $revision_page->setPageId($page->getId());

            $em->persist($revision_page);
            $em->flush();

            // On redirige vers la page d'edition
            return $this->redirectToRoute('edition');

        }

        // On redirige vers la page de modification (qui sert aussi de page d'ajout)
        return $this->render('modification/modification.html.twig', array(
            'form' => $form->createView(),
            'descFonction' => $descFonction
        ));
    }



    /**
     * @Route("/modification/{slug}", name="modification")
     */
    public function modifierPage(Request $request, Connection $connection,  $slug)
    {

        // On cherche dans la base de données les informations sur la page demandée
        $sql ='SELECT pr.date_created, pr.title, pr.id AS idRevision, p.slug, p.published, p.id AS idPage, pr.content, pr.meta_description, pr.meta_title
               FROM page_revision AS pr
               INNER JOIN page AS p ON pr.page_id = p.id
               AND p.slug = ?
               ORDER BY pr.date_created DESC;';

        $stmt = $connection->prepare($sql);
        $stmt->bindValue(1, $slug);
        $stmt->execute();
        $revision = $stmt->fetchAll();


        // Si le resultat de la requête est nulle , la page n'existe pas, on affiche donc une page 404 (cas où l'utilisateur rentre l'url à la main)
        if(count($revision)==0){  return $this->render('bundles/TwigBundle/Exception/error404.html.twig'); }

        // Description de la fonctionnalité
        $descFonction = "Modification de la page \"" . $revision[0]['title'] . "\"";


        // Initialisation du formulaire
        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            ->add('Titre', TextType::class)
            ->add('Contenu', CKEditorType::class)
            ->add('URL', TextType::class)
            ->add('MetaTitle', TextareaType::class)
            ->add('MetaDescription', TextareaType::class)
            ->add('Visible', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('envoi', SubmitType::class, array('label' => 'Creer Page'))
            ->getForm();


        // On remplit les champs du formulaire avec les informations de la page récupérée dans la BDD
        $form->get('Titre')->setData($revision[0]['title']);
        $form->get('Contenu')->setData($revision[0]['content']);
        $form->get('Visible')->setData((boolean)$revision[0]['published']);
        $form->get('URL')->setData($revision[0]['slug']);
        $form->get('MetaTitle')->setData($revision[0]['meta_title']);
        $form->get('MetaDescription')->setData($revision[0]['meta_description']);

        $form->handleRequest($request);

        // A la confirmation du formulaire
        if($form->isSubmitted() && $form->isValid()){

            // Ajout d'une nouvelle révision
            $sql ='INSERT INTO page_revision(id, date_created, title, content, meta_title, meta_description, user_id, page_id) VALUES (NULL, CURRENT_TIMESTAMP, ?, ?, ?, ?, 1, ?);';
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $form->getData()['Titre']);
            $stmt->bindValue(2, $form->getData()['Contenu']);
            $stmt->bindValue(3, $form->getData()['MetaTitle']);
            $stmt->bindValue(4, $form->getData()['MetaDescription']);
            $stmt->bindValue(5, $revision[0]['idPage']);
            $stmt->execute();


            // Mise a jour de la page (pour l'URL et la visibilité)
            if ($form->getData()['URL']!=$revision[0]['slug'] || (int)$form->getData()['Visible']!=(boolean)$revision[0]['published']) // Si l'utilisateur n'a pas modifié l'URL ou la visibilité, on ne fait pas d'update.
            {
                $sql = "UPDATE page SET slug = ?, published = ?  WHERE id = ?";
                $stmt = $connection->prepare($sql);
                $stmt->bindValue(1, $form->getData()['URL']);
                $stmt->bindValue(2, (int)$form->getData()['Visible']);
                $stmt->bindValue(3, $revision[0]['idPage']);
                $stmt->execute();
            }

            // Et on renvoie vers la page d'édition
            return $this->redirectToRoute('edition');

        }


        // On envoie vers la page de modification
        return $this->render('modification/modification.html.twig', array(
            'form' => $form->createView(),
            'descFonction' => $descFonction
        ));
    }




    /**
     * @Route("/supprimer/{idPage}", name="supprimer")
     */
    public function supprimerPage(Connection $connection, $idPage)
    {

        // Fonctionnalité permettant de supprimer une page et toutes ses révisions associées
        $sql = "DELETE FROM page WHERE id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(1, $idPage);
        $stmt->execute();

        // On renvoie vers la route d'édition
        return $this->redirectToRoute('edition');

    }


}