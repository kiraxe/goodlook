<?php

namespace kiraxe\AdminCrmBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Workers
 *
 * @ORM\Table(name="workers")
 * @ORM\Entity(repositoryClass="kiraxe\AdminCrmBundle\Repository\WorkersRepository")
 */
class Workers
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="passport", type="text", nullable=true)
     */
    private $passport;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="text", nullable=true)
     */
    private $address;

    /**
     * @var boolean
     *
     * @ORM\Column(name="type_workers", type="boolean")
     */
    private $typeworkers;

    /**
     * @var boolean
     *
     * @ORM\Column(name="freelancer", type="boolean", options={"default" : 0})
     */
    private $freelancer;

    // ...

    /**
     * @ORM\OneToMany(targetEntity="kiraxe\AdminCrmBundle\Entity\WorkerService", mappedBy="workers", fetch="EAGER", cascade={"persist"})
     */
    private $workerservice;

    /**
     * @ORM\OneToMany(targetEntity="kiraxe\AdminCrmBundle\Entity\ManagerPercent", mappedBy="workers", fetch="EAGER", cascade={"persist"})
     */
    private $managerpercent;

    /**
     * @ORM\OneToMany(targetEntity="kiraxe\AdminCrmBundle\Entity\WorkerOrders", mappedBy="workers", fetch="EAGER", cascade={"persist"})
     */
    private $workerorders;

    /**
     * @var boolean
     *
     * @ORM\Column(name="workeractive", type="boolean", options={"default":1})
     */
    private $workeractive;

    public function __construct()
    {
        $this->workerservice = new ArrayCollection();
        $this->workerorders = new ArrayCollection();
        $this->managerpercent = new ArrayCollection();
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
     * Set phone
     *
     * @param string $phone
     *
     * @return Workers
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
     * Set passport
     *
     * @param string $passport
     *
     * @return Workers
     */
    public function setPassport($passport)
    {
        $this->passport = $passport;

        return $this;
    }

    /**
     * Get passport
     *
     * @return string
     */
    public function getPassport()
    {
        return $this->passport;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Workers
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Workers
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getTypeworkers() {
        return $this->typeworkers;
    }

    public function setTypeworkers($typeworkers) {
        $this->typeworkers = $typeworkers;
    }


    public function getFreelancer() {
        return $this->freelancer;
    }

    public function setFreelancer($freelancer) {
        $this->freelancer = $freelancer;
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

    public function getWorkerService()
    {
        return $this->workerservice;
    }
    public function addWorkerservouse(WorkerService $workerservices)
    {
        $workerservices->setWorkers($this);
        $this->workerservice->add($workerservices);
    }

    public function removeWorkerservouse(WorkerService $workerservices)
    {
        $this->workerservice->removeElement($workerservices);
    }



    public function getWorkerOrders()
    {
        return $this->workerorders;
    }
    public function addWorkerOrders(WorkerOrders $workerorders)
    {
        $workerorders->setWorkers($this);
        $this->workerorders->add($workerorders);
    }

    public function removeWorkerOrders(WorkerOrders $workerorders)
    {
        $this->workerorders->removeElement($workerorders);
    }



    public function getManagerPercent()
    {
        return $this->managerpercent;
    }
    public function addManagerPercent(ManagerPercent $managerpercent)
    {
        $managerpercent->setWorkers($this);
        $this->managerpercent->add($managerpercent);
    }

    public function removeManagerpercent(ManagerPercent $managerpercent)
    {
        $this->managerpercent->removeElement($managerpercent);
    }


    /**
     * Get workeractive
     *
     * @return int
     */
    public function getWorkeractive()
    {
        return $this->workeractive;
    }

    /**
     * Set workeractive
     *
     * @param string $workeractive
     *
     * @return Workers
     */
    public function setWorkeractive($workeractive)
    {
        $this->workeractive = $workeractive;

        return $this;
    }


}
