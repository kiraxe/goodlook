<?php

namespace kiraxe\AdminCrmBundle\Controller;

use kiraxe\AdminCrmBundle\Entity\Calendar;
use kiraxe\AdminCrmBundle\Services\TableMeta\TableMeta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Calendar controller.
 *
 */
class CalendarController extends Controller
{
    /**
     * Lists all calendar entities.
     *
     */
    public function indexAction(Request $request, TableMeta $tableMeta)
    {
        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

        $calendars = $em->getRepository('kiraxeAdminCrmBundle:Calendar')->findAll();

        $cldr = null;

        foreach ($calendars as $calendar) {
            $cldr[] = [
                "id" => $calendar->getId(),
                "title" => "\\n" . $calendar->getName() . "\\n" . $calendar->getPhone() . "\\n" . $calendar->getText(),
                "start" => date('Y-m-d H:i:s', $calendar->getDate()->getTimestamp()),
                "end" => date('Y-m-d H:i:s', $calendar->getDatecl()->getTimestamp()),
            ];
        }

        $tableSettingsName = [];
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();


        if(!empty($request->request->get('kiraxe_admincrmbundle_calendar')['id'])) {
            $id = $request->request->get('kiraxe_admincrmbundle_calendar')['id'];
            $calendar = $em->getRepository(Calendar::class)->find($id);

            $date = $request->request->get('kiraxe_admincrmbundle_calendar')['date']['date'] ." " . $request->request->get('kiraxe_admincrmbundle_calendar')['date']['time'];
            $datecl = $request->request->get('kiraxe_admincrmbundle_calendar')['datecl']['date'] . " " . $request->request->get('kiraxe_admincrmbundle_calendar')['datecl']['time'];

            $date1 = new \DateTime($date);
            $datecl1 = new \DateTime($datecl);

            $calendar->setName($request->request->get('kiraxe_admincrmbundle_calendar')['name']);
            $calendar->setPhone($request->request->get('kiraxe_admincrmbundle_calendar')['phone']);
            $calendar->setText($request->request->get('kiraxe_admincrmbundle_calendar')['text']);
            $calendar->setDate($date1);
            $calendar->setDatecl($datecl1);
            $em->flush();
            return $this->redirectToRoute('calendar_index');

        } else {
            $calendar = new Calendar();
            $form = $this->createForm('kiraxe\AdminCrmBundle\Form\CalendarType', $calendar);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($calendar);
                $em->flush();

                return $this->redirectToRoute('calendar_index');
            }
        }


        return $this->render('calendar/index.html.twig', array(
            'calendars' => $cldr,
            'calendar' => $calendar,
            'form' => $form->createView(),
            'tables' => $tableName,
            'user' => $user
        ));
    }

    /**
     * Creates a new calendar entity.
     *
     */
    public function newAction(Request $request)
    {
        $calendar = new Calendar();
        $form = $this->createForm('kiraxe\AdminCrmBundle\Form\CalendarType', $calendar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($calendar);
            $em->flush();

            return $this->redirectToRoute('calendar_show', array('id' => $calendar->getId()));
        }

        return $this->render('calendar/new.html.twig', array(
            'calendar' => $calendar,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a calendar entity.
     *
     */
    public function showAction(Calendar $calendar)
    {
        $deleteForm = $this->createDeleteForm($calendar);

        return $this->render('calendar/show.html.twig', array(
            'calendar' => $calendar,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing calendar entity.
     *
     */
    public function editAction(Request $request, Calendar $calendar)
    {
        $deleteForm = $this->createDeleteForm($calendar);
        $editForm = $this->createForm('kiraxe\AdminCrmBundle\Form\CalendarType', $calendar);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('calendar_edit', array('id' => $calendar->getId()));
        }

        return $this->render('calendar/edit.html.twig', array(
            'calendar' => $calendar,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a calendar entity.
     *
     */
    public function deleteAction(Request $request, Calendar $calendar)
    {
        $form = $this->createDeleteForm($calendar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($calendar);
            $em->flush();
        }

        return $this->redirectToRoute('calendar_index');
    }

    public function ajaxdeletformAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $calendar = null;
        $deleteForm = "";

        if($request->request->get('calendarID')) {
            $id = $request->request->get('calendarID');
            $calendar = $em->getRepository(Calendar::class)->find($id);
            $deleteForm = $this->createDeleteForm($calendar);
        }

        return $this->render('calendar/ajaxdeletform.html.twig', array(
            'delete_form' => $deleteForm->createView(),
        ));

    }

    /**
     * Creates a form to delete a calendar entity.
     *
     * @param Calendar $calendar The calendar entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Calendar $calendar)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('calendar_delete', array('id' => $calendar->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
