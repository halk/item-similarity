<?php
namespace Koklu\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use Koklu\Model\ItemManager;
use Koklu\Model\Recommender;

class RecommenderService
{
    /** @var ItemManager */
    protected $_itemManager;
    /** @var Recommender */
    protected $_recommender;

    /**
     * @param DocumentManager $documentManager
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->_itemManager = new ItemManager($documentManager);
        $this->_recommender = new Recommender($documentManager, $this->_itemManager);
    }

    /**
     * @param string|array $itemIds
     * @param string|null  $collection
     * @param int|bool     $limit
     * @return array
     */
    public function recommend($itemIds, $collection = null, $limit = false)
    {
        return $this->_recommender->recommend($itemIds, $collection, $limit);
    }

    /**
     * @param int         $itemId
     * @param array       $data
     * @param string|null $collection
     */
    public function post($itemId, array $data, $collection = null)
    {
        $item = $this->_itemManager->get($itemId, $collection);
        if ($item) {
            $this->delete($item, $collection);
        }

        $item = $this->_itemManager->post($itemId, $data);
        $this->_recommender->onPost($item);
    }

    /**
     * @param string|Item $item
     * @param string|null $collection
     */
    public function delete($item, $collection = null)
    {
        if (is_string($item)) {
            $item = $this->_itemManager->get($item, $collection);
        }
        if ($item) {
            $this->_itemManager->delete($item, $collection);
            $this->_recommender->onDelete($item);
        }
    }
}
