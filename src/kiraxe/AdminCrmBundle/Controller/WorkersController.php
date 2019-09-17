<?php

namespace kiraxe\AdminCrmBundle\Controller;

use kiraxe\AdminCrmBundle\Entity\Workers;
use kiraxe\AdminCrmBundle\Entity\Services;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Worker controller.
 *
 */
class WorkersController extends Controller
{
    /**
     * Lists all worker entities.
     *
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $workers = $em->getRepository('kiraxeAdminCrmBundle:Workers')->findAll();

        $deleteForm = [];

        for($i = 0; $i < count($workers); $i++) {
            $deleteForm[$workers[$i]->getName()] = $this->createDeleteForm($workers[$i])->createView();
        }

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $workers, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();
        $tableName = [];
        $tableSettingsName = [];
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Workers')->getTableName()] = "Сотрудники";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Services')->getTableName()] = "Услуги";
        $tableSettingsName[$em->getClassMetadata('kiraxeAdminCrmBundle:User')->getTableName()] = "Пользователи";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Materials')->getTableName()] = "Материалы";
        $tableSettingsName[$em->getClassMetadata('kiraxeAdminCrmBundle:Measure')->getTableName()] = "Единицы измерения";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Orders')->getTableName()] = "Заказ-наряд";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Expenses')->getTableName()] = "Расход";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars = [];
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";

        return $this->render('workers/index.html.twig', array(
            'workers' => $workers,
            'delete_form' => $deleteForm,
            'pagination' => $pagination,
            'tables' => $tableName,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
        ));
    }

    /**
     * Creates a new worker entity.
     *
     */
    public function newAction(Request $request)
    {
        $workers = new Workers();

        /*$workerservice = new WorkerService();
        $workerservice->setWorkerId(1);
        $workers->getWorkerService()->add($workerservice);*/

        /*$workerservice = [];

        foreach ($request->request->all() as $key => $value) {
            if (isset($value['workerservice'])) {
                $workerservice = $value['workerservice'];
            }
        }*/

        $form = $this->createForm('kiraxe\AdminCrmBundle\Form\WorkersType', $workers);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($workers);
            $em->flush();

            return $this->redirectToRoute('workers_show', array('id' => $workers->getId()));
        }

