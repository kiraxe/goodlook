<?php

namespace kiraxe\AdminCrmBundle\Controller;

//use http\Env\Response;
use kiraxe\AdminCrmBundle\Entity\Materials;
use kiraxe\AdminCrmBundle\Entity\Orders;
use kiraxe\AdminCrmBundle\Services\TableMeta\TableMeta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Material controller.
 *
 */
class MaterialsController extends Controller
{
    /**
     * Lists all material entities.
     *
     */
    public function indexAction(Request $request, TableMeta $tableMeta)
    {
        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

        $materials = $em->getRepository('kiraxeAdminCrmBundle:Materials')->findBy(array('active' => '1'), array('name' => 'ASC'));

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();


        $arithmeticMean = [];
        $residue = 0;

        if (count($materials) > 0) {

            for($i = 0; $i < count($materials); $i++) {
                $deleteForm[$materials[$i]->getName()] = $this->createDeleteForm($materials[$i])->createView();
                $arithmeticMean[$i] = $materials[$i]->getQuantitypack() / 2;

                $workerorders = $em->createQuery(
                    'SELECT w FROM kiraxeAdminCrmBundle:WorkerOrders w where w.materials ='. $materials[$i]->getId()
                )->getResult();


                if (count($workerorders) == 0) {
                    $materials[$i]->setResidue($materials[$i]->getTotalsize());
                    $em->persist($materials[$i]);
                    $em->flush();
                } elseif (count($workerorders) > 0) {
                    foreach ($workerorders as $workerorder) {
                        $residue += $workerorder->getAmountOfMaterial() + $workerorder->getMarriage();
                        //echo "|(id)". $workerorder->getID() . "|(название)" . $materials[$i]->getName() . "|(Общее название)" . $materials[$i]->getTotalsize() . "|('потрачено')" . $workerorder->getAmountOfMaterial() . "|" . $workerorder->getMarriage() . '<br/>';
                    }

                    $materials[$i]->setResidue($materials[$i]->getTotalsize() - $residue);
                    $em->persist($materials[$i]);
                    $em->flush();
                    $residue = 0;
                }
            }

        } else {
            $deleteForm = null;
        }

        /*foreach ($materials as $material) {
            echo $material->getResidue() . "<br/>";
        }*/

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $materials, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            50 /*limit per page*/
        );

        return $this->render('materials/index.html.twig', array(
            'materials' => $materials,
            'arithmeticMea' => $arithmeticMean,
            'tables' => $tableName,
            'user' => $user,
            'pagination' => $pagination,
            'delete_form' => $deleteForm,
        ));
    }

    /**
     * Creates a new material entity.
     *
     */
    public function newAction(Request $request, TableMeta $tableMeta)
    {
        $material = new Materials();
        $form = $this->createForm('kiraxe\AdminCrmBundle\Form\MaterialsType', $material);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {

            $material->setResidue($material->getTotalsize());
            $pricepackage = round($material->getPricepackage(), 1);
            $quantitypack = round($material->getQuantitypack(), 1);
            $priceUnit = round($pricepackage / $quantitypack, 1);
            $material->setPriceUnit($priceUnit);
            $price = $priceUnit * $material->getTotalsize();
            $material->setPrice(round($price, 1));

            $material->setActive(true);

            $em->persist($material);

            $em->flush();

            return $this->redirectToRoute('materials_show', array('id' => $material->getId()));
        }

        return $this->render('materials/new.html.twig', array(
            'material' => $material,
            'form' => $form->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Finds and displays a material entity.
     *
     */
    public function showAction(Materials $material, TableMeta $tableMeta)
    {
        $deleteForm = $this->createDeleteForm($material);

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        return $this->render('materials/show.html.twig', array(
            'material' => $material,
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Displays a form to edit an existing material entity.
     *
     */
    public function editAction($id,Request $request, Materials $material, TableMeta $tableMeta)
    {
        $deleteForm = $this->createDeleteForm($material);
        $editForm = $this->createForm('kiraxe\AdminCrmBundle\Form\MaterialsType', $material);

        $editForm->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);
        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        //$user = $this->getUser();
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();
        $residue = 0;



        $workerorders = $em->createQuery(
            'SELECT w FROM kiraxeAdminCrmBundle:WorkerOrders w where w.materials ='. $id
        )->getResult();


        if (count($workerorders) == 0) {
            $material->setResidue($material->getTotalsize());
            $em->persist($material);
            $em->flush();
        } elseif (count($workerorders) > 0) {
            foreach ($workerorders as $workerorder) {
                $residue += $workerorder->getAmountOfMaterial() + $workerorder->getMarriage();
            }
            $material->setResidue($material->getTotalsize() - $residue);
            $em->persist($material);
            $em->flush();
        }

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $pricepackage = $material->getPricepackage();
            $quantitypack = $material->getQuantitypack();
            $priceUnit = $pricepackage / $quantitypack;
            $material->setPriceUnit($priceUnit);
            $price = $priceUnit * $material->getTotalsize();
            $material->setPrice($price);
            $em->flush();

            return $this->redirectToRoute('materials_edit', array('id' => $material->getId()));
        }

        return $this->render('materials/edit.html.twig', array(
            'material' => $material,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Deletes a material entity.
     *
     */
    public function deleteAction(Request $request, Materials $material)
    {
        $form = $this->createDeleteForm($material);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $material->setActive(false);
            //$em->remove($material);
            $em->flush();
        }

        return $this->redirectToRoute('materials_index');
    }

    /**
     * Creates a form to delete a material entity.
     *
     * @param Materials $material The material entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Materials $material)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('materials_delete', array('id' => $material->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
