<?php

namespace kiraxe\AdminCrmBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BodyType
 *
 * @ORM\Table(name="body_type")
 * @ORM\Entity(repositoryClass="kiraxe\AdminCrmBundle\Repository\BodyTypeRepository")
 */
class BodyType
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
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", options={"default":1})
     */
    private $active;


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
     * Set name
     *
     * @param string $name
     *
     * @return BodyType
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
     * Get active
     *
     * @return int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set active
     *
     * @param string $active
     *
     * @return BodyType
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }
}

