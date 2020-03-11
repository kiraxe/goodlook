<?php

namespace kiraxe\AdminCrmBundle\Controller;

use kiraxe\AdminCrmBundle\Entity\User;
use kiraxe\AdminCrmBundle\Form\SqlType;
use kiraxe\AdminCrmBundle\Services\TableMeta\TableMeta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\Stream;
use kiraxe\AdminCrmBundle\Services\FileUploader\FileUploader;





/**
 * User controller.
 *
 */
class UserController extends Controller
{
    /**
     * Lists all user entities.
     *
     */
    public function indexAction(Request $request, KernelInterface $kernel, FileUploader $fileUploader, TableMeta $tableMeta)
    {
        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

        $users = $em->getRepository('kiraxeAdminCrmBundle:User')->findAll();

        $deleteForm = [];

        $form = $this->createForm(SqlType::class);
        $form->handleRequest($request);

        $publicResourcesFolderPath = $this->getParameter('kernel.project_dir');

        if ($form->isSubmitted() && $form->isValid()) {

            $brochureFile = $form->get('brochure')->getData();
            if ($brochureFile) {
                $brochureFileName = $fileUploader->upload($brochureFile);

                $application = new Application($kernel);
                $application->setAutoExit(false);

                $input = new ArrayInput(array(
                    'command' => 'mysql:addDump',
                    'filename' => $brochureFileName
                ));

                $output = new BufferedOutput();
                $application->run($input, $output);
                $content = $output->fetch();
            }
        }



        for($i = 0; $i < count($users); $i++) {
            $deleteForm[$users[$i]->getUsername()] = $this->createDeleteForm($users[$i])->createView();
        }

        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $hasAccess = $this->isGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, "Вам доступ запрещен");
        $user = $this->getUser();


        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $users, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            50 /*limit per page*/
        );

        return $this->render('user/index.html.twig', array(
            'form' => $form->createView(),
            'users' => $users,
            'delete_form' => $deleteForm,
            'tables' => $tableName,
            'user' => $user,
            'pagination' => $pagination,
        ));
    }

    /**
     * Creates a new user entity.
     *
     */
    public function newAction(Request $request, TableMeta $tableMeta)
    {
        $passwordEncoder = $this->get('security.password_encoder');
        $user = new User();
        $form = $this->createForm('kiraxe\AdminCrmBundle\Form\UserType', $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('user_show', array('id' => $user->getId()));
        }

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();


        return $this->render('user/new.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Finds and displays a user entity.
     *
     */
    public function showAction(User $user, TableMeta $tableMeta)
    {
        $deleteForm = $this->createDeleteForm($user);

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();


        return $this->render('user/show.html.twig', array(
            'user' => $user,
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Displays a form to edit an existing user entity.
     *
     */
    public function editAction(Request $request, User $user, TableMeta $tableMeta)
    {
        $passwordEncoder = $this->get('security.password_encoder');
        $deleteForm = $this->createDeleteForm($user);
        $editForm = $this->createForm('kiraxe\AdminCrmBundle\Form\UserType', $user);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_edit', array('id' => $user->getId()));
        }

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        return $this->render('user/edit.html.twig', array(
            'user' => $user,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Deletes a user entity.
     *
     */
    public function deleteAction(Request $request, User $user)
    {
        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    /**
     * Creates a form to delete a user entity.
     *
     * @param User $user The user entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(User $user)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('user_delete', array('id' => $user->getId())))
            ->setMethod('DELETE')
            ->getForm();

    }

    public function getdumpAction(Request $request, KernelInterface $kernel) {

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
            'command' => 'mysql:dump',
        ));

        $output = new BufferedOutput();
        $application->run($input, $output);
        $content = $output->fetch();

        $filename = date('Y-m-d_H-i-s');

        //$publicResourcesFolderPath = $this->getParameter('kernel.project_dir') . '\web\public\crontab\\';

        $response = new Response($content);

        /*$stream  = new Stream($publicResourcesFolderPath.$content);
           $response = new BinaryFileResponse($publicResourcesFolderPath.$content);

        $response->headers->set('Content-Type', 'text/plain');
        $response->setContentDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $content
        );*/

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename.'.sql'
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
