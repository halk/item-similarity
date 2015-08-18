<?php
namespace Koklu\Entity;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\EmbeddedDocument */
class Similar
{
    /** @ODM\Float */
    private $score;

    /** @ODM\String */
    private $item;

    /**
     * @param float  $score
     * @param string $item
     */
    public function __construct($score, $item)
    {
        $this->score = $score;
        $this->item  = $item;
    }

    /**
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param float $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * @return string
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param string $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }
}
