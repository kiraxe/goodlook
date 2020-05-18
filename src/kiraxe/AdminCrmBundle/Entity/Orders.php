<?php

namespace kiraxe\AdminCrmBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Orders
 *
 * @ORM\Table(name="orders")
 * @ORM\Entity(repositoryClass="kiraxe\AdminCrmBundle\Repository\OrdersRepository")
 */
class Orders
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
     * @var \DateTime
     *
     * @ORM\Column(name="date_open", type="datetime", nullable=true)
     */
    private $dateOpen;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_close", type="datetime", nullable=true)
     */
    private $dateClose;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_payment", type="datetime", nullable=true)
     */
    private $datePayment;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="kiraxe\AdminCrmBundle\Entity\Model")
     * @ORM\JoinColumn(name="car_id", referencedColumnName="id")
     */
    private $car;

    /**
     * @ORM\ManyToOne(targetEntity="kiraxe\AdminCrmBundle\Entity\BodyType")
     * @ORM\JoinColumn(name="body_id", referencedColumnName="id")
     */
    private $body;

    /**
     * @var string
     *
     * @ORM\Column(name="number", type="string", length=255)
     */
    private $number;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255)
     */
    private $phone;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @var bool
     *
     * @ORM\Column(name="close", type="boolean")
     */
    private $close;

    /**
     * @var bool
     *
     * @ORM\Column(name="notification", type="boolean")
     */
    private $notification;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=255, nullable=true)
     */
    private $color;

    /**
     * @var string
     *
     * @ORM\Column(name="vin", type="string", length=255, nullable=true)
     */
    private $vin;


    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;


    /**
     * @var smallint
     *
     * @ORM\Column(name="payment", type="smallint")
     */
    private $payment;


    /**
     * @ORM\OneToMany(targetEntity="kiraxe\AdminCrmBundle\Entity\WorkerOrders", mappedBy="orders", fetch="EAGER", cascade={"persist"})
     */
    private $workerorders;


    /**
     * @ORM\OneToMany(targetEntity="kiraxe\AdminCrmBundle\Entity\ManagerOrders", mappedBy="orders", fetch="EAGER", cascade={"persist"})
     */
    private $managerorders;


    /**
     * @ORM\ManyToOne(targetEntity="kiraxe\AdminCrmBundle\Entity\Brand")
     * @ORM\JoinColumn(name="brand_id", referencedColumnName="id")
     */
    private $brand;


    /**
     * @ORM\ManyToOne(targetEntity="kiraxe\AdminCrmBundle\Entity\Workers", fetch="EAGER")
     * @ORM\JoinColumn(name="workeropen_id", referencedColumnName="id")
     */
    private $workeropen;


    /**
     * @ORM\ManyToOne(targetEntity="kiraxe\AdminCrmBundle\Entity\Workers", fetch="EAGER")
     * @ORM\JoinColumn(name="workerclose_id", referencedColumnName="id")
     */
    private $workerclose;


    /**
     * @var text
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var text
     *
     * @ORM\Column(name="recommendations", type="text", nullable=true)
     */
    private $recommendations;

    /**
     * @var text
     * @ORM\Column(name="damages", type="text", nullable=true)
     */
    private $damages;


    /**
     * @var text
     *
     * @ORM\Column(name="note", type="text", nullable=true)
     */
    private $note;

    /**
     * @ORM\Column(name="images", type="text", nullable=true)
     */
    private $images;



    public function __construct()
    {
        $this->workerorders = new ArrayCollection();
        $this->managerorders = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dateOpen
     *
     * @param \DateTime $dateOpen
     *
     * @return Orders
     */
    public function setDateOpen($dateOpen)
    {
        $this->dateOpen = $dateOpen;

        return $this;
    }

    /**
     * Get dateOpen
     *
     * @return \DateTime
     */
    public function getDateOpen()
    {
        return $this->dateOpen;
    }

    /**
     * Set dateClose
     *
     * @param \DateTime $dateClose
     *
     * @return Orders
     */
    public function setDateClose($dateClose)
    {
        $this->dateClose = $dateClose;

        return $this;
    }

    /**
     * Get dateClose
     *
     * @return \DateTime
     */
    public function getDateClose()
    {
        return $this->dateClose;
    }

    /**
     * Set datePayment
     *
     * @param \DateTime $datePayment
     *
     * @return Orders
     */
    public function setDatePayment($datePayment)
    {
        $this->datePayment = $datePayment;

        return $this;
    }

    /**
     * Get datePayment
     *
     * @return \DateTime
     */
    public function getDatePayment()
    {
        return $this->datePayment;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Orders
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set carId
     *
     * @param integer $carId
     *
     * @return Orders
     */
    public function setCarId($car)
    {
        $this->car = $car;

        return $this;
    }

    /**
     * Get carId
     *
     * @return int
     */
    public function getCarId()
    {
        return $this->car;
    }

    /**
     * Set body
     *
     * @param integer $body
     *
     * @return Orders
     */
    public function setBodyId($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return int
     */
    public function getBodyId()
    {
        return $this->body;
    }

    /**
     * Set number
     *
     * @param string $number
     *
     * @return Orders
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Orders
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set price
     *
     * @param float $price
     *
     * @return Orders
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

    /**
     * Set close
     *
     * @param boolean $close
     *
     * @return Orders
     */
    public function setClose($close)
    {
        $this->close = $close;

        return $this;
    }

    /**
     * Get notification
     *
     * @return bool
     */
    public function getClose()
    {
        return $this->close;
    }


    /**
     * Set notification
     *
     * @param boolean $notification
     *
     * @return Orders
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Get notification
     *
     * @return bool
     */
    public function getNotification()
    {
        return $this->notification;
    }


    public function getWorkerorders()
    {
        return $this->workerorders;
    }
    public function addWorkerorder(WorkerOrders $workerorders)
    {
        $workerorders->setOrders($this);
        $this->workerorders->add($workerorders);
    }

    public function removeWorkerorder(WorkerOrders $workerorders)
    {
        $this->workerorders->removeElement($workerorders);
    }

    public function setBrandId($brand)
    {
        $this->brand = $brand;

        return $this;
    }


    public function getBrandId()
    {
        return $this->brand;
    }


    public function setWorkeropen($workeropen)
    {
        $this->workeropen = $workeropen;

        return $this;
    }

    public function getWorkeropen()
    {
        return $this->workeropen;
    }

    public function setWorkerclose($workerclose)
    {
        $this->workerclose = $workerclose;

        return $this;
    }


    public function getWorkerclose()
    {
        return $this->workerclose;
    }


    public function addManagerorders(ManagerOrders $managerorders)
    {
        $managerorders->setOrders($this);
        $this->managerorders->add($managerorders);
    }


    public function getManagerorders()
    {
        return $this->managerorders;
    }


    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }


    public function getDescription()
    {
        return $this->description;
    }

    public function setRecommendations($recommendations)
    {
        $this->recommendations = $recommendations;
        return $this;
    }

    public function getRecommendations()
    {
        return $this->recommendations;
    }

    public function setDamages($damages)
    {
        $this->damages = $damages;

        return $this;
    }


    public function getDamages()
    {
        return $this->damages;
    }


    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }


    public function getColor()
    {
        return $this->color;
    }


    public function setVin($vin)
    {
        $this->vin = $vin;

        return $this;
    }


    public function getVin()
    {
        return $this->vin;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }


    public function getEmail()
    {
        return $this->email;
    }


    public function setPayment($payment)
    {
        $this->payment = $payment;

        return $this;
    }


    public function getPayment()
    {
        return $this->payment;
    }


    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    public function getNote()
    {
        return $this->note;
    }

    public function getImages()
    {
        return $this->images;
    }

    public function setImages($images)
    {
        $this->images = $images;
        return $this;
    }
}
