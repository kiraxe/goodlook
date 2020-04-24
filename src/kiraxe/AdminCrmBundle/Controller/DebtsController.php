<?php

namespace kiraxe\AdminCrmBundle\Controller;

use kiraxe\AdminCrmBundle\Entity\Debts;
use kiraxe\AdminCrmBundle\Services\DumpDbs;
use kiraxe\AdminCrmBundle\Services\TableMeta\TableMeta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;


/**
 * Debts controller.
 *
 */
class DebtsController extends Controller
{
    /**
     * Lists all debts entities.
     *
     */
    public function indexAction(Request $request, TableMeta $tableMeta)
    {
        //$dbs->openFile('my_dump');
        //print_r($dbs->getDump());

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

        $debts = $em->getRepository('kiraxeAdminCrmBundle:Debts')->findBy(array(),array('date' => 'DESC'));//findAll();

        $deleteForm = [];

        for($i = 0; $i < count($debts); $i++) {
            $deleteForm[$debts[$i]->getId()] = $this->createDeleteForm($debts[$i])->createView();
        }

        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();

        $sqlDebts = "SELECT e FROM kiraxeAdminCrmBundle:Debts e where";

        if (!empty($request->query->get('form')['dateFrom']) && empty($request->query->get('form')['dateTo'])) {
            $dateFrom = $request->query->get('form')['dateFrom'];
            $dateFrom = str_replace("-", "", $dateFrom);
            $sqlDebts .= " date(e.date) =".$dateFrom;
        }

        if (empty($request->query->get('form')['dateFrom']) && !empty($request->query->get('form')['dateTo'])) {
            $dateTo = $request->query->get('form')['dateTo'];
            $dateTo = str_replace("-", "", $dateTo);
            $sqlDebts .= " date(e.date) =".$dateTo;
        }

        if (!empty($request->query->get('form')['dateFrom']) && !empty($request->query->get('form')['dateTo'])) {
            $dateFrom = $request->query->get('form')['dateFrom'];
            $dateTo = $request->query->get('form')['dateTo'];
            $dateFrom = str_replace("-", "", $dateFrom);
            $dateTo = str_replace("-", "", $dateTo);
            $sqlDebts .= " date(e.date) between " . $dateFrom . " and " . $dateTo;
        }

        if (!empty($request->query->get('form')['dateFrom'])) {
            $debts = $em->createQuery($sqlDebts)->getResult();
        }

        $form = $this->get("form.factory")->createNamedBuilder("form")
            ->setMethod('GET')
            ->add('dateFrom', TextType::class ,array(
                'label' => 'с',
                'required' => false,
            ))
            ->add('dateTo', TextType::class ,array(
                'label' => 'по',
                'required' => false,
            ))
            ->getForm();


        $form->handleRequest($request);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $debts, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            50 /*limit per page*/
        );


        return $this->render('debts/index.html.twig', array(
            'debts' => $debts,
            'form' => $form->createView(),
            'delete_form' => $deleteForm,
            'tables' => $tableName,
            'pagination' => $pagination,
            'user' => $user,
        ));
    }

    /**
     * Creates a new debts entity.
     *
     */
    public function newAction(Request $request, TableMeta $tableMeta)
    {
        $debts = new Debts();
        $form = $this->createForm('kiraxe\AdminCrmBundle\Form\DebtsType', $debts);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

        $hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();


        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($debts);
            $em->flush();

            return $this->redirectToRoute('debts_show', array('id' => $debts->getId()));
        }

        return $this->render('debts/new.html.twig', array(
            'debts' => $debts,
            'form' => $form->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Finds and displays a debts entity.
     *
     */
    public function showAction(Debts $debts, TableMeta $tableMeta)
    {
        $deleteForm = $this->createDeleteForm($debts);

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

        $hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();


        return $this->render('debts/show.html.twig', array(
            'debts' => $debts,
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Displays a form to edit an existing debts entity.
     *
     */
    public function editAction(Request $request, Debts $debts, TableMeta $tableMeta)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($entityManager);
        $deleteForm = $this->createDeleteForm($debts);
        $editForm = $this->createForm('kiraxe\AdminCrmBundle\Form\DebtsType', $debts);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('debts_edit', array('id' => $debts->getId()));
        }

        $hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();


        return $this->render('debts/edit.html.twig', array(
            'debts' => $debts,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Deletes a debts entity.
     *
     */
    public function deleteAction(Request $request, Debts $debts)
    {
        $form = $this->createDeleteForm($debts);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($debts);
            $em->flush();
        }

        return $this->redirectToRoute('debts_index');
    }

    /**
     * Creates a form to delete a debts entity.
     *
     * @param Debts $debts The debts entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Debts $debts)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('debts_delete', array('id' => $debts->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}
