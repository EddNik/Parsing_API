<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NamespaceSymfony
 *
 * @ORM\Table(name="namespace_symfony")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NamespaceSymfonyRepository")
 */
class NamespaceSymfony
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
     * @ORM\Column(name="name", type="text")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;


    /**
     * @ORM\OneToMany(targetEntity="InterfaceSymfony", mappedBy="namespace")
     */
    private $interfaces;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ClassSymfony", mappedBy="namespace")
     */
    private $classes;

    public function __construct($name, $url)
    {
        $this->name = $name;
        $this->url = $url;
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return NamespaceSymfony
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return NamespaceSymfony
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInterfaces()
    {
        return $this->interfaces;
    }

    /**
     * @return mixed
     */
    public function getClasses()
    {
        return $this->classes;
    }

}

