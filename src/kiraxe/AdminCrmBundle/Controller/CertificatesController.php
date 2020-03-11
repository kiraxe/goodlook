<?php

namespace kiraxe\AdminCrmBundle\Controller;

use kiraxe\AdminCrmBundle\Entity\Certificates;
use kiraxe\AdminCrmBundle\Services\TableMeta\TableMeta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;


/**
 * Certificates controller.
 *
 */
class CertificatesController extends Controller
{
    /**
     * Lists all certificates entities.
     *
     */
    public function indexAction(Request $request, TableMeta $tableMeta)
    {
        //$dbs->openFile('my_dump');
        //print_r($dbs->getDump());

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

        $certificates = $em->getRepository('kiraxeAdminCrmBundle:Certificates')->findBy(array(),array('date' => 'DESC'));//findAll();

        $deleteForm = [];

        for($i = 0; $i < count($certificates); $i++) {
            $deleteForm[$certificates[$i]->getId()] = $this->createDeleteForm($certificates[$i])->createView();
        }

        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();

        $sqlCertificates = "SELECT e FROM kiraxeAdminCrmBundle:Certificates e where";

        if (!empty($request->query->get('form')['dateFrom']) && empty($request->query->get('form')['dateTo'])) {
            $dateFrom = $request->query->get('form')['dateFrom'];
            $dateFrom = str_replace("-", "", $dateFrom);
            $sqlCertificates .= " date(e.date) =".$dateFrom;
        }

        if (empty($request->query->get('form')['dateFrom']) && !empty($request->query->get('form')['dateTo'])) {
            $dateTo = $request->query->get('form')['dateTo'];
            $dateTo = str_replace("-", "", $dateTo);
            $sqlCertificates .= " date(e.date) =".$dateTo;
        }

        if (!empty($request->query->get('form')['dateFrom']) && !empty($request->query->get('form')['dateTo'])) {
            $dateFrom = $request->query->get('form')['dateFrom'];
            $dateTo = $request->query->get('form')['dateTo'];
            $dateFrom = str_replace("-", "", $dateFrom);
            $dateTo = str_replace("-", "", $dateTo);
            $sqlCertificates .= " date(e.date) between " . $dateFrom . " and " . $dateTo;
        }

        if (!empty($request->query->get('form')['dateFrom'])) {
            $certificates = $em->createQuery($sqlCertificates)->getResult();
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
            $certificates, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            50 /*limit per page*/
        );

        return $this->render('certificates/index.html.twig', array(
            'certificates' => $certificates,
            'form' => $form->createView(),
            'delete_form' => $deleteForm,
            'tables' => $tableName,
            'pagination' => $pagination,
            'user' => $user,
        ));
    }

    /**
     * Creates a new certificates entity.
     *
     */
    public function newAction(Request $request, TableMeta $tableMeta)
    {
        $certificates = new Certificates();
        $form = $this->createForm('kiraxe\AdminCrmBundle\Form\CertificatesType', $certificates);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

        $hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();


        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($certificates);
            $em->flush();

            return $this->redirectToRoute('сertificates_show', array('id' => $certificates->getId()));
        }

        return $this->render('certificates/new.html.twig', array(
            'certificates' => $certificates,
            'form' => $form->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Finds and displays a certificates entity.
     *
     */
    public function showAction(Certificates $certificates, TableMeta $tableMeta)
    {
        $deleteForm = $this->createDeleteForm($certificates);

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

        $hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();


        return $this->render('certificates/show.html.twig', array(
            'certificates' => $certificates,
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Displays a form to edit an existing certificates entity.
     *
     */
    public function editAction(Request $request, Certificates $certificates, TableMeta $tableMeta)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($entityManager);
        $deleteForm = $this->createDeleteForm($certificates);
        $editForm = $this->createForm('kiraxe\AdminCrmBundle\Form\CertificatesType', $certificates);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('сertificates_edit', array('id' => $certificates->getId()));
        }

        $hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();


        return $this->render('certificates/edit.html.twig', array(
            'certificates' => $certificates,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Deletes a certificates entity.
     *
     */
    public function deleteAction(Request $request, Certificates $certificates)
    {
        $form = $this->createDeleteForm($certificates);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($certificates);
            $em->flush();
        }

        return $this->redirectToRoute('сertificates_index');
    }

    /**
     * Creates a form to delete a certificates entity.
     *
     * @param Certificates $certificates The certificates entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Certificates $certificates)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('сertificates_delete', array('id' => $certificates->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}
