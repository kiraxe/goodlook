<?php

namespace kiraxe\AdminCrmBundle\Controller;

use kiraxe\AdminCrmBundle\Entity\Expenses;
use kiraxe\AdminCrmBundle\Services\DumpDbs;
use kiraxe\AdminCrmBundle\Services\TableMeta\TableMeta;
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
    public function indexAction(Request $request, TableMeta $tableMeta)
    {
        //$dbs->openFile('my_dump');
        //print_r($dbs->getDump());

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

        $expenses = $em->getRepository('kiraxeAdminCrmBundle:Expenses')->findBy(array(),array('date' => 'DESC'));//findAll();

        $deleteForm = [];

        for($i = 0; $i < count($expenses); $i++) {
            $deleteForm[$expenses[$i]->getId()] = $this->createDeleteForm($expenses[$i])->createView();
        }

        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        //$hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        //$this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();

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
        ));
    }

    /**
     * Creates a new expense entity.
     *
     */
    public function newAction(Request $request, TableMeta $tableMeta)
    {
        $expense = new Expenses();
        $form = $this->createForm('kiraxe\AdminCrmBundle\Form\ExpensesType', $expense);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

        //$hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        //$this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();


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
        ));
    }

    /**
     * Finds and displays a expense entity.
     *
     */
    public function showAction(Expenses $expense, TableMeta $tableMeta)
    {
        $deleteForm = $this->createDeleteForm($expense);

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

        $hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();


        return $this->render('expenses/show.html.twig', array(
            'expense' => $expense,
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Displays a form to edit an existing expense entity.
     *
     */
    public function editAction(Request $request, Expenses $expense, TableMeta $tableMeta)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($entityManager);
        $deleteForm = $this->createDeleteForm($expense);
        $editForm = $this->createForm('kiraxe\AdminCrmBundle\Form\ExpensesType', $expense);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('expenses_edit', array('id' => $expense->getId()));
        }

        //$hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        //$this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();


        return $this->render('expenses/edit.html.twig', array(
            'expense' => $expense,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
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
