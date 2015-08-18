<?php
namespace Koklu\Model;

use Doctrine\ODM\MongoDB\DocumentManager;
use Koklu\Entity\Item;
use Koklu\Repository\ItemRepository;

class ItemManager
{
    /** @var DocumentManager */
    protected $dm;

    /**
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * @param string      $itemId
     * @param string|null $collection
     * @return Item
     */
    public function get($itemId, $collection = null)
    {
        return $this->getRepository($collection)->find($itemId);
    }

    /**
     * @param string      $itemId
     * @param array       $data
     * @param string|null $collection
     * @return Item
     */
    public function post($itemId, array $data, $collection = null)
    {
        $this->setCollection($collection);
        // create new Item
        $item = new Item();
        $item->setId($itemId);
        $item->setAttributes($data);
        // save
        $this->dm->persist($item);
        $this->dm->flush();

        return $item;
    }

    /**
     * @param Item        $item
     * @param string|null $collection
     * @return Item
     */
    public function delete($item, $collection = null)
    {
        $this->setCollection($collection);
        $this->dm->remove($item);
        $this->dm->flush();

        return $item;
    }

    /**
     * Sets collection, this is useful when re-using the engine as this allows multiple data sets
     *
     * @param string|null $collection
     */
    public function setCollection($collection)
    {
        if ($collection !== null) {
            $this->dm->getClassMetadata('docs:Item')->setCollection($collection);
        }
    }

    /**
     * @param string|null $collection
     * @return ItemRepository
     */
    public function getRepository($collection = null)
    {
        $this->setCollection($collection);
        return $this->dm->getRepository('docs:Item');
    }
}