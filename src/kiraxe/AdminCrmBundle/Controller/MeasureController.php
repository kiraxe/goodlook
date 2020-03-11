<?php

namespace kiraxe\AdminCrmBundle\Controller;

use kiraxe\AdminCrmBundle\Entity\Measure;
use kiraxe\AdminCrmBundle\Services\TableMeta\TableMeta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Measure controller.
 *
 */
class MeasureController extends Controller
{
    /**
     * Lists all measure entities.
     *
     */
    public function indexAction(Request $request, TableMeta $tableMeta)
    {
        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

        $measures = $em->getRepository('kiraxeAdminCrmBundle:Measure')->findBy(['active' => '1']);

        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();

        for($i = 0; $i < count($measures); $i++) {
            $deleteForm[$measures[$i]->getName()] = $this->createDeleteForm($measures[$i])->createView();
        }

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $measures, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            50 /*limit per page*/
        );

        return $this->render('measure/index.html.twig', array(
            'measures' => $measures,
            'tables' => $tableName,
            'user' => $user,
            'pagination' => $pagination,
            'delete_form' => $deleteForm,
        ));
    }

    /**
     * Creates a new measure entity.
     *
     */
    public function newAction(Request $request, TableMeta $tableMeta)
    {
        $measure = new Measure();
        $form = $this->createForm('kiraxe\AdminCrmBundle\Form\MeasureType', $measure);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);
        $hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($measure);
            $em->flush();

            return $this->redirectToRoute('measure_show', array('id' => $measure->getId()));
        }

        return $this->render('measure/new.html.twig', array(
            'measure' => $measure,
            'form' => $form->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Finds and displays a measure entity.
     *
     */
    public function showAction(Measure $measure, TableMeta $tableMeta)
    {
        $deleteForm = $this->createDeleteForm($measure);

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);
        $hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();

        return $this->render('measure/show.html.twig', array(
            'measure' => $measure,
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Displays a form to edit an existing measure entity.
     *
     */
    public function editAction(Request $request, Measure $measure, TableMeta $tableMeta)
    {
        $deleteForm = $this->createDeleteForm($measure);
        $editForm = $this->createForm('kiraxe\AdminCrmBundle\Form\MeasureType', $measure);
        $editForm->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);
        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('measure_edit', array('id' => $measure->getId()));
        }

        return $this->render('measure/edit.html.twig', array(
            'measure' => $measure,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Deletes a measure entity.
     *
     */
    public function deleteAction(Request $request, Measure $measure)
    {
        $form = $this->createDeleteForm($measure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $measure->setActive(false);
            //$em->remove($measure);
            $em->flush();
        }

        return $this->redirectToRoute('measure_index');
    }

    /**
     * Creates a form to delete a measure entity.
     *
     * @param Measure $measure The measure entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Measure $measure)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('measure_delete', array('id' => $measure->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
