<?php

namespace kiraxe\AdminCrmBundle\Controller;

use kiraxe\AdminCrmBundle\Entity\Expenses;
use kiraxe\AdminCrmBundle\Services\DumpDbs;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;


/**
 * Expense controller.
 *
 */
class ExpensesController extends Controller
{
    /**
     * Lists all expense entities.
     *
     */
    public function indexAction(Request $request)
    {
        //$dbs->openFile('my_dump');
        //print_r($dbs->getDump());

        $em = $this->getDoctrine()->getManager();

        $expenses = $em->getRepository('kiraxeAdminCrmBundle:Expenses')->findBy(array(),array('date' => 'DESC'));//findAll();

        $deleteForm = [];

        for($i = 0; $i < count($expenses); $i++) {
            $deleteForm[$expenses[$i]->getId()] = $this->createDeleteForm($expenses[$i])->createView();
        }

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
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Clientele')->getTableName()] = "Клиенты";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Expenses')->getTableName()] = "Расход";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars = [];
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";




        $sqlExpenses = "SELECT e FROM kiraxeAdminCrmBundle:Expenses e where";

        if (!empty($request->query->get('form')['dateFrom']) && empty($request->query->get('form')['dateTo'])) {
            $dateFrom = $request->query->get('form')['dateFrom'];
            $dateFrom = str_replace("-", "", $dateFrom);
            $sqlExpenses .= " date(e.date) =".$dateFrom;
        }

        if (empty($request->query->get('form')['dateFrom']) && !empty($request->query->get('form')['dateTo'])) {
            $dateTo = $request->query->get('form')['dateTo'];
            $dateTo = str_replace("-", "", $dateTo);
            $sqlExpenses .= " date(e.date) =".$dateTo;
        }

        if (!empty($request->query->get('form')['dateFrom']) && !empty($request->query->get('form')['dateTo'])) {
            $dateFrom = $request->query->get('form')['dateFrom'];
            $dateTo = $request->query->get('form')['dateTo'];
            $dateFrom = str_replace("-", "", $dateFrom);
            $dateTo = str_replace("-", "", $dateTo);
            $sqlExpenses .= " date(e.date) between " . $dateFrom . " and " . $dateTo;
        }

        if (!empty($request->query->get('form')['dateFrom'])) {
            $expenses = $em->createQuery($sqlExpenses)->getResult();
        }

        $form = $this->get("form.factory")->createNamedBuilder("form")
            ->setMethod('GET')
            ->add('dateFrom', TextType::class ,array(
                'label' => 'с',
                'required' => false,
            ))
            ->add('dateTo', TextType::class ,array(
                'label' => 'по',
                'required' => false,
            ))
            ->getForm();


        $form->handleRequest($request);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $expenses, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            50 /*limit per page*/
        );

        return $this->render('expenses/index.html.twig', array(
            'expenses' => $expenses,
            'form' => $form->createView(),
            'delete_form' => $deleteForm,
            'tables' => $tableName,
            'pagination' => $pagination,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
        ));
    }

    /**
     * Creates a new expense entity.
     *
     */
    public function newAction(Request $request)
    {
        $expense = new Expenses();
        $form = $this->createForm('kiraxe\AdminCrmBundle\Form\ExpensesType', $expense);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();

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
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Clientele')->getTableName()] = "Клиенты";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Expenses')->getTableName()] = "Расход";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars = [];
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($expense);
            $em->flush();

            return $this->redirectToRoute('expenses_show', array('id' => $expense->getId()));
        }

        return $this->render('expenses/new.html.twig', array(
            'expense' => $expense,
            'form' => $form->createView(),
            'tables' => $tableName,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
        ));
    }

    /**
     * Finds and displays a expense entity.
     *
     */
    public function showAction(Expenses $expense)
    {
        $deleteForm = $this->createDeleteForm($expense);

        $em = $this->getDoctrine()->getManager();
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
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Clientele')->getTableName()] = "Клиенты";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Expenses')->getTableName()] = "Расход";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars = [];
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";

        return $this->render('expenses/show.html.twig', array(
            'expense' => $expense,
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
        ));
    }

    /**
     * Displays a form to edit an existing expense entity.
     *
     */
    public function editAction(Request $request, Expenses $expense)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $deleteForm = $this->createDeleteForm($expense);
        $editForm = $this->createForm('kiraxe\AdminCrmBundle\Form\ExpensesType', $expense);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('expenses_edit', array('id' => $expense->getId()));
        }

        $hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();
        $tableName = [];
        $tableSettingsName = [];
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Workers')->getTableName()] = "Сотрудники";
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Services')->getTableName()] = "Услуги";
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:User')->getTableName()] = "Пользователи";
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Materials')->getTableName()] = "Материалы";
        $tableSettingsName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Measure')->getTableName()] = "Единицы измерения";
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Orders')->getTableName()] = "Заказ-наряд";
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Clientele')->getTableName()] = "Клиенты";
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Expenses')->getTableName()] = "Расход";
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars = [];
        $tableCars[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";

        return $this->render('expenses/edit.html.twig', array(
            'expense' => $expense,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
        ));
    }

    /**
     * Deletes a expense entity.
     *
     */
    public function deleteAction(Request $request, Expenses $expense)
    {
        $form = $this->createDeleteForm($expense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($expense);
            $em->flush();
        }

        return $this->redirectToRoute('expenses_index');
    }

    /**
     * Creates a form to delete a expense entity.
     *
     * @param Expenses $expense The expense entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Expenses $expense)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('expenses_delete', array('id' => $expense->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
