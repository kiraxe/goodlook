<?php

namespace kiraxe\AdminCrmBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Admin controller.
 *
 * @Route("admin")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="admin_index", methods={"GET"})
     */
    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
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
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Clientele')->getTableName()] = "Клиенты";
        $tableName[$em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName()] = "Календарь";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName()] = "Бренд автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName()] = "Модель автомобиля";
        $tableCars[$em->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName()] = "Тип кузова";


        $sql = "SELECT o FROM kiraxeAdminCrmBundle:Orders o where o.close = 1";
        $sqlExpenses = "SELECT e FROM kiraxeAdminCrmBundle:Expenses e where";
        $orders = null;
        $expenses = null;

        if (!empty($request->query->get('form')['dateFrom']) && empty($request->query->get('form')['dateTo'])) {
            $dateFrom = $request->query->get('form')['dateFrom'];
            $dateFrom = str_replace("-", "", $dateFrom);
            $sql .= " and date(o.dateClose) =".$dateFrom;
            $sqlExpenses .= " date(e.date) =".$dateFrom;
        }

        if (empty($request->query->get('form')['dateFrom']) && !empty($request->query->get('form')['dateTo'])) {
            $dateTo = $request->query->get('form')['dateTo'];
            $dateTo = str_replace("-", "", $dateTo);
            $sql .= " and date(o.dateClose) =".$dateTo;
            $sqlExpenses .= " date(e.date) =".$dateTo;
        }

        if (!empty($request->query->get('form')['dateFrom']) && !empty($request->query->get('form')['dateTo'])) {
            $dateFrom = $request->query->get('form')['dateFrom'];
            $dateTo = $request->query->get('form')['dateTo'];
            $dateFrom = str_replace("-", "", $dateFrom);
            $dateTo = str_replace("-", "", $dateTo);
            $sql .= " and date(o.dateClose) between " . $dateFrom . " and " . $dateTo;
            $sqlExpenses .= " date(e.date) between " . $dateFrom . " and " . $dateTo;
        }

        if (!empty($request->query->get('form')['dateFrom'])) {
            $orders = $em->createQuery($sql)->getResult();
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

        $price = 0;
        $salary = 0;
        $totalExpenses = 0;
        $totalExpensesOne = 0;
        $totalExpensesSecond = 0;
        $partExpenses = 0;
        $interestpayments = 0;
        $earnings = 0;
        $workers_id = [];
        $workerCart = [];


        if ($expenses != null) {
            foreach ($expenses as $expense) {
                $partExpenses += $expense->getAmount();
            }
        }

        if ($orders != null) {
            $step = 0;
            foreach ($orders as $order) {
                $price += $order->getPrice();
                if ($order->getPayment() == 2) {
                    $interestpayments += ($order->getPrice() / 100) * 1.85;
                }
                foreach ($order->getWorkerorders() as $workerorder) {
                    $salary += $workerorder->getSalary();
                    $workers_id[$step] = $workerorder->getWorkers()->getId();

                    if ($workerorder->getSalary() > 0 || $workerorder->getSalary() < 0) {
                        $totalExpenses += $workerorder->getSalary();
                        $totalExpensesOne += ($workerorder->getPriceUnit() * $workerorder->getAmountOfMaterial()) + $workerorder->getSalary();
                        $totalExpensesSecond += $workerorder->getSalary();
                    } else {
                        $totalExpensesOne += ($workerorder->getPriceUnit() * $workerorder->getAmountOfMaterial());
                    }

                    $step++;
                }

                foreach ($order->getManagerorders() as $managerorder) {
                    if ($managerorder->getWorkers()) {
                        $salary += $managerorder->getOpenprice() + $managerorder->getCloseprice();
                        $workers_id[$step] = $managerorder->getWorkers()->getId();
                        $totalExpenses += $managerorder->getCloseprice() + $managerorder->getOpenprice();
                        $totalExpensesOne += $managerorder->getCloseprice() + $managerorder->getOpenprice();
                        $totalExpensesSecond += $managerorder->getCloseprice() + $managerorder->getOpenprice();
                        $step++;
                    }


                }


            }



            $workers_id = array_unique($workers_id);
            $workers_id = array_values($workers_id);

            for ($i = 0; $i < count($workers_id); $i++) {
                $workerCart[$i] = array(
                    'id' => $workers_id[$i],
                    'name' => '',
                    'salary' => 0
                );
            }

            foreach ($orders as $order) {

                foreach ($order->getWorkerorders() as $workerorder) {
                    for ($i = 0; $i < count($workerCart); $i++) {
                        if ($workerCart[$i]['id'] == $workerorder->getWorkers()->getId()) {
                            $workerCart[$i]['name'] = $workerorder->getWorkers()->getName();
                            $workerCart[$i]['salary'] += $workerorder->getSalary();
                        }
                    }
                }
                foreach ($order->getManagerorders() as $managerorder) {
                    if ($managerorder->getWorkers()) {
                        for ($i = 0; $i < count($workerCart); $i++) {
                            if ($workerCart[$i]['id'] == $managerorder->getWorkers()->getId()) {
                                $workerCart[$i]['name'] = $managerorder->getWorkers()->getName();
                                $workerCart[$i]['salary'] += $managerorder->getOpenprice() + $managerorder->getCloseprice();
                            }
                        }
                    }
                }
            }
        }

        $earnings = $price - ($totalExpenses + $interestpayments);
        $earningsOne = $price - ($totalExpensesOne + $interestpayments);
        $earningsSecond = $price - ($totalExpensesSecond + $partExpenses + $interestpayments);

        echo  $salary;
        /*echo $earningsSecond;
        echo '<br/>';
        echo $price . "-" . "(" . $totalExpensesSecond . "+" . $partExpenses . "+" . $interestpayments .")";
        echo '<br/>';
        echo 1398760 - (575604.13450292 + 703764 + 16399.51);
        */

        return $this->render('default/index.html.twig', array(
            'form' => $form->createView(),
            'price' => $price,
            'salary' => $salary,
            'totalExpenses' => $partExpenses,
            'earnings' => $earnings,
            'earningsOne' => $earningsOne,
            'earningsSecond' => $earningsSecond,
            'tables' => $tableName,
            'user' => $user,
            'tableSettingsName' => $tableSettingsName,
            'tableCars' => $tableCars,
            'workerCart' => $workerCart,
            'interestpayments'  => $interestpayments           
        ));
    }
}
