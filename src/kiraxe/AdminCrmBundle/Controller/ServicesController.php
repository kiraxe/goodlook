<?php

namespace kiraxe\AdminCrmBundle\Controller;

use kiraxe\AdminCrmBundle\Entity\Services;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Service controller.
 *
 */
class ServicesController extends Controller
{
    /**
     * Lists all service entities.
     *
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /*$services = $em->getRepository('kiraxeAdminCrmBundle:Services');

        $services = $services->createQueryBuilder('s1')
            ->join('s1.childrens', 's2', Join::WITH , 's1.id = s2.parent')
            ->getQuery()->getResult();
         */
        $services = $em->createQuery(
            'SELECT s1, s2 FROM kiraxeAdminCrmBundle:Services s1 JOIN kiraxeAdminCrmBundle:Services s2 WITH s1.id = s2.parent WHERE s1.active = 1 AND s2.active = 1 ORDER BY s2.parent ASC'
        )->getResult();

        $servicesNotChild = $em->createQuery(
            'SELECT s3 FROM kiraxeAdminCrmBundle:Services s3 WHERE s3.childrens IS EMPTY AND s3.active = 1 AND s3.parent IS NULL'
        )->getResult();

        $services = array_merge($services, $servicesNotChild);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $services, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            50 /*limit per page*/
        );

        $deleteForm = [];

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $tableName = [];
        $tableSettingsName = [];
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Workers')->getTableName()] = "Сотрудники";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Services')->getTableName()] = "Услуги";
        $tableSettingsName[$em->getClassMetadata('kiraxeAdminCrmBundle:User')->getTableName()] = "Пользователи";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Materials')->getTableName()] = "Материалы";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Orders')->getTableName()] = "Заказ-наряд";
        $tableSettingsName[$em->getClassMetadata('kiraxeAdminCrmBundle:Measure')->getTableName()] = "Единицы измерения";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Expenses')->getTableName()] = "Расход";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Clientele')->getTableName()] = "Клиенты";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars = [];
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";

        for($i = 0; $i < count($services); $i++) {
            $deleteForm[$services[$i]->getId()] = $this->createDeleteForm($services[$i])->createView();
        }

        return $this->render('services/index.html.twig', array(
            'services' => $services,
            'delete_form' => $deleteForm,
            'tables' => $tableName,
            'pagination' => $pagination,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
        ));
    }

    /**
     * Creates a new service entity.
     *
     */
    public function newAction(Request $request)
    {
        $service = new Services();
        $form = $this->createForm('kiraxe\AdminCrmBundle\Form\ServicesType', $service, [
            'obj_id' => $service->getId(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $service->setActive(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($service);
            $em->flush();

            return $this->redirectToRoute('services_show', array('id' => $service->getId()));
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
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Clientele')->getTableName()] = "Клиенты";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars = [];
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";


        return $this->render('services/new.html.twig', array(
            'service' => $service,
            'form' => $form->createView(),
            'tables' => $tableName,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
        ));
    }

    /**
     * Finds and displays a service entity.
     *
     */
    public function showAction(Services $service)
    {
        $deleteForm = $this->createDeleteForm($service);
        $em = $this->getDoctrine()->getManager();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $tableName = [];
        $tableSettingsName = [];
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Workers')->getTableName()] = "Сотрудники";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Services')->getTableName()] = "Услуги";
        $tableSettingsName[$em->getClassMetadata('kiraxeAdminCrmBundle:User')->getTableName()] = "Пользователи";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Materials')->getTableName()] = "Материалы";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Orders')->getTableName()] = "Заказ-наряд";
        $tableSettingsName[$em->getClassMetadata('kiraxeAdminCrmBundle:Measure')->getTableName()] = "Единицы измерения";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Expenses')->getTableName()] = "Расход";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Clientele')->getTableName()] = "Клиенты";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars = [];
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";

        return $this->render('services/show.html.twig', array(
            'service' => $service,
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
        ));
    }

    /**
     * Displays a form to edit an existing service entity.
     *
     */
    public function editAction(Request $request, Services $service)
    {
        $deleteForm = $this->createDeleteForm($service);
        $editForm = $this->createForm('kiraxe\AdminCrmBundle\Form\ServicesType', $service, [
            'obj_id' => $service->getId(),
        ]);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('services_edit', array('id' => $service->getId()));
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
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Orders')->getTableName()] = "Заказ-наряд";
        $tableSettingsName[$em->getClassMetadata('kiraxeAdminCrmBundle:Measure')->getTableName()] = "Единицы измерения";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Expenses')->getTableName()] = "Расход";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Clientele')->getTableName()] = "Клиенты";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars = [];
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";

        return $this->render('services/edit.html.twig', array(
            'service' => $service,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
        ));
    }

    /**
     * Deletes a service entity.
     *
     */
    public function deleteAction(Request $request, Services $service)
    {
        $form = $this->createDeleteForm($service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /*if ($service->getParent()) {
                $service->setParent(null);
            }*/
            /*foreach($service->getWorkerOrders() as $order) {
                $order->setServices(null);
                $order->setServicesparent(null);
            }*/

            if ($service->getChildrens()) {
                foreach ($service->getChildrens() as $children) {
                    $children->setActive(false);
                }
            }
            $service->setActive(false);
            //$em->remove($service);
            $em->flush();
        }

        return $this->redirectToRoute('services_index');
    }

    /**
     * Creates a form to delete a service entity.
     *
     * @param Services $service The service entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Services $service)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('services_delete', array('id' => $service->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
