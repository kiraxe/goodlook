<?php

namespace kiraxe\AdminCrmBundle\Controller;

use kiraxe\AdminCrmBundle\Entity\BodyType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use kiraxe\AdminCrmBundle\Services\TableMeta\TableMeta;

/**
 * Bodytype controller.
 *
 */
class BodyTypeController extends Controller
{
    /**
     * Lists all bodyType entities.
     *
     */
    public function indexAction(Request $request, TableMeta $tableMeta)
    {
        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

        $bodyTypes = $em->getRepository('kiraxeAdminCrmBundle:BodyType')->findBy(['active' => '1']);


        $user = $this->getUser();

        for($i = 0; $i < count($bodyTypes); $i++) {
            $deleteForm[$bodyTypes[$i]->getName()] = $this->createDeleteForm($bodyTypes[$i])->createView();
        }

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $bodyTypes, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            50 /*limit per page*/
        );

        return $this->render('bodytype/index.html.twig', array(
            'bodyTypes' => $bodyTypes,
            'tables' => $tableName,
            'user' => $user,
            'pagination' => $pagination,
            'delete_form' => $deleteForm
        ));
    }

    /**
     * Creates a new bodyType entity.
     *
     */
    public function newAction(Request $request, TableMeta $tableMeta)
    {
        $bodyType = new Bodytype();
        $form = $this->createForm('kiraxe\AdminCrmBundle\Form\BodyTypeType', $bodyType);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $tableName = $tableMeta->getTableName($em);

        if ($form->isSubmitted() && $form->isValid()) {
            $bodyType->setActive(true);
            $em->persist($bodyType);
            $em->flush();

            return $this->redirectToRoute('bodytype_show', array('id' => $bodyType->getId()));
        }

        return $this->render('bodytype/new.html.twig', array(
            'bodyType' => $bodyType,
            'form' => $form->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Finds and displays a bodyType entity.
     *
     */
    public function showAction(BodyType $bodyType, TableMeta $tableMeta)
    {
        $deleteForm = $this->createDeleteForm($bodyType);

        $em = $this->getDoctrine()->getManager();
        $tableName = $tableMeta->getTableName($em);
        $user = $this->getUser();


        return $this->render('bodytype/show.html.twig', array(
            'bodyType' => $bodyType,
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Displays a form to edit an existing bodyType entity.
     *
     */
    public function editAction(Request $request, BodyType $bodyType, TableMeta $tableMeta)
    {
        $deleteForm = $this->createDeleteForm($bodyType);
        $editForm = $this->createForm('kiraxe\AdminCrmBundle\Form\BodyTypeType', $bodyType);
        $editForm->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);
        $user = $this->getUser();

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('bodytype_edit', array('id' => $bodyType->getId()));
        }

        return $this->render('bodytype/edit.html.twig', array(
            'bodyType' => $bodyType,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Deletes a bodyType entity.
     *
     */
    public function deleteAction(Request $request, BodyType $bodyType)
    {
        $form = $this->createDeleteForm($bodyType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $bodyType->setActive(false);
            //$em->remove($bodyType);
            $em->flush();
        }

        return $this->redirectToRoute('bodytype_index');
    }

    /**
     * Creates a form to delete a bodyType entity.
     *
     * @param BodyType $bodyType The bodyType entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(BodyType $bodyType)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('bodytype_delete', array('id' => $bodyType->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
