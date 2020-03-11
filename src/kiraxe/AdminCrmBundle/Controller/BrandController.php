<?php

namespace kiraxe\AdminCrmBundle\Controller;

use kiraxe\AdminCrmBundle\Entity\Brand;
use kiraxe\AdminCrmBundle\Services\TableMeta\TableMeta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
//use kiraxe\AdminCrmBundle\Services\DbDump\Dump\MysqlDb;
//use kiraxe\AdminCrmBundle\Services\DbDump\Dump\MysqlDump;
//use kiraxe\AdminCrmBundle\Services\DbDump\FileWriter\WriterSql;
//use kiraxe\AdminCrmBundle\Services\DbDump\FileWriter\WriterSqlPart;


/**
 * Brand controller.
 *
 */
class BrandController extends Controller
{
    /**
     * Lists all brand entities.
     *
     */
    public function indexAction(Request $request, TableMeta $tableMeta)
    {
        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

        //$impDump = new MysqlDump();
        //$absDump = new MysqlDb($impDump);

        //$impWr = new WriterSqlPart();
        //$absWr = new WriterSql($impWr, $absDump);

        //$absWr->openFile('Mydump');

        //$file = $absWr->getFile();

        //echo $file;



        //$basePath = $this->getParameter('kernel.project_dir');
        //$output = shell_exec('bash '.$basePath.'\dump.sh');
        //echo $output;


        $brands = $em->getRepository('kiraxeAdminCrmBundle:Brand')->findBy(['active' => '1']);

        $user = $this->getUser();

        for($i = 0; $i < count($brands); $i++) {
            $deleteForm[$brands[$i]->getName()] = $this->createDeleteForm($brands[$i])->createView();
        }

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $brands, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            50 /*limit per page*/
        );

        return $this->render('brand/index.html.twig', array(
            'brands' => $brands,
            'tables' => $tableName,
            'user' => $user,
            'pagination' => $pagination,
            'delete_form' => $deleteForm
        ));
    }

    /**
     * Creates a new brand entity.
     *
     */
    public function newAction(Request $request, TableMeta $tableMeta)
    {
        $brand = new Brand();
        $form = $this->createForm('kiraxe\AdminCrmBundle\Form\BrandType', $brand);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $brand->setActive(true);
            $em->persist($brand);
            $em->flush();

            return $this->redirectToRoute('brand_show', array('id' => $brand->getId()));
        }

        return $this->render('brand/new.html.twig', array(
            'brand' => $brand,
            'form' => $form->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Finds and displays a brand entity.
     *
     */
    public function showAction(Brand $brand, TableMeta $tableMeta)
    {

        $em = $this->getDoctrine()->getManager();
        $tableName = $tableMeta->getTableName($em);
        $user = $this->getUser();


        $deleteForm = $this->createDeleteForm($brand);

        return $this->render('brand/show.html.twig', array(
            'brand' => $brand,
            'tables' => $tableName,
            'user' => $user,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing brand entity.
     *
     */
    public function editAction(Request $request, Brand $brand, TableMeta $tableMeta)
    {
        $deleteForm = $this->createDeleteForm($brand);
        $editForm = $this->createForm('kiraxe\AdminCrmBundle\Form\BrandType', $brand);
        $editForm->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $tableName = $tableMeta->getTableName($em);
        $user = $this->getUser();

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('brand_edit', array('id' => $brand->getId()));
        }

        return $this->render('brand/edit.html.twig', array(
            'brand' => $brand,
            'tables' => $tableName,
            'user' => $user,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a brand entity.
     *
     */
    public function deleteAction(Request $request, Brand $brand)
    {
        $form = $this->createDeleteForm($brand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $brand->setActive(false);
            //$em->remove($brand);
            $em->flush();
        }

        return $this->redirectToRoute('brand_index');
    }

    /**
     * Creates a form to delete a brand entity.
     *
     * @param Brand $brand The brand entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Brand $brand)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('brand_delete', array('id' => $brand->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
