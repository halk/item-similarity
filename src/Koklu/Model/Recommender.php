<?php
namespace Koklu\Model;

use Doctrine\ODM\MongoDB\DocumentManager;
use Koklu\Entity\Item;
use Koklu\Entity\Similar;
use Koklu\Model\Similarity\TanimotoCoefficient;

class Recommender
{
    /** @var DocumentManager */
    protected $dm;
    /** @var ItemManager */
    protected $im;

    /**
     * @param DocumentManager $documentManager
     * @param ItemManager $itemManager
     */
    public function __construct(DocumentManager $documentManager, ItemManager $itemManager)
    {
        $this->dm = $documentManager;
        $this->im = $itemManager;
    }

    /**
     * @param string|array $itemIds
     * @param string|null  $collection
     * @param int|bool     $limit
     * @return array
     */
    public function recommend($itemIds, $collection = null, $limit = false)
    {
        if (!is_array($itemIds)) {
            $itemIds = [$itemIds];
        }

        $results = $this->im->getRepository()->findSimilarItems($collection, $itemIds, $limit);

        $similarItems = [];
        foreach ($results['result'] as $result) {
            $similarItems[$result['_id']] = $result['score'];
        }

        return $similarItems;
    }

    /**
     * @param Item $item
     */
    public function onPost(Item $item)
    {
        $similarityAlgorithm = new TanimotoCoefficient();
        /** @var Item $otherItem */
        foreach ($this->im->getRepository()->getAllItemsExcept($item) as $otherItem) {
            // compute similarity
            $similarity = $similarityAlgorithm->getSimilarity(
                $item->getAttributes(), $otherItem->getAttributes()
            );
            // skip if similarity is 0
            if ($similarity <= 0) {
                continue;
            }
            // add other item to similar items for item
            $item->addSimilar(new Similar($similarity, $otherItem->getId()));
            // add item to similar items of the other item
            $otherItem->addSimilar(new Similar($similarity, $item->getId()));
            $this->dm->persist($otherItem);
        }
        $this->dm->persist($item);
        $this->dm->flush();
        $this->dm->clear();
    }

    /**
     * @param Item $item
     */
    public function onDelete(Item $item)
    {
        $this->im->getRepository()->removeAllSimilarReferencesForItem($item);
        $this->dm->clear();
    }
}