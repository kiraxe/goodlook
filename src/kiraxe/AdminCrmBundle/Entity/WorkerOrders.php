<?php

namespace kiraxe\AdminCrmBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WorkerOrders
 *
 * @ORM\Table(name="worker_orders")
 * @ORM\Entity(repositoryClass="kiraxe\AdminCrmBundle\Repository\WorkerOrdersRepository")
 */
class WorkerOrders
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Many features have one product. This is the owning side.
     * @ORM\ManyToOne(targetEntity="kiraxe\AdminCrmBundle\Entity\Workers", fetch="EAGER", inversedBy="workerorders")
     * @ORM\JoinColumn(name="worker_id", referencedColumnName="id")
     */
    private $workers;

    /**
     * @ORM\ManyToOne(targetEntity="kiraxe\AdminCrmBundle\Entity\Orders", fetch="EAGER", inversedBy="workerorders")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $orders;

    /**
     * @ORM\ManyToOne(targetEntity="kiraxe\AdminCrmBundle\Entity\Services", fetch="EAGER", inversedBy="workerorders")
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id")
     */
    private $services;

    /**
     * @ORM\ManyToOne(targetEntity="kiraxe\AdminCrmBundle\Entity\Services", fetch="EAGER")
     * @ORM\JoinColumn(name="serviceparent_id", referencedColumnName="id")
     */
    private $serviceparent;

    /**
     * @ORM\ManyToOne(targetEntity="kiraxe\AdminCrmBundle\Entity\Materials", fetch="EAGER", inversedBy="workerorders")
     * @ORM\JoinColumn(name="material_id", referencedColumnName="id")
     */
    private $materials;

    /**
     * @var float
     *
     * @ORM\Column(name="amount_of_material", type="float", nullable=true)
     */
    private $amountOfMaterial;


    /**
     * @var float
     *
     * @ORM\Column(name="marriage", type="float", options={"default":"0"}, nullable=true)
     */
    private $marriage;


    /**
     * @var float
     *
     * @ORM\Column(name="fine", type="float", options={"default":"0"}, nullable=true)
     */
    private $fine;

    /**
     * @var float
     *
     * @ORM\Column(name="salary", type="float", nullable=true)
     */
    private $salary;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;


    /**
     * @var string
     *
     * @ORM\Column(name="free", type="text", nullable=true)
     */
    private $free;

    /**
     * @var string
     *
     * @ORM\Column(name="pricefr", type="text", nullable=true)
     */
    private $pricefr;

    /**
     * @var string
     *
     * @ORM\Column(name="pricefrAction", type="boolean", nullable=true)
     */

    private $pricefrAction;

    /**
     * @var float
     *
     * @ORM\Column(name="priceUnit", type="float", nullable=true)
     */
    private $priceUnit;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setWorkers(Workers $worker = null)
    {
        $this->workers = $worker;
    }

    public function getWorkers()
    {
        return $this->workers;
    }

    public function setOrders(Orders $order = null)
    {
        $this->orders = $order;
    }

    public function getOrders()
    {
        return $this->orders;
    }

    public function setServices(Services $service = null)
    {
        $this->services = $service;
    }

    public function getServices()
    {
        return $this->services;
    }


    public function setServicesparent(Services $serviceparent = null)
    {
        $this->serviceparent = $serviceparent;

        return $this;
    }

    public function getServicesparent()
    {
        return $this->serviceparent;
    }

    public function setMaterials(Materials $material = null)
    {
        $this->materials = $material;

        return $this;
    }

    public function getMaterials()
    {
        return $this->materials;
    }

    /**
     * Set amountOfMaterial
     *
     * @param float $amountOfMaterial
     *
     * @return WorkerOrders
     */
    public function setAmountOfMaterial($amountOfMaterial)
    {
        $this->amountOfMaterial = $amountOfMaterial;

        return $this;
    }

    /**
     * Get amountOfMaterial
     *
     * @return float
     */
    public function getAmountOfMaterial()
    {
        return $this->amountOfMaterial;
    }

    /**
     * Set price
     *
     * @param float $price
     *
     * @return WorkerOrders
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }


    public function setSalary($salary)
    {
        if ($this->pricefrAction) {
            if ($this->marriage && !$this->fine) {
                $this->salary = $this->pricefr - ($this->getMaterials()->getPriceUnit() * $this->marriage);
            } elseif (!$this->marriage && $this->fine) {
                $this->salary = $this->pricefr - $this->fine;
            } elseif ($this->marriage && $this->fine) {
                $this->salary = ($this->pricefr - $this->fine) - ($this->getMaterials()->getPriceUnit() * $this->marriage);
            } else {
                $this->salary = $this->pricefr;
            }
        } else {
            $this->salary = $salary;
        }

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getSalary()
    {
        return $this->salary;
    }


    public function setMarriage($marriage)
    {
        $this->marriage = $marriage;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getMarriage()
    {
        return $this->marriage;
    }


    public function setFine($fine)
    {
        $this->fine = $fine;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getFine()
    {
        return $this->fine;
    }

    public function setFree($free)
    {
        $this->free = $free;

        return $this;
    }

    public function getFree()
    {
        return $this->free;
    }


    public function setPricefr($pricefr)
    {
        $this->pricefr = $pricefr;

        return $this;
    }

    public function getPricefr()
    {
        return $this->pricefr;
    }

    /**
     * @return string
     */
    public function getPricefrAction()
    {
        return $this->pricefrAction;
    }

    /**
     * @param string $pricefrAction
     */
    public function setPricefrAction($pricefrAction)
    {
        $this->pricefrAction = $pricefrAction;
    }

    /**
     * Set priceUnit
     *
     * @param float $priceUnit
     *
     * @return WorkerOrders
     */
    public function setPriceUnit($priceUnit)
    {
        $this->priceUnit = $priceUnit;

        return $this;
    }

    /**
     * Get priceUnit
     *
     * @return float
     */
    public function getPriceUnit()
    {
        return $this->priceUnit;
    }
}