        $em = $this->getDoctrine()->getManager();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $tableName = [];
        $tableSettingsName = [];
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Workers')->getTableName()] = "Сотрудники";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Services')->getTableName()] = "Услуги";
        $tableSettingsName[$em->getClassMetadata('kiraxeAdminCrmBundle:User')->getTableName()] = "Пользователи";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Materials')->getTableName()] = "Материалы";
        $tableSettingsName[$em->getClassMetadata('kiraxeAdminCrmBundle:Measure')->getTableName()] = "Единицы измерения";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Orders')->getTableName()] = "Заказ-наряд";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Expenses')->getTableName()] = "Расход";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars = [];
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";
        return $this->render('workers/new.html.twig', array(
            'worker' => $workers,
            'form' => $form->createView(),
            'tables' => $tableName,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
        ));
    }

    /**
     * Finds and displays a worker entity.
     *
     */
    public function showAction(Workers $workers)
    {
        $deleteForm = $this->createDeleteForm($workers);


        $originalTags = new ArrayCollection();

        // Create an ArrayCollection of the current Tag objects in the database
        foreach ($workers->getWorkerService() as $workerservice) {
            $originalTags->add($workerservice);
        }

        $em = $this->getDoctrine()->getManager();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $tableName = [];
        $tableSettingsName = [];
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Workers')->getTableName()] = "Сотрудники";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Services')->getTableName()] = "Услуги";
        $tableSettingsName[$em->getClassMetadata('kiraxeAdminCrmBundle:User')->getTableName()] = "Пользователи";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Materials')->getTableName()] = "Материалы";
        $tableSettingsName[$em->getClassMetadata('kiraxeAdminCrmBundle:Measure')->getTableName()] = "Единицы измерения";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Orders')->getTableName()] = "Заказ-наряд";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Expenses')->getTableName()] = "Расход";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars = [];
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";

        return $this->render('workers/show.html.twig', array(
            'worker' => $workers,
            'delete_form' => $deleteForm->createView(),
            'service' => $originalTags,
            'tables' => $tableName,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
        ));
    }

    /**
     * Displays a form to edit an existing worker entity.
     *
     */
    /*public function editAction(Request $request, Workers $workers)
    {
        $deleteForm = $this->createDeleteForm($workers);
        $editForm = $this->createForm('kiraxe\AdminCrmBundle\Form\WorkersType', $workers);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('workers_edit', array('id' => $workers->getId()));
        }

        return $this->render('workers/edit.html.twig', array(
            'worker' => $workers,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }*/

    public function editAction($id, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $workers = $entityManager->getRepository(Workers::class)->find($id);

        if (!$workers) {
            throw $this->createNotFoundException('No workers found for id '.$id);
        }

        $originalTags = new ArrayCollection();


        // Create an ArrayCollection of the current Tag objects in the database
        foreach ($workers->getWorkerService() as $workerservice) {
            $originalTags->add($workerservice);
        }


        $managerpercent = $workers->getManagerPercent();


        $editForm = $this->createForm('kiraxe\AdminCrmBundle\Form\WorkersType', $workers);
        $deleteForm = $this->createDeleteForm($workers);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {

            if ($workers->getTypeworkers() == 1) {
                foreach ($originalTags as $workerservice) {
                    $entityManager->persist($workerservice);
                    $entityManager->remove($workerservice);
                    $entityManager->flush();
                }
            } elseif ($workers->getTypeworkers() == 0){
                foreach ($managerpercent as $managerpercent) {
                    $entityManager->persist($managerpercent);
                    $entityManager->remove($managerpercent);
                    $entityManager->flush();
                }
                // remove the relationship between the tag and the Task
                foreach ($originalTags as $tag) {
                    if (false === $workers->getWorkerService()->contains($tag)) {
                        // remove the Task from the Tag

                        //$tag->getWorkers()->removeElement($workers);

                        // if it was a many-to-one relationship, remove the relationship like this
                        //$tag->setWorkers(null);

                        $entityManager->persist($tag);

                        // if you wanted to delete the Tag entirely, you can also do that
                        $entityManager->remove($tag);
                    }
                }
            }

            $entityManager->persist($workers);
            $entityManager->flush();

            // redirect back to some edit page
            return $this->redirectToRoute('workers_edit', ['id' => $id]);
        }

        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $hasAccess = $this->isGranted('ROLE_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();
        $tableName = [];
        $tableSettingsName = [];
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Workers')->getTableName()] = "Сотрудники";
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Services')->getTableName()] = "Услуги";
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:User')->getTableName()] = "Пользователи";
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Materials')->getTableName()] = "Материалы";
        $tableSettingsName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Measure')->getTableName()] = "Единицы измерения";
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Orders')->getTableName()] = "Заказ-наряд";
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Expenses')->getTableName()] = "Расход";
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars = [];
        $tableCars[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";
        // render some form template
        return $this->render('workers/edit.html.twig', array(
            'worker' => $workers,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
        ));
    }


    /**
     * Deletes a worker entity.
     *
     */
    public function deleteAction(Request $request, Workers $workers)
    {
        $id = $workers->getId();
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT o FROM kiraxeAdminCrmBundle:Orders o where o.workeropen = ". $id ." or o.workerclose = " . $id;
        $orders = $em->createQuery($sql)->getResult();
        if ($orders) {
            foreach($orders as $result) {

                if($result->getWorkeropen()->getId() == $id && $result->getWorkerclose()->getId() == $id) {
                    $result->setWorkerclose(null);
                    $result->setWorkeropen(null);
                } elseif ( $result->getWorkeropen()->getId() == $id) {
                    $result->setWorkeropen(null);
                } elseif ($result->getWorkerclose()->getId() == $id) {
                    $result->setWorkerclose();
                }


                foreach($result->getManagerorders() as $managerorder) {
                    if ($managerorder->getWorkers()->getId() == $id) {
                        $managerorder->setWorkers(null);
                    }
                }
            }
        }
        $form = $this->createDeleteForm($workers);
        $form->handleRequest($request);
        $workerservice = $workers->getWorkerService();
        $managerpercent = $workers->getManagerPercent();
        $workerorders = $workers->getWorkerOrders();
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            foreach($workerservice as $service) {
                $em->persist($service);
                $em->remove($service);
            }

            foreach($managerpercent as $managerpercent) {
                $em->persist($managerpercent);
                $em->remove($managerpercent);
            }

            foreach($workerorders as $workerorders) {
                $em->persist($workerorders);
                $em->remove($workerorders);
            }

            $em->remove($workers);
            $em->flush();
        }

        return $this->redirectToRoute('workers_index');
    }

    /**
     * Creates a form to delete a workers entity.
     *
     * @param Workers $workers The workers entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Workers $workers)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('workers_delete', array('id' => $workers->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
