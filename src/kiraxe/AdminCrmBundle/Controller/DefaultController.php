<?php

namespace kiraxe\AdminCrmBundle\Controller;

use Doctrine\ORM\EntityRepository;
use kiraxe\AdminCrmBundle\Services\TableMeta\TableMeta;
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
    public function indexAction(Request $request, TableMeta $tableMeta)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $tableName = $tableMeta->getTableName($em);


        $sql = "SELECT o FROM kiraxeAdminCrmBundle:Orders o where o.close = 1";
        $sqlExpenses = "SELECT e FROM kiraxeAdminCrmBundle:Expenses e where";
        $sqlRevenues = "SELECT r FROM kiraxeAdminCrmBundle:Revenue r where";
        $orders = null;
        $expenses = null;
        $revenues = null;

        if (!empty($request->query->get('form')['dateFrom']) && empty($request->query->get('form')['dateTo'])) {
            $dateFrom = $request->query->get('form')['dateFrom'];
            $dateFrom = str_replace("-", "", $dateFrom);
            $sql .= " and date(o.dateClose) =".$dateFrom;
            $sqlExpenses .= " date(e.date) =".$dateFrom;
            $sqlRevenues .= " date(r.date) =".$dateFrom;
        }

        if (empty($request->query->get('form')['dateFrom']) && !empty($request->query->get('form')['dateTo'])) {
            $dateTo = $request->query->get('form')['dateTo'];
            $dateTo = str_replace("-", "", $dateTo);
            $sql .= " and date(o.dateClose) =".$dateTo;
            $sqlExpenses .= " date(e.date) =".$dateTo;
            $sqlRevenues .= " date(r.date) =".$dateTo;
        }

        if (!empty($request->query->get('form')['dateFrom']) && !empty($request->query->get('form')['dateTo'])) {
            $dateFrom = $request->query->get('form')['dateFrom'];
            $dateTo = $request->query->get('form')['dateTo'];
            $dateFrom = str_replace("-", "", $dateFrom);
            $dateTo = str_replace("-", "", $dateTo);
            $sql .= " and date(o.dateClose) between " . $dateFrom . " and " . $dateTo;
            $sqlExpenses .= " date(e.date) between " . $dateFrom . " and " . $dateTo;
            $sqlRevenues .= " date(r.date) between " . $dateFrom . " and " . $dateTo;
        }

        if (!empty($request->query->get('form')['dateFrom'])) {
            $orders = $em->createQuery($sql)->getResult();
            $expenses = $em->createQuery($sqlExpenses)->getResult();
            $revenues = $em->createQuery($sqlRevenues)->getResult();
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
        $partRevenue = 0;
        $interestpayments = 0;
        $earnings = 0;
        $priceInCash = 0;
        $priceNotCash = 0;
        $workers_id = [];
        $workerCart = [];


        if ($expenses != null) {
            foreach ($expenses as $expense) {
                $partExpenses += $expense->getAmount();
            }
        }

        if ($revenues != null) {
            foreach ($revenues as $revenue) {
                $partRevenue += $revenue->getAmount();
            }
        }

        $price += $partRevenue;

        if ($orders != null) {
            $step = 0;
            foreach ($orders as $order) {
                $price += $order->getPrice();
                if ($order->getPayment() == 2) {
                    $interestpayments += ($order->getPrice() / 100) * 1.85;
                    $priceNotCash += $order->getPrice();
                }
                if ($order->getPayment() == 0) {
                    $priceInCash += $order->getPrice();
                }
                if ($order->getPayment() == 1) {
                    $priceNotCash += $order->getPrice();
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

            for ($i = 0; $i < count($workerCart); $i++) {
                $workerCart[$i]['salary'] = round($workerCart[$i]['salary'], 1, PHP_ROUND_HALF_EVEN);
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
            'price' => round($price, 1, PHP_ROUND_HALF_EVEN),
            'salary' => round($salary, 1, PHP_ROUND_HALF_EVEN),
            'totalExpenses' => round($partExpenses, 1, PHP_ROUND_HALF_EVEN),
            'earnings' => round($earnings, 1, PHP_ROUND_HALF_EVEN),
            'earningsOne' => round($earningsOne, 1, PHP_ROUND_HALF_EVEN),
            'earningsSecond' => round($earningsSecond, 1, PHP_ROUND_HALF_EVEN),
            'tables' => $tableName,
            'user' => $user,
            'workerCart' => $workerCart,
            'interestpayments'  => round($interestpayments, 1, PHP_ROUND_HALF_EVEN),
            'priceNotCash' => $priceNotCash,
            'priceInCash' => $priceInCash,
        ));
    }


    /**
     * @Route("/", name="admin_ajaxautocom", methods={"GET"})
     */
    public function ajaxautocomAction(Request $request) {

        $data = file_get_contents('php://input');
        $data = json_decode($data);
        $output = array();

        $value = $data->param;
        $obj = $data->obj;
        $recover = $data->recover;
        $em = $this->getDoctrine()->getManager();

        if ($value != "" && !$recover) {
            $res = $em->createQuery('SELECT c FROM kiraxeAdminCrmBundle:'.$obj.' c where c.name like ' . ':value' . ' and c.active = 0')
                ->setParameter('value', '%' . $value . '%')->getResult();

            $step = 0;
            foreach ($res as $re) {
                $output[$obj][$step] = array(
                    'name' => $re->getName(),
                );
                $step++;
            }
        }

        if ($value != "" && $recover) {

            $res = $em->getRepository('kiraxeAdminCrmBundle:'.$obj.'')->findOneBy(array('active' => '0', 'name' => $value));

            if ($res->getParent()) {
                $res->getParent()->setActive(true);
            }

            $res->setActive(true);
            $em->persist($res);
            $em->flush();
        }

        $response = new Response();
        $response->headers->set('Content-type', 'application/json');
        $response->setContent(json_encode($output));

        return $response;

    }
}
