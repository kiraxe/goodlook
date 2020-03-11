<?php

namespace kiraxe\AdminCrmBundle\Controller;

use kiraxe\AdminCrmBundle\Entity\Clientele;
use kiraxe\AdminCrmBundle\Services\TableMeta\TableMeta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use League\Csv\CharsetConverter;


/**
 * Clientele controller.
 *
 */
class ClienteleController extends Controller
{
    /**
     * Lists all clientele entities.
     *
     */
    public function indexAction(Request $request, TableMeta $tableMeta)
    {
        $em = $this->getDoctrine()->getManager();
        $tableName = $tableMeta->getTableName($em);
        $deleteForm = null;

        $clientele = $em->getRepository('kiraxeAdminCrmBundle:Clientele')->findAll();

        $user = $this->getUser();

        for($i = 0; $i < count($clientele); $i++) {
            $deleteForm[$clientele[$i]->getName()] = $this->createDeleteForm($clientele[$i])->createView();
        }

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $clientele, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            50 /*limit per page*/
        );

        return $this->render('clientele/index.html.twig', array(
            'clientele' => $clientele,
            'tables' => $tableName,
            'user' => $user,
            'pagination' => $pagination,
            'delete_form' => $deleteForm
        ));
    }

    /**
     * Creates a new clientele entity.
     *
     */
    public function newAction(Request $request, TableMeta $tableMeta)
    {
        $clientele = new Clientele();
        $form = $this->createForm('kiraxe\AdminCrmBundle\Form\ClienteleType', $clientele);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $tableName = $tableMeta->getTableName($em);
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($clientele);
            $em->flush();

            return $this->redirectToRoute('clientele_show', array('id' => $clientele->getId()));
        }

        return $this->render('clientele/new.html.twig', array(
            'clientele' => $clientele,
            'form' => $form->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Finds and displays a clientele entity.
     *
     */
    public function showAction(Clientele $clientele, TableMeta $tableMeta)
    {
        $em = $this->getDoctrine()->getManager();
        $tableName = $tableMeta->getTableName($em);
        $user = $this->getUser();


        $deleteForm = $this->createDeleteForm($clientele);

        return $this->render('clientele/show.html.twig', array(
            'clientele' => $clientele,
            'tables' => $tableName,
            'user' => $user,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing clientele entity.
     *
     */
    public function editAction(Request $request, Clientele $clientele, TableMeta $tableMeta)
    {
        $deleteForm = $this->createDeleteForm($clientele);
        $editForm = $this->createForm('kiraxe\AdminCrmBundle\Form\ClienteleType', $clientele);
        $editForm->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $tableName = $tableMeta->getTableName($em);
        $user = $this->getUser();

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('clientele_edit', array('id' => $clientele->getId()));
        }

        return $this->render('clientele/edit.html.twig', array(
            'clientele' => $clientele,
            'tables' => $tableName,
            'user' => $user,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a clientele entity.
     *
     */
    public function deleteAction(Request $request, Clientele $clientele)
    {
        $form = $this->createDeleteForm($clientele);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($clientele);
            $em->flush();
        }

        return $this->redirectToRoute('clientele_index');
    }

    /**
     * Creates a form to delete a clientele entity.
     *
     * @param Clientele $clientele The clientele entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Clientele $clientele)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('clientele_delete', array('id' => $clientele->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    public function exportAction() {

        $em = $this->getDoctrine()->getManager();
        $clientele = $em->getRepository('kiraxeAdminCrmBundle:Clientele')->findAll();

        $encoder = (new CharsetConverter())
            ->inputEncoding('utf-8')
            ->outputEncoding('Windows-1251')
        ;

        $today = date("d.m.y");

        $writer = $this->container->get('egyg33k.csv.writer');
        $csv = $writer::createFromFileObject(new \SplTempFileObject());
        $csv->addFormatter($encoder);
        $csv->setDelimiter(";");
        $csv->insertOne(['ФИО', 'Авто', 'Номер', 'VIN', 'Телефон', 'Почта']);
        foreach ( $clientele as $client) {
            $csv->insertOne([$client->getName(), $client->getAvto(), $client->getNumber(), $client->getVin(), $client->getPhone(), $client->getEmail()]);
        }
        $csv->output('clientele_'.$today.'.csv');

        exit;
    }
}
