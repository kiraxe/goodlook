<?php

namespace kiraxe\AdminCrmBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use kiraxe\AdminCrmBundle\Entity\ManagerOrders;
use kiraxe\AdminCrmBundle\Entity\Materials;
use kiraxe\AdminCrmBundle\Entity\Orders;
use kiraxe\AdminCrmBundle\Entity\Workers;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\PhpWord;
use Symfony\Component\Form\FormEvents;


/**
 * Order controller.
 *
 */
class OrdersController extends Controller
{
    /**
     * Lists all order entities.
     *
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $deleteForm = null;

        $sql = "SELECT o FROM kiraxeAdminCrmBundle:Orders o where";

        if (!empty($request->query->get('form')['dateFrom']) && empty($request->query->get('form')['dateTo'])) {
            $dateFrom = $request->query->get('form')['dateFrom'];
            $dateFrom = str_replace("-", "", $dateFrom);
            $sql .= " date(o.dateOpen) =".$dateFrom;
        }

        if (!empty($request->query->get('form')['dateFrom']) && !empty($request->query->get('form')['dateTo'])) {
            $dateFrom = $request->query->get('form')['dateFrom'];
            $dateTo = $request->query->get('form')['dateTo'];
            $dateFrom = str_replace("-", "", $dateFrom);
            $dateTo = str_replace("-", "", $dateTo);
            $sql .= " date(o.dateOpen) between " . $dateFrom . " and " . $dateTo;
        }

        if (!empty($request->query->get('form')['tel'])) {
            $phone = $request->query->get('form')['tel'];

            if ($sql == "SELECT o FROM kiraxeAdminCrmBundle:Orders o where") {
                $sql .= ' o.phone =' . "'" . $phone . "'";
            } else {
                $sql .= ' and o.phone =' . "'" . $phone . "'";
            }

        }


        if (!empty($request->query->get('form')['manager'])) {
            $manager = $request->query->get('form')['manager'];

            if ($sql == "SELECT o FROM kiraxeAdminCrmBundle:Orders o where") {
                $sql .= ' (o.workeropen =' . $manager . ' or o.workerclose =' . $manager . ")";
            } else {
                $sql .= ' and (o.workeropen =' . $manager . " or o.workerclose =" .$manager . ")";
            }
        }

        if (!empty($request->query->get('form')['number'])) {
            $number = $request->query->get('form')['number'];
            if ($sql == "SELECT o FROM kiraxeAdminCrmBundle:Orders o where") {
                $sql .= ' o.number =' . "'" . $number . "'";
            } else {
                $sql .= ' and o.number =' . "'" . $number . "'";
            }
        }


        if (!empty($request->query->get('form')['close'])) {

            $close = $request->query->get('form')['close'];

            if ($close == 2) {
                $close = 0;
            }

            if ($sql == "SELECT o FROM kiraxeAdminCrmBundle:Orders o where") {
                $sql .= ' o.close =' . $close;
            } else {
                $sql .= ' and o.close =' . $close;
            }
        }

        print_r($sql);

        if (empty($request->query->get('form')['dateFrom']) && empty($request->query->get('form')['tel']) && empty($request->query->get('form')['manager']) && empty($request->query->get('form')['number']) && empty($request->query->get('form')['close'])) {
            $orders = $em->getRepository('kiraxeAdminCrmBundle:Orders')->findBy(array(), array('dateOpen' => 'DESC'));
        } else {
            $orders = $em->createQuery($sql."ORDER BY o.dateOpen DESC")->getResult();
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
            ->add('tel', TelType::class ,array(
                'label' => 'Сортировка по телефону',
                'empty_data' => null,
                'required' => false,
            ))
            ->add('number', TextType::class ,array(
                'label' => 'Сортировка по гос.номеру',
                'empty_data' => null,
                'required' => false,
            ))
            ->add('manager', EntityType::class , array(
                'class' => 'kiraxe\AdminCrmBundle\Entity\Workers',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $workeropen) {
                    return $workeropen->createQueryBuilder('w')->where("w.typeworkers = 1");
                },
                'label' => 'Сортировка по менеджеру',
                'required' => false,
                'placeholder' => 'Выберите менеджера',
                'empty_data' => null,
            ))
            ->add('close', ChoiceType::class, [
                'choices'  => [
                    'Открыт' => 2,
                    'Закрыт' => 1,
                ],
                'label' => 'Cтатус заказа',
                'placeholder' => 'Выберите статус заказа',
                'empty_data' => null,
                'required' => false,
            ])
            ->getForm();


        $form->handleRequest($request);

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
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars = [];
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";

        for($i = 0; $i < count($orders); $i++) {
            $deleteForm[$orders[$i]->getId()] = $this->createDeleteForm($orders[$i])->createView();
        }

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $orders, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('orders/index.html.twig', array(
            'form' => $form->createView(),
            'orders' => $orders,
            'delete_form' => $deleteForm,
            'tables' => $tableName,
            'pagination' => $pagination,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
        ));
    }

    /**
     * Creates a new order entity.
     *
     */
    public function newAction(Request $request)
    {
        $order = new Orders();
        $managerorder = new ManagerOrders();
        $managerordersecond = new ManagerOrders();
        $form = $this->createForm('kiraxe\AdminCrmBundle\Form\OrdersType', $order);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        $serviceparent = null;
        $price = null;
        $unitprice = null;

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
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars = [];
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";
        if ($form->isValid()) {

            foreach ($order->getWorkerorders() as $workerorders) {
                $price += $workerorders->getPrice();
                $serviceparent = $workerorders->getServicesparent()->getId();
                if ($workerorders->getMaterials()) {
                    $workerorders->setPriceUnit($workerorders->getMaterials()->getPriceUnit());
                }
                foreach ($workerorders->getWorkers()->getWorkerService() as $k => $val) {
                    if ($val->getServices()->getId() == $serviceparent) {
                        if ($workerorders->getMaterials()) {
                            $unitprice = $workerorders->getMaterials()->getPriceUnit() * $workerorders->getMarriage();
                        }
                        if ($unitprice == 0) {
                            $workerorders->setSalary(($workerorders->getPrice() / 100) * $val->getPercent());
                        } else {
                            $workerorders->setSalary( (($workerorders->getPrice() / 100) * $val->getPercent()) - $unitprice);
                        }

                        if ($workerorders->getFine() > 0) {

                            $salary = $workerorders->getSalary();
                            $workerorders->setSalary($salary - $workerorders->getFine());
                        }
                    }
                }
            }

            $order->setPrice($price);
            $priceOrder = $order->getPrice();

            if ($order->getWorkeropen() && $order->getWorkerclose()) {

                if ($order->getWorkeropen()->getId() == $order->getWorkerclose()->getId()) {
                    $managerorder->setWorkers($order->getWorkeropen());
                    $order->addManagerorders($managerorder);

                    foreach ($order->getWorkeropen()->getManagerPercent() as $percent) {
                        $openpercent = $percent->getOpenpercent();
                    }

                    foreach ($order->getWorkerclose()->getManagerPercent() as $percent) {
                        $closepercent = $percent->getClosepercent();
                    }

                    $priceOpen = ($priceOrder / 100) * $openpercent;
                    $priceClose = ($priceOrder / 100) * $closepercent;


                    foreach ($order->getManagerorders() as $ord) {
                        $ord->setOpenprice($priceOpen);
                        $ord->setCloseprice($priceClose);
                    }

                } else {

                    $managerorder->setWorkers($order->getWorkeropen());
                    $managerordersecond->setWorkers($order->getWorkerclose());

                    $order->addManagerorders($managerorder);
                    $order->addManagerorders($managerordersecond);

                    foreach ($order->getWorkeropen()->getManagerPercent() as $percent) {
                        $openpercent = $percent->getOpenpercent();
                    }

                    foreach ($order->getWorkerclose()->getManagerPercent() as $percent) {
                        $closepercent = $percent->getClosepercent();
                    }

                    $priceOpen = ($priceOrder / 100) * $openpercent;
                    $priceClose = ($priceOrder / 100) * $closepercent;

                    foreach ($order->getManagerorders() as $ord) {

                        if ($order->getWorkeropen()->getId() == $ord->getWorkers()->getId()) {
                            $ord->setOpenprice($priceOpen);
                        }

                        if ($order->getWorkerclose()->getId() == $ord->getWorkers()->getId()) {
                            $ord->setCloseprice($priceClose);
                        }
                    }
                }
            } elseif($order->getWorkeropen()) {
                $managerorder->setWorkers($order->getWorkeropen());
                $order->addManagerorders($managerorder);

                foreach ($order->getWorkeropen()->getManagerPercent() as $percent) {
                    $openpercent = $percent->getOpenpercent();
                    $priceOpen = ($priceOrder / 100) * $openpercent;

                    foreach ($order->getManagerorders() as $ord) {

                        if ($order->getWorkeropen()->getId() == $ord->getWorkers()->getId()) {
                            $ord->setOpenprice($priceOpen);
                        }

                    }
                }
            } elseif($order->getWorkerclose()) {
                $managerordersecond->setWorkers($order->getWorkerclose());
                $order->addManagerorders($managerordersecond);

                foreach ($order->getWorkerclose()->getManagerPercent() as $percent) {
                    $closepercent = $percent->getClosepercent();
                }

                $priceClose = ($priceOrder / 100) * $closepercent;

                foreach ($order->getManagerorders() as $ord) {

                    if ($order->getWorkerclose()->getId() == $ord->getWorkers()->getId()) {
                        $ord->setCloseprice($priceClose);
                    }
                }
            }

            $em->persist($order);
            $em->flush();

            return $this->redirectToRoute('orders_show', array('id' => $order->getId()));
        }

        return $this->render('orders/new.html.twig', array(
            'order' => $order,
            'form' => $form->createView(),
            'tables' => $tableName,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
        ));
    }

