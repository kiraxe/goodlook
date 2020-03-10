<?php

namespace kiraxe\AdminCrmBundle\Controller;

use kiraxe\AdminCrmBundle\Entity\Brand;
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
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();


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
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";
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
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
            'delete_form' => $deleteForm
        ));
    }

    /**
     * Creates a new brand entity.
     *
     */
    public function newAction(Request $request)
    {
        $brand = new Brand();
        $form = $this->createForm('kiraxe\AdminCrmBundle\Form\BrandType', $brand);
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
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";
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
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars
        ));
    }

    /**
     * Finds and displays a brand entity.
     *
     */
    public function showAction(Brand $brand)
    {

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
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";

        $deleteForm = $this->createDeleteForm($brand);

        return $this->render('brand/show.html.twig', array(
            'brand' => $brand,
            'tables' => $tableName,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing brand entity.
     *
     */
    public function editAction(Request $request, Brand $brand)
    {
        $deleteForm = $this->createDeleteForm($brand);
        $editForm = $this->createForm('kiraxe\AdminCrmBundle\Form\BrandType', $brand);
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
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('brand_edit', array('id' => $brand->getId()));
        }

        return $this->render('brand/edit.html.twig', array(
            'brand' => $brand,
            'tables' => $tableName,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
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
