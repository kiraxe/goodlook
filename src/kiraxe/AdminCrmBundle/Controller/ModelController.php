<?php

namespace kiraxe\AdminCrmBundle\Controller;

use kiraxe\AdminCrmBundle\Entity\Model;
use kiraxe\AdminCrmBundle\Services\TableMeta\TableMeta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Model controller.
 *
 */
class ModelController extends Controller
{
    /**
     * Lists all model entities.
     *
     */
    public function indexAction(Request $request, TableMeta $tableMeta)
    {
        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

        $models = $em->getRepository('kiraxeAdminCrmBundle:Model')->findBy(array('active' => '1'), array('brand' => 'ASC'));

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $models, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            50 /*limit per page*/
        );

        $user = $this->getUser();

        for($i = 0; $i < count($models); $i++) {
            $deleteForm[$models[$i]->getName()] = $this->createDeleteForm($models[$i])->createView();
        }


        return $this->render('model/index.html.twig', array(
            'models' => $models,
            'tables' => $tableName,
            'pagination' => $pagination,
            'user' => $user,
            'delete_form' => $deleteForm
        ));
    }

    /**
     * Creates a new model entity.
     *
     */
    public function newAction(Request $request, TableMeta $tableMeta)
    {
        $model = new Model();
        $form = $this->createForm('kiraxe\AdminCrmBundle\Form\ModelType', $model);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $model->setActive(true);
            $em->persist($model);
            $em->flush();

            return $this->redirectToRoute('model_show', array('id' => $model->getId()));
        }

        return $this->render('model/new.html.twig', array(
            'model' => $model,
            'form' => $form->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Finds and displays a model entity.
     *
     */
    public function showAction(Model $model, TableMeta $tableMeta)
    {
        $deleteForm = $this->createDeleteForm($model);

        $em = $this->getDoctrine()->getManager();
        $tableName = $tableMeta->getTableName($em);
        $user = $this->getUser();

        return $this->render('model/show.html.twig', array(
            'model' => $model,
            'tables' => $tableName,
            'user' => $user,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing model entity.
     *
     */
    public function editAction(Request $request, Model $model, TableMeta $tableMeta)
    {
        $deleteForm = $this->createDeleteForm($model);
        $editForm = $this->createForm('kiraxe\AdminCrmBundle\Form\ModelType', $model);
        $editForm->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);
        $user = $this->getUser();

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('model_edit', array('id' => $model->getId()));
        }

        return $this->render('model/edit.html.twig', array(
            'model' => $model,
            'edit_form' => $editForm->createView(),
            'tables' => $tableName,
            'user' => $user,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a model entity.
     *
     */
    public function deleteAction(Request $request, Model $model)
    {
        $form = $this->createDeleteForm($model);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $model->setActive(false);
            //$em->remove($model);
            $em->flush();
        }

        return $this->redirectToRoute('model_index');
    }

    /**
     * Creates a form to delete a model entity.
     *
     * @param Model $model The model entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Model $model)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('model_delete', array('id' => $model->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