    /**
     * Finds and displays a order entity.
     *
     */
    public function showAction(Orders $orders)
    {
        $deleteForm = $this->createDeleteForm($orders);

        $em = $this->getDoctrine()->getManager();

        $originalTags = new ArrayCollection();

        // Create an ArrayCollection of the current Tag objects in the database
        foreach ($orders->getWorkerorders() as $workerorders) {
            $originalTags->add($workerorders);
        }


        $step = 0;


        foreach ($orders->getWorkerorders() as $order) {

            $workerId[$step] = $order->getWorkers()->getId();
            $worker[$step] = array(
                "id" => $order->getWorkers()->getId(),
                "name" => $order->getWorkers()->getName(),
                "salary" => $order->getSalary()
            );
            $step++;

        }


        foreach ($orders->getManagerorders() as $order) {
            if ($order->getWorkers()) {
                $workerId[$step] = $order->getWorkers()->getId();
                $worker[$step] = array(
                    "id" => $order->getWorkers()->getId(),
                    "name" => $order->getWorkers()->getName(),
                    "salary" => $order->getOpenprice() + $order->getCloseprice()
                );
                $step++;
            }
        }


        $workerId = array_unique($workerId);
        $workerCart = [];

        $step = 0;
        foreach ($workerId as $w_id) {
            $workerCart[$step] = array(
                'id' => $w_id,
                'name' => null,
                'salary' => null,
            );
            foreach ($worker as $cart) {
                if ($w_id == $cart['id']) {
                    $workerCart[$step]['name'] = $cart['name'];
                    $workerCart[$step]['salary'] += round($cart['salary'], 1);
                }
            }
            $step++;
        }

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
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars = [];
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";
        return $this->render('orders/show.html.twig', array(
            'order' => $orders,
            'workerCart' => $workerCart,
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'workerorders' => $originalTags,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
        ));
    }

