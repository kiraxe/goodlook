<?php

namespace kiraxe\AdminCrmBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use kiraxe\AdminCrmBundle\Entity\ManagerOrders;
use kiraxe\AdminCrmBundle\Entity\Materials;
use kiraxe\AdminCrmBundle\Entity\Orders;
use kiraxe\AdminCrmBundle\Entity\Workers;
use kiraxe\AdminCrmBundle\Entity\Clientele;
use kiraxe\AdminCrmBundle\Entity\Brand;
use kiraxe\AdminCrmBundle\Entity\BodyType;
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
use Symfony\Component\Filesystem\Filesystem;

use kiraxe\AdminCrmBundle\Services\TableMeta\TableMeta;

use kiraxe\AdminCrmBundle\Services\FileUploaderImages\FileUploaderImages;


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
    public function indexAction(Request $request, TableMeta $tableMeta)
    {
        $em = $this->getDoctrine()->getManager();
        $deleteForm = null;

        $tableName = $tableMeta->getTableName($em);

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

        if (!empty($request->query->get('form')['worker'])) {

            $wr = $request->query->get('form')['worker'];
            $wrs = $em->getRepository('kiraxeAdminCrmBundle:WorkerOrders')->findBy(array('workers' => $wr));
            $wr_ar = [];


            $step = 0;
            foreach ($wrs as $item) {
                $wr_ar[$step] = $item->getOrders()->getId();
                $step++;
            }

            $wr_ar = array_unique($wr_ar);

            $wr_ar = implode("','", $wr_ar);


            if ($sql == "SELECT o FROM kiraxeAdminCrmBundle:Orders o where") {
                $sql .= ' o.id IN ('."'".$wr_ar. "'" .") ";
            } else {
                $sql .= ' and o.id IN ('."'".$wr_ar. "'" .") ";
            }
        }

        if (!empty($request->query->get('form')['services'])) {

            $sr = $request->query->get('form')['services'];
            $srs = $em->getRepository('kiraxeAdminCrmBundle:WorkerOrders')->findBy(array('serviceparent' => $sr));
            $sr_ar = [];


            $step = 0;
            foreach ($srs as $item) {
                $sr_ar[$step] = $item->getOrders()->getId();
                $step++;
            }

            $sr_ar = array_unique($sr_ar);

            $sr_ar = implode("','", $sr_ar);


            if ($sql == "SELECT o FROM kiraxeAdminCrmBundle:Orders o where") {
                $sql .= ' o.id IN ('."'".$sr_ar. "'" .") ";
            } else {
                $sql .= ' and o.id IN ('."'".$sr_ar. "'" .") ";
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

        if (empty($request->query->get('form')['dateFrom']) && empty($request->query->get('form')['tel']) && empty($request->query->get('form')['manager']) && empty($request->query->get('form')['number']) && empty($request->query->get('form')['close']) && empty($request->query->get('form')['worker']) && empty($request->query->get('form')['services'])) {
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
            ->add('worker', EntityType::class , array(
                'class' => 'kiraxe\AdminCrmBundle\Entity\Workers',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $worker) {
                    return $worker->createQueryBuilder('w')->where("w.typeworkers = 0");
                },
                'label' => 'Сортировка по работнику',
                'required' => false,
                'placeholder' => 'Выберите работник',
                'empty_data' => null,
            ))
            ->add('services', EntityType::class , array(
                'class' => 'kiraxe\AdminCrmBundle\Entity\Services',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $service) {
                    return $service->createQueryBuilder('s')->where("s.parent is null");
                },
                'label' => 'Сортировка по услуге',
                'required' => false,
                'placeholder' => 'Выберите услугу',
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

        for($i = 0; $i < count($orders); $i++) {
            $deleteForm[$orders[$i]->getId()] = $this->createDeleteForm($orders[$i])->createView();
        }

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $orders, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            50 /*limit per page*/
        );

        return $this->render('orders/index.html.twig', array(
            'form' => $form->createView(),
            'orders' => $orders,
            'delete_form' => $deleteForm,
            'tables' => $tableName,
            'pagination' => $pagination,
            'user' => $user,
        ));
    }

    /**
     * Creates a new order entity.
     *
     */
    public function newAction(Request $request, TableMeta $tableMeta, FileUploaderImages $fileUploaderImages)
    {
        $order = new Orders();
        $managerorder = new ManagerOrders();
        $managerordersecond = new ManagerOrders();
        $client = new Clientele();
        $form = $this->createForm('kiraxe\AdminCrmBundle\Form\OrdersType', $order);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);

        $serviceparent = null;
        $price = null;
        $unitprice = null;

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $freelancerPrice = null;

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
                        } else {
                            $unitprice = null;
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
                if ($workerorders->getWorkers()->getFreelancer()) {
                    $freelancerPrice += $workerorders->getSalary();
                }
            }

            $order->setPrice($price);

            if(!$freelancerPrice) {
                $priceOrder = $order->getPrice();
            } else {
                $priceOrder = $order->getPrice() - $freelancerPrice;
            }

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

            $clienteles = $em->getRepository('kiraxeAdminCrmBundle:Clientele')->findAll();
            //$orders = $em->getRepository('kiraxeAdminCrmBundle:Orders')->findAll();

            /*$cls = array();

            foreach ( $orders as $ord) {
                $avto = $ord->getBrandId()->getName() ." ". $ord->getCarId()->getName() ." ". $ord->getBodyId()->getName() ." ". $ord->getColor();
                $cls[] = [
                    'id' => $ord->getId(),
                    'name' => $ord->getName(),
                    'avto' => $avto,
                    'number' => $ord->getNumber(),
                    'vin' => $ord->getVin(),
                    'phone' => $ord->getPhone(),
                    'email' => $ord->getEmail()
                ];
            }

            $taken = array();

            foreach($cls as $key => $item) {
                if(!in_array($item['number'], $taken)) {
                    $taken[] = $item['number'];
                } else {
                    unset($cls[$key]);
                }
            }

            foreach($cls as $key => $item) {
                if(!in_array($item['vin'], $taken)) {
                    $taken[] = $item['vin'];
                } else {
                    unset($cls[$key]);
                }
            }

            foreach ( $cls as $cl) {
                $client = new Clientele();
                $client->setName($cl['name']);
                $client->setAvto($cl['avto']);
                $client->setNumber($cl['number']);
                $client->setVin($cl['vin']);
                $client->setPhone($cl['phone']);
                $client->setEmail($cl['email']);

                $em->persist($client);
            }
            */



            $flug = false;
            $avto = $order->getBrandId()->getName() ." ". $order->getCarId()->getName() ." ". $order->getBodyId()->getName() ." ". $order->getColor();

            foreach ($clienteles as $clientele) {
                if (($clientele->getName() == $order->getName() && $clientele->getNumber() == $order->getNumber()) || ($clientele->getName() == $order->getName() && $clientele->getVin() == $order->getVin()) || ($clientele->getName() == $order->getName() && $clientele->getPhone() == $order->getPhone())) {
                    $flug = false;
                } else {
                    $flug = true;
                }
            }

            if ($flug) {

                $client->setName($order->getName());
                $client->setAvto($avto);
                $client->setNumber($order->getNumber());
                $client->setVin($order->getVin());
                $client->setPhone($order->getPhone());
                $client->setEmail($order->getEmail());

                $em->persist($client);
            }

            $files = $request->files->all();

            if(!empty($files['kiraxe_admincrmbundle_orders']['images'])) {



                //$fileNames = [];

                $filesystem = new Filesystem();

                //$filesystem->remove([$fileUploaderImages->getTargetDir().'order_'.$order->getId()]);

                foreach ($files['kiraxe_admincrmbundle_orders']['images'] as $file) {
                    $fileNames[] = $fileUploaderImages->upload($file, $order);
                }

                $order->setImages(json_encode($fileNames));
            } else {
                $order->setImages(null);
            }

            $em->persist($order);
            $em->flush();

            if(!empty($files['kiraxe_admincrmbundle_orders']['images'])) {
                $filesystem->rename($fileUploaderImages->getTargetDir() . 'order_', $fileUploaderImages->getTargetDir() . 'order_' . $order->getId());
            }

            return $this->redirectToRoute('orders_edit', array('id' => $order->getId()));
        }

        return $this->render('orders/new.html.twig', array(
            'order' => $order,
            'form' => $form->createView(),
            'tables' => $tableName,
            'user' => $user,
        ));
    }

    /**
     * Finds and displays a order entity.
     *
     */
    public function showAction(Orders $orders, TableMeta $tableMeta)
    {
        $deleteForm = $this->createDeleteForm($orders);

        $em = $this->getDoctrine()->getManager();

        $originalTags = new ArrayCollection();

        $tableName = $tableMeta->getTableName($em);

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

        return $this->render('orders/show.html.twig', array(
            'order' => $orders,
            'workerCart' => $workerCart,
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'workerorders' => $originalTags,
            'user' => $user,
        ));
    }

    /**
     * Displays a form to edit an existing order entity.
     *
     */
    public function editAction($id, Request $request, TableMeta $tableMeta, FileUploaderImages $fileUploaderImages)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $orders = $entityManager->getRepository(Orders::class)->find($id);

        $tableName = $tableMeta->getTableName($entityManager);
        $images = $orders->getImages();

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
        $freelancerPrice = null;

        $editForm->handleRequest($request);


        if ($editForm->isSubmitted()) {
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
                        } else {
                            $unitprice = null;
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

                if ($workerorders->getWorkers()->getFreelancer()) {
                    $freelancerPrice += $workerorders->getSalary();
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

            if(!$freelancerPrice) {
                $priceOrder = $orders->getPrice();
            } else {
                $priceOrder = $orders->getPrice() - $freelancerPrice;
            }

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


            $files = $request->files->all();


            if(!empty($files['kiraxe_admincrmbundle_orders']['images'])) {

                //$fileNames = [];

                //$filesystem = new Filesystem();

                //$filesystem->remove([$fileUploaderImages->getTargetDir().'order_'.$orders->getId()]);

                foreach ($files['kiraxe_admincrmbundle_orders']['images'] as $file) {
                    $fileNames[] = $fileUploaderImages->upload($file, $orders);
                }

                $orders->setImages(json_encode($fileNames));
            }

            if(isset($request->get('kiraxe_admincrmbundle_orders')['loaded'])) {
                if(isset($fileNames)) {
                    $fileNames = array_merge($fileNames, $request->get('kiraxe_admincrmbundle_orders')['loaded']);
                } else {
                    $fileNames = $request->get('kiraxe_admincrmbundle_orders')['loaded'];
                }
                $orders->setImages(json_encode($fileNames));
            }

            if (!isset($request->get('kiraxe_admincrmbundle_orders')['loaded']) && empty($files['kiraxe_admincrmbundle_orders']['images'])) {
                $orders->setImages(null);
            }


            $entityManager->persist($orders);
            $entityManager->flush();

            // redirect back to some edit page
            return $this->redirectToRoute('orders_edit', ['id' => $id]);
        }

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();




        // render some form template
        return $this->render('orders/edit.html.twig', array(
            'order' => $orders,
            'images' => json_decode($images),
            'workerCart' => $workerCart,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'tables' => $tableName,
            'user' => $user,
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

            $brand = $em->getRepository('kiraxeAdminCrmBundle:Model')->findAll();

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

    public function ajaxautocompleteAction(Request $request) {

        $data = file_get_contents('php://input');
        $data = json_decode($data);
        $output = array();

        $value = $data->param;
        $data_type = "c.".$data->data_type;

        $em = $this->getDoctrine()->getManager();

        if ($value != "") {
            $clienteles = $em->createQuery(
                'SELECT c FROM kiraxeAdminCrmBundle:Clientele c where '.$data_type. ' like ' . ':value'
            )->setParameter('value', '%' . $value . '%')->getResult();

            $step = 0;
            foreach ($clienteles as $clientele) {
                $output['clientele'][$step] = array(
                    'name' => $clientele->getName(),
                    'avto' => $clientele->getAvto(),
                    'number' => $clientele->getNumber(),
                    'vin' => $clientele->getVin(),
                    'phone' => $clientele->getPhone(),
                    'email' => $clientele->getEmail()
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
