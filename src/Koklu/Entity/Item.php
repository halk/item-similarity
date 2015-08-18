<?php
namespace Koklu\Entity;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document(repositoryClass="\Koklu\Repository\ItemRepository") */
class Item
{
    /** @ODM\Id(strategy="NONE") */
    private $id;

    /** @ODM\Hash */
    private $attributes;

    /** @ODM\EmbedMany(targetDocument="Similar") */
    private $similar = [];

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getSimilar()
    {
        return $this->similar;
    }

    /**
     * @param Similar $similar
     */
    public function addSimilar(Similar $similar)
    {
        $this->similar[] = $similar;
    }
}
