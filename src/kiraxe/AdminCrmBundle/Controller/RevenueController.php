<?php

namespace kiraxe\AdminCrmBundle\Controller;

use kiraxe\AdminCrmBundle\Entity\Revenue;
use kiraxe\AdminCrmBundle\Services\DumpDbs;
use kiraxe\AdminCrmBundle\Services\TableMeta\TableMeta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;


/**
 * Revenue controller.
 *
 */
class RevenueController extends Controller
{
    /**
     * Lists all revenue entities.
     *
     */
    public function indexAction(Request $request, TableMeta $tableMeta)
    {
        //$dbs->openFile('my_dump');
        //print_r($dbs->getDump());

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

        $revenue = $em->getRepository('kiraxeAdminCrmBundle:Revenue')->findBy(array(),array('date' => 'DESC'));//findAll();

        $deleteForm = [];

        for($i = 0; $i < count($revenue); $i++) {
            $deleteForm[$revenue[$i]->getId()] = $this->createDeleteForm($revenue[$i])->createView();
        }

        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();

        $sqlRevenue = "SELECT e FROM kiraxeAdminCrmBundle:Revenue e where";

        if (!empty($request->query->get('form')['dateFrom']) && empty($request->query->get('form')['dateTo'])) {
            $dateFrom = $request->query->get('form')['dateFrom'];
            $dateFrom = str_replace("-", "", $dateFrom);
            $sqlRevenue .= " date(e.date) =".$dateFrom;
        }

        if (empty($request->query->get('form')['dateFrom']) && !empty($request->query->get('form')['dateTo'])) {
            $dateTo = $request->query->get('form')['dateTo'];
            $dateTo = str_replace("-", "", $dateTo);
            $sqlRevenue .= " date(e.date) =".$dateTo;
        }

        if (!empty($request->query->get('form')['dateFrom']) && !empty($request->query->get('form')['dateTo'])) {
            $dateFrom = $request->query->get('form')['dateFrom'];
            $dateTo = $request->query->get('form')['dateTo'];
            $dateFrom = str_replace("-", "", $dateFrom);
            $dateTo = str_replace("-", "", $dateTo);
            $sqlRevenue .= " date(e.date) between " . $dateFrom . " and " . $dateTo;
        }

        if (!empty($request->query->get('form')['dateFrom'])) {
            $revenue = $em->createQuery($sqlRevenue)->getResult();
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
            $revenue, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            50 /*limit per page*/
        );

        return $this->render('revenue/index.html.twig', array(
            'revenue' => $revenue,
            'form' => $form->createView(),
            'delete_form' => $deleteForm,
            'tables' => $tableName,
            'pagination' => $pagination,
            'user' => $user,
        ));
    }

    /**
     * Creates a new revenue entity.
     *
     */
    public function newAction(Request $request, TableMeta $tableMeta)
    {
        $revenue = new Revenue();
        $form = $this->createForm('kiraxe\AdminCrmBundle\Form\RevenueType', $revenue);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

        $hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();


        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($revenue);
            $em->flush();

            return $this->redirectToRoute('revenue_show', array('id' => $revenue->getId()));
        }

        return $this->render('revenue/new.html.twig', array(
            'revenue' => $revenue,
            'form' => $form->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Finds and displays a revenue entity.
     *
     */
    public function showAction(Revenue $revenue, TableMeta $tableMeta)
    {
        $deleteForm = $this->createDeleteForm($revenue);

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

        $hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();


        return $this->render('revenue/show.html.twig', array(
            'revenue' => $revenue,
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Displays a form to edit an existing revenue entity.
     *
     */
    public function editAction(Request $request, Revenue $revenue, TableMeta $tableMeta)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($entityManager);
        $deleteForm = $this->createDeleteForm($revenue);
        $editForm = $this->createForm('kiraxe\AdminCrmBundle\Form\RevenueType', $revenue);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('revenue_edit', array('id' => $revenue->getId()));
        }

        $hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();


        return $this->render('revenue/edit.html.twig', array(
            'revenue' => $revenue,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Deletes a revenue entity.
     *
     */
    public function deleteAction(Request $request, Revenue $revenue)
    {
        $form = $this->createDeleteForm($revenue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($revenue);
            $em->flush();
        }

        return $this->redirectToRoute('revenue_index');
    }

    /**
     * Creates a form to delete a revenue entity.
     *
     * @param Revenue $revenue The revenue entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Revenue $revenue)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('revenue_delete', array('id' => $revenue->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}
