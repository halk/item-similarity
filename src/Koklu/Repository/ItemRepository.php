<?php
namespace Koklu\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Koklu\Entity\Item;

class ItemRepository extends DocumentRepository
{
    /**
     * Returns all items except the one given as parameter
     *
     * @param Item $item
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAllItemsExcept(Item $item)
    {
        return $this->createQueryBuilder()
                ->field('id')->notEqual($item->getId())
                ->getQuery()
                ->execute();
    }

    /**
     * Removes all "similar" references of the given item
     *
     * @param Item $item
     */
    public function removeAllSimilarReferencesForItem(Item $item)
    {
        $this->createQueryBuilder()
            ->update()
            ->field('similar')->pull(['item' => $item->getId()])
            ->multiple(true)
            ->getQuery()
            ->execute();
    }

    /**
     * @param string   $collection
     * @param array    $forItems
     * @param int|bool $limit
     * @return array
     */
    public function findSimilarItems($collection, array $forItems, $limit)
    {
        $pipeline = [
            ['$match' => ['_id' => ['$in' => $forItems]]],
            ['$unwind' => '$similar'],
            ['$group' => ['_id' => '$similar.item', 'score' => ['$max' => '$similar.score']]],
            ['$sort' => ['score' => -1]]
        ];

        if ($limit !== false) {
            $pipeline[] = ['$limit' => (int) $limit];
        }

        // the Doctrine ODM for MongoDB has no aggregate feature yet, thus we fall back to the
        // PECL Mongo client
        return $this->getDocumentManager()->getDocumentDatabase($this->getClassName())->getMongoDB()
            ->selectCollection($collection)
            ->aggregate($pipeline);
    }
}