    /**
     * Displays a form to edit an existing order entity.
     *
     */
    public function editAction($id, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $orders = $entityManager->getRepository(Orders::class)->find($id);

        $step = 0;
        
        
       foreach ($orders->getWorkerorders() as $order) {
            
            $workerId[$step] = $order->getWorkers()->getId();
            $worker[$step] = array(
                "id" => $order->getWorkers()->getId(),
                "name" => $order->getWorkers()->getName(),
                "salary" => $order->getSalary()
            );
            $step++;
            
        }
        
        
        foreach ($orders->getManagerorders() as $order) {
            if ($order->getWorkers()) {
                $workerId[$step] = $order->getWorkers()->getId();
                $worker[$step] = array(
                    "id" => $order->getWorkers()->getId(),
                    "name" => $order->getWorkers()->getName(),
                    "salary" => $order->getOpenprice() + $order->getCloseprice()
                );
                $step++;
            }
        }
        

        $workerId = array_unique($workerId);
        $workerCart = [];

        $step = 0;
        foreach ($workerId as $w_id) {
            $workerCart[$step] = array(
                'id' => $w_id,
                'name' => null,
                'salary' => null,
            );
            foreach ($worker as $cart) {
                if ($w_id == $cart['id']) {
                    $workerCart[$step]['name'] = $cart['name'];
                    $workerCart[$step]['salary'] += round($cart['salary'], 1);
                }
            }
            $step++;
        }

        /*$basePath = $this->getParameter('kernel.project_dir');
        $templateProcessor = new TemplateProcessor($basePath.'\web\public\file\test.docx');
        $templateProcessor->setValue('name', 'Artem');
        $templateProcessor->setValue('table', table());
        $templateProcessor->setValue('phone', '+79037188521');
        $templateProcessor->saveAs($basePath.'\web\public\file\test1.docx');*/


        if (!$orders) {
            throw $this->createNotFoundException('No orders found for id '.$id);
        }

        $originalTags = new ArrayCollection();

        // Create an ArrayCollection of the current Tag objects in the database
        foreach ($orders->getWorkerorders() as $workerorders) {
            $originalTags->add($workerorders);
        }

        $editForm = $this->createForm('kiraxe\AdminCrmBundle\Form\OrdersType', $orders);
        $deleteForm = $this->createDeleteForm($orders);

        $serviceparent = null;
        $price = null;
        $unitprice = null;

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            // remove the relationship between the tag and the Task

            foreach ($orders->getWorkerorders() as $workerorders) {
                $price += $workerorders->getPrice();
                $serviceparent = $workerorders->getServicesparent()->getId();
                if ($workerorders->getMaterials()) {
                    $workerorders->setPriceUnit($workerorders->getMaterials()->getPriceUnit());
                }
                foreach ($workerorders->getWorkers()->getWorkerService() as $k => $val) {
                    if ($val->getServices()->getId() == $serviceparent) {
                        if ($workerorders->getMaterials()) {
                            $unitprice = $workerorders->getMaterials()->getPriceUnit() * $workerorders->getMarriage();
                        }
                        if ($unitprice == 0) {
                            $workerorders->setSalary(($workerorders->getPrice() / 100) * $val->getPercent());
                        } else {
                            $workerorders->setSalary((($workerorders->getPrice() / 100) * $val->getPercent()) - $unitprice);
                        }

                        if ($workerorders->getFine() > 0) {
                            $salary = $workerorders->getSalary();
                            $workerorders->setSalary($salary - $workerorders->getFine());
                        }
                    }
                }
            }

            foreach ($originalTags as $tag) {
                if (false === $orders->getWorkerorders()->contains($tag)) {
                    // remove the Task from the Tag

                    //$tag->getOrders()->removeElement($tag);

                    // if it was a many-to-one relationship, remove the relationship like this
                    //$tag->setWorkers(null);

                    $entityManager->persist($tag);

                    // if you wanted to delete the Tag entirely, you can also do that
                    $entityManager->remove($tag);
                }
            }

            $orders->setPrice($price);
            $priceOrder = $orders->getPrice();

            if ($orders->getWorkeropen()) {
                foreach ($orders->getWorkeropen()->getManagerPercent() as $percent) {
                    $openpercent = $percent->getOpenpercent();
                }
            }

            if ($orders->getWorkerclose()) {
                foreach ($orders->getWorkerclose()->getManagerPercent() as $percent) {
                    $closepercent = $percent->getClosepercent();
                }
            }

            if (isset($openpercent)) {
                $priceOpen = ($priceOrder / 100) * $openpercent;
            }

            if (isset($closepercent)) {
                $priceClose = ($priceOrder / 100) * $closepercent;
            }

            if ($orders->getWorkeropen() && $orders->getWorkerclose()) {

                if ($orders->getWorkeropen()->getId() == $orders->getWorkerclose()->getId()) {
                    foreach ($orders->getManagerorders() as $managerorder) {
                        if ($managerorder->getWorkers()->getId() != $orders->getWorkeropen()->getId()) {
                            $entityManager->persist($managerorder);
                            $entityManager->remove($managerorder);
                            $entityManager->flush();
                        }
                    }
                }
            } elseif ($orders->getWorkeropen() && !$orders->getWorkerclose()) {
                foreach ($orders->getManagerorders() as $managerorder) {
                    if ($managerorder->getWorkers()->getId() != $orders->getWorkeropen()->getId()) {
                        $entityManager->persist($managerorder);
                        $entityManager->remove($managerorder);
                        $entityManager->flush();
                    }
                }
            } elseif (!$orders->getWorkeropen() && $orders->getWorkerclose()) {
                foreach ($orders->getManagerorders() as $managerorder) {
                    if ($managerorder->getWorkers()->getId() != $orders->getWorkerclose()->getId()) {
                        $entityManager->persist($managerorder);
                        $entityManager->remove($managerorder);
                        $entityManager->flush();
                    }
                }
            } elseif (!$orders->getWorkeropen() && !$orders->getWorkerclose()) {
                foreach ($orders->getManagerorders() as $managerorder) {
                    $entityManager->persist($managerorder);
                    $entityManager->remove($managerorder);
                    $entityManager->flush();
                }
            }


            if ($orders->getWorkeropen() && $orders->getWorkerclose()) {
                $workers = ["open" => $orders->getWorkeropen(), "close" => $orders->getWorkerclose()];
            } elseif($orders->getWorkeropen() && !$orders->getWorkerclose()) {
                $workers = ["open" => $orders->getWorkeropen()];
            } elseif (!$orders->getWorkeropen() && $orders->getWorkerclose()) {
                $workers = ["close" => $orders->getWorkerclose()];
            }



            if ($orders->getWorkeropen() && $orders->getWorkerclose()) {

                if ((count($orders->getManagerorders()) == 1) && ($orders->getWorkeropen()->getId() != $orders->getWorkerclose()->getId())) {
                    $managerorder = new ManagerOrders();
                    foreach ($workers as $key => $worker) {
                        foreach ($orders->getManagerorders() as $ord) {
                            if ($worker->getId() != $ord->getWorkers()->getId()) {
                                $managerorder->setWorkers($worker);
                                if ($key == "open") {
                                    $managerorder->setOpenprice($priceOpen);
                                } elseif ($key == "close") {
                                    $managerorder->setCloseprice($priceClose);
                                }
                            }
                        }
                    }
                    $orders->addManagerorders($managerorder);
                } elseif ((count($orders->getManagerorders()) == 0) && ($orders->getWorkeropen()->getId() != $orders->getWorkerclose()->getId())) {
                    $managerorder = new ManagerOrders();
                    $managerordersecond = new ManagerOrders();

                    $managerorder->setWorkers($orders->getWorkeropen());
                    $managerordersecond->setWorkers($orders->getWorkerclose());

                    $orders->addManagerorders($managerorder);
                    $orders->addManagerorders($managerordersecond);

                    foreach ($orders->getManagerorders() as $ord) {

                        if ($orders->getWorkeropen()->getId() == $ord->getWorkers()->getId()) {
                            $ord->setOpenprice($priceOpen);
                        }

                        if ($orders->getWorkerclose()->getId() == $ord->getWorkers()->getId()) {
                            $ord->setCloseprice($priceClose);
                        }
                    }
                } elseif ((count($orders->getManagerorders()) == 0) && ($orders->getWorkeropen()->getId() == $orders->getWorkerclose()->getId())) {

                    $managerorder = new ManagerOrders();
                    $managerorder->setWorkers($orders->getWorkeropen());
                    $orders->addManagerorders($managerorder);

                    foreach ($orders->getManagerorders() as $ord) {

                        if ($orders->getWorkeropen()->getId() == $ord->getWorkers()->getId()) {
                            $ord->setOpenprice($priceOpen);
                        }

                        if ($orders->getWorkerclose()->getId() == $ord->getWorkers()->getId()) {
                            $ord->setCloseprice($priceClose);
                        }
                    }
                }
            } elseif($orders->getWorkeropen() && !$orders->getWorkerclose()) {

                $managerorder = new ManagerOrders();
                $managerorder->setWorkers($orders->getWorkeropen());
                $orders->addManagerorders($managerorder);
                foreach ($orders->getManagerorders() as $ord) {
                    $ord->setOpenprice($priceOpen);
                }

            } elseif (!$orders->getWorkeropen() && $orders->getWorkerclose()) {

                $managerorder = new ManagerOrders();
                $managerorder->setWorkers($orders->getWorkerclose());
                $orders->addManagerorders($managerorder);
                foreach ($orders->getManagerorders() as $ord) {

                    if ($order->getWorkerclose()->getId() == $ord->getWorkers()->getId()) {
                        $ord->setCloseprice($priceClose);
                    }
                }

            }



            foreach ($orders->getManagerorders() as $managerorder) {

                if (count($orders->getManagerorders()) == 2) {

                    foreach($workers as $key => $worker) {
                        if ($worker->getId() == $managerorder->getWorkers()->getId()) {
                            if ($key == "open") {
                                $managerorder->setOpenprice($priceOpen);
                                $managerorder->setCloseprice(null);
                            } elseif ($key == "close") {
                                $managerorder->setCloseprice($priceClose);
                                $managerorder->setOpenprice(null);
                            }
                        }
                    }
                } else {

                    if ($orders->getWorkeropen()) {

                        if (isset($priceClose)) {
                            $managerorder->setCloseprice($priceClose);
                        }

                        if (isset($priceOpen)) {
                            $managerorder->setOpenprice($priceOpen);
                        }

                        $managerorder->setWorkers($orders->getWorkeropen());

                    } elseif($orders->getWorkerclose()) {

                        if (isset($priceClose)) {
                            $managerorder->setCloseprice($priceClose);
                        }

                        if (isset($priceOpen)) {
                            $managerorder->setOpenprice($priceOpen);
                        }

                        $managerorder->setWorkers($orders->getWorkerclose());
                    }

                }

            }

            $entityManager->persist($orders);
            $entityManager->flush();

            // redirect back to some edit page
            return $this->redirectToRoute('orders_edit', ['id' => $id]);
        }

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $tableName = [];
        $tableSettingsName = [];
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Workers')->getTableName()] = "Сотрудники";
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Services')->getTableName()] = "Услуги";
        $tableSettingsName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:User')->getTableName()] = "Пользователи";
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Materials')->getTableName()] = "Материалы";
        $tableSettingsName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Measure')->getTableName()] = "Единицы измерения";
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Orders')->getTableName()] = "Заказ-наряд";
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Expenses')->getTableName()] = "Расход";
        $tableName[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars = [];
        $tableCars[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$entityManager->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";
        // render some form template
        return $this->render('orders/edit.html.twig', array(
            'order' => $orders,
            'workerCart' => $workerCart,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
        ));
    }

    /**
     * Deletes a order entity.
     *
     */
    public function deleteAction(Request $request, Orders $order)
    {
        $form = $this->createDeleteForm($order);
        $form->handleRequest($request);
        $workerorders = $order->getWorkerorders();
        $managerorders = $order->getManagerorders();
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            foreach($workerorders as $orders) {
                $em->persist($orders);
                $em->remove($orders);
            }
            foreach($managerorders as $orders) {
                $em->persist($orders);
                $em->remove($orders);
            }
            $em->remove($order);
            $em->flush();
        }

        return $this->redirectToRoute('orders_index');
    }

