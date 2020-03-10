<?php

namespace kiraxe\AdminCrmBundle\Controller;

use kiraxe\AdminCrmBundle\Entity\Model;
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
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $models = $em->getRepository('kiraxeAdminCrmBundle:Model')->findBy(array('active' => '1'), array('brand' => 'ASC'));

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $models, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            50 /*limit per page*/
        );

        $user = $this->getUser();
        $tableName = [];
        $tableSettingsName = [];
        $tableCars = [];
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Workers')->getTableName()] = "Сотрудники";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Services')->getTableName()] = "Услуги";
        $tableSettingsName[$em->getClassMetadata('kiraxeAdminCrmBundle:User')->getTableName()] = "Пользователи";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Materials')->getTableName()] = "Материалы";
        $tableSettingsName[$em->getClassMetadata('kiraxeAdminCrmBundle:Measure')->getTableName()] = "Единицы измерения";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Orders')->getTableName()] = "Заказ-наряд";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Expenses')->getTableName()] = "Расход";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Clientele')->getTableName()] = "Клиенты";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";
        for($i = 0; $i < count($models); $i++) {
            $deleteForm[$models[$i]->getName()] = $this->createDeleteForm($models[$i])->createView();
        }


        return $this->render('model/index.html.twig', array(
            'models' => $models,
            'tables' => $tableName,
            'pagination' => $pagination,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
            'delete_form' => $deleteForm
        ));
    }

    /**
     * Creates a new model entity.
     *
     */
    public function newAction(Request $request)
    {
        $model = new Model();
        $form = $this->createForm('kiraxe\AdminCrmBundle\Form\ModelType', $model);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tableName = [];
        $tableSettingsName = [];
        $tableCars = [];
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Workers')->getTableName()] = "Сотрудники";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Services')->getTableName()] = "Услуги";
        $tableSettingsName[$em->getClassMetadata('kiraxeAdminCrmBundle:User')->getTableName()] = "Пользователи";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Materials')->getTableName()] = "Материалы";
        $tableSettingsName[$em->getClassMetadata('kiraxeAdminCrmBundle:Measure')->getTableName()] = "Единицы измерения";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Orders')->getTableName()] = "Заказ-наряд";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Expenses')->getTableName()] = "Расход";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Clientele')->getTableName()] = "Клиенты";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";
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
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars
        ));
    }

    /**
     * Finds and displays a model entity.
     *
     */
    public function showAction(Model $model)
    {
        $deleteForm = $this->createDeleteForm($model);

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tableName = [];
        $tableSettingsName = [];
        $tableCars = [];
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Workers')->getTableName()] = "Сотрудники";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Services')->getTableName()] = "Услуги";
        $tableSettingsName[$em->getClassMetadata('kiraxeAdminCrmBundle:User')->getTableName()] = "Пользователи";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Materials')->getTableName()] = "Материалы";
        $tableSettingsName[$em->getClassMetadata('kiraxeAdminCrmBundle:Measure')->getTableName()] = "Единицы измерения";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Orders')->getTableName()] = "Заказ-наряд";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Expenses')->getTableName()] = "Расход";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Clientele')->getTableName()] = "Клиенты";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";
        return $this->render('model/show.html.twig', array(
            'model' => $model,
            'tables' => $tableName,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing model entity.
     *
     */
    public function editAction(Request $request, Model $model)
    {
        $deleteForm = $this->createDeleteForm($model);
        $editForm = $this->createForm('kiraxe\AdminCrmBundle\Form\ModelType', $model);
        $editForm->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tableName = [];
        $tableSettingsName = [];
        $tableCars = [];
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Workers')->getTableName()] = "Сотрудники";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Services')->getTableName()] = "Услуги";
        $tableSettingsName[$em->getClassMetadata('kiraxeAdminCrmBundle:User')->getTableName()] = "Пользователи";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Materials')->getTableName()] = "Материалы";
        $tableSettingsName[$em->getClassMetadata('kiraxeAdminCrmBundle:Measure')->getTableName()] = "Единицы измерения";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Orders')->getTableName()] = "Заказ-наряд";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Expenses')->getTableName()] = "Расход";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Clientele')->getTableName()] = "Клиенты";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('model_edit', array('id' => $model->getId()));
        }

        return $this->render('model/edit.html.twig', array(
            'model' => $model,
            'edit_form' => $editForm->createView(),
            'tables' => $tableName,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
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
