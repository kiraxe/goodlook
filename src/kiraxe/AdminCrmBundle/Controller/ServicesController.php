<?php

namespace kiraxe\AdminCrmBundle\Controller;

use kiraxe\AdminCrmBundle\Entity\Services;
use kiraxe\AdminCrmBundle\Services\TableMeta\TableMeta;
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
    public function indexAction(Request $request, TableMeta $tableMeta)
    {
        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

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


        for($i = 0; $i < count($services); $i++) {
            $deleteForm[$services[$i]->getId()] = $this->createDeleteForm($services[$i])->createView();
        }

        return $this->render('services/index.html.twig', array(
            'services' => $services,
            'delete_form' => $deleteForm,
            'tables' => $tableName,
            'pagination' => $pagination,
            'user' => $user,
        ));
    }

    /**
     * Creates a new service entity.
     *
     */
    public function newAction(Request $request, TableMeta $tableMeta)
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

        $tableName = $tableMeta->getTableName($em);
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        return $this->render('services/new.html.twig', array(
            'service' => $service,
            'form' => $form->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Finds and displays a service entity.
     *
     */
    public function showAction(Services $service, TableMeta $tableMeta)
    {
        $deleteForm = $this->createDeleteForm($service);
        $em = $this->getDoctrine()->getManager();
        $tableName = $tableMeta->getTableName($em);
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();


        return $this->render('services/show.html.twig', array(
            'service' => $service,
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Displays a form to edit an existing service entity.
     *
     */
    public function editAction(Request $request, Services $service, TableMeta $tableMeta)
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

        $tableName = $tableMeta->getTableName($em);
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        return $this->render('services/edit.html.twig', array(
            'service' => $service,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
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
