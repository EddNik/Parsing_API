<?php

namespace AppBundle\Entity;

/**
 * Interface PageItemInterface
 * @package AppBundle\Entity
 */
interface PageItemInterface
{
    public function setName($name);
    public function setUrl($url);
    public function setNamespace($namespace);
}