    public function ajaxAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        if ($request->get('idWorker') && $request->get('idWorker') != "") {

            $id = $request->get('idWorker');
            $workerservice = $em->createQuery(
                'SELECT w FROM kiraxeAdminCrmBundle:WorkerService w where w.workers in (' . implode(',', $id) . ')'
            )->getResult();
            $step = 0;
            foreach ($workerservice as $workerservice) {
                $result['workerservice'][$step] = array(
                    'worker_id' => $workerservice->getWorkers()->getId(),
                    'service_id' => $workerservice->getServices()->getId(),
                );
                $step++;
            }



            if($result['workerservice']) {
                $step = 0;
                foreach ($result['workerservice'] as $service) {
                    $services_parent = $em->createQuery(
                        'SELECT sp FROM kiraxeAdminCrmBundle:Services sp where sp.id =' . $service['service_id']
                    )->getResult();
                    foreach ($services_parent as $parent) {
                        $output['parent'][$step] = array(
                            'worker_id' => $service['worker_id'],
                            'id' => $parent->getId(),
                            'name' => $parent->getName(),
                        );
                    }
                    $step++;
                }
            }
        }

        if ($request->get('idSelect') && $request->get('idSelect') != "") {

            $id = $request->get('idSelect');
            $services = $em->createQuery(
                'SELECT s FROM kiraxeAdminCrmBundle:Services s where s.parent in (' . implode(',', $id) . ')' . 'ORDER BY s.parent ASC'
            )->getResult();

            $materials = $em->createQuery(
                'SELECT m FROM kiraxeAdminCrmBundle:Materials m where m.service in (' . implode(',', $id) . ')' . 'and m.residue != 0 and m.residue is not null'
            )->getResult();

            $output = array();
            $step = 0;
            foreach ($services as $service) {
                $output['services'][$step] = array(
                    'parent_id' => $service->getParent()->getId(),
                    'id' => $service->getId(),
                    'name' => $service->getName(),
                    'free' => $service->getFree(),
                    'pricefr' => $service->getPricefr()
                );
                $step++;
            }
            $step = 0;
            foreach ($materials as $material) {
                $output['materials'][$step] = array(
                    'id' => $material->getId(),
                    'name' => $material->getName(),
                );
                $step++;
            }
        } else if((!$request->get('idSelect') && !$request->get('idWorker')) || ($request->get('idSelect') !="" && $request->get('idWorker') !="")) {

            /*$services = $em->createQuery(
                'SELECT s FROM kiraxeAdminCrmBundle:Services s where s.parent is not null'
            )->getResult();

            $materials = $em->getRepository('kiraxeAdminCrmBundle:Materials')->findAll();

            $output = array();
            $step = 0;
            foreach ($services as $service) {
                $output['services'][$step] = array(
                    'id' => $service->getId(),
                    'name' => $service->getName(),
                );
                $step++;
            }
            $step = 0;
            foreach ($materials as $material) {
                $output['materials'][$step] = array(
                    'id' => $material->getId(),
                    'name' => $material->getName(),
                );
                $step++;
            }*/
            $output = null;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;

    }

    public function ajaxmodelAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        if ($request->get('idSelect')) {
            $id = $request->get('idSelect');
            $brand = $em->createQuery(
                'SELECT b FROM kiraxeAdminCrmBundle:Model b where b.brand =' . $id
            )->getResult();

            $output = array();
            $step = 0;
            foreach ($brand as $brand) {
                $output['brand'][$step] = array(
                    'id' => $brand->getId(),
                    'name' => $brand->getName(),
                );
                $step++;
            }
        } else {

            $brand = $em->getRepository('kiraxeAdminCrmBundle:Brand')->findAll();

            $output = array();
            $step = 0;
            foreach ($brand as $brand) {
                $output['brand'][$step] = array(
                    'id' => $brand->getId(),
                    'name' => $brand->getName(),
                );
                $step++;
            }
        }


        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;

    }

    /**
     * Creates a form to delete a order entity.
     *
     * @param Orders $order The order entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Orders $order)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('orders_delete', array('id' => $order->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
