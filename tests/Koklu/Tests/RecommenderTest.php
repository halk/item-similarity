<?php
namespace Koklu\Tests;

use Silex\WebTestCase;
use Symfony\Component\HttpKernel\Client;

class RecommenderTest extends WebTestCase
{
    /** @var string */
    protected $_collection;
    /** @var Client */
    protected $_client;

    /**
     * Creates the application.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernel
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../../../src/app.php';
        unset($app['exception_handler']);

        return $app;
    }

    public function testBasicRecommendation()
    {
        $this->_setCollection('test1');

        $this->_postItem('item1', ['color' => 'red', 'size' => 3, 'name' => 'Apple']);
        $this->_postItem('item2', ['color' => 'red', 'size' => 3, 'name' => 'Peach']);
        $this->_postItem('item3', ['color' => 'red', 'size' => 1, 'name' => 'Cherry']);
        $this->_postItem('item4', ['color' => 'green', 'size' => 6, 'name' => 'Watermelon']);

        $recommendations = $this->_getRecommendation(['item1']);
        $this->assertEquals(['item2' => 0.5, 'item3' => 0.2], $recommendations);

        $recommendations = $this->_getRecommendation(['item2']);
        $this->assertEquals(['item1' => 0.5, 'item3' => 0.2], $recommendations);

        $recommendations = $this->_getRecommendation(['item3']);
        $this->assertEquals(['item1' => 0.2, 'item2' => 0.2], $recommendations);

        $recommendations = $this->_getRecommendation(['item4']);
        $this->assertEquals([], $recommendations);

        // adding another item and see if recommendation changed for item 4
        $this->_postItem('item5', ['color' => 'green', 'size' => 2, 'name' => 'Pear']);

        $recommendations = $this->_getRecommendation(['item4']);
        $this->assertEquals(['item5' => 0.2], $recommendations);

        // test recommendations for multiple items at the same time
        $recommendations = $this->_getRecommendation(['item1', 'item4']);
        $this->assertEquals(['item2' => 0.5, 'item5' => 0.2, 'item3' => 0.2], $recommendations);
    }

    public function testRemovingItem()
    {
        $this->_setCollection('test2');

        $this->_postItem('item1', ['color' => 'red', 'size' => 3, 'name' => 'Apple']);
        $this->_postItem('item2', ['color' => 'red', 'size' => 3, 'name' => 'Peach']);
        $this->_postItem('item3', ['color' => 'red', 'size' => 1, 'name' => 'Cherry']);

        $recommendations = $this->_getRecommendation(['item1']);
        $this->assertEquals(['item2' => 0.5, 'item3' => 0.2], $recommendations);

        // remove an item and see if it was removed from the recommendations
        $this->_deleteItem('item2');

        $recommendations = $this->_getRecommendation(['item1']);
        $this->assertEquals(['item3' => 0.2], $recommendations);

        $recommendations = $this->_getRecommendation(['item2']);
        $this->assertEquals([], $recommendations);

        $recommendations = $this->_getRecommendation(['item3']);
        $this->assertEquals(['item1' => 0.2], $recommendations);
    }

    public function testLimitRecommendations()
    {
        $this->_setCollection('test3');

        $this->_postItem('item1', ['color' => 'red', 'size' => 3, 'name' => 'Apple']);
        $this->_postItem('item2', ['color' => 'red', 'size' => 3, 'name' => 'Peach']);
        $this->_postItem('item3', ['color' => 'red', 'size' => 1, 'name' => 'Cherry']);
        $this->_postItem('item4', ['color' => 'green', 'size' => 3, 'name' => 'Pear']);

        $recommendations = $this->_getRecommendation(['item1']);
        $this->assertEquals(['item2' => 0.5, 'item3' => 0.2, 'item4' => 0.2], $recommendations);

        $recommendations = $this->_getRecommendation(['item1'], 1);
        $this->assertEquals(['item2' => 0.5], $recommendations);
    }

    public function testCommaSeparatedValues()
    {
        $this->_setCollection('test4');

        $this->_postItem('item1', ['color' => 'red,green', 'size' => 3, 'name' => 'Apple']);
        $this->_postItem('item2', ['color' => 'red', 'size' => 3, 'name' => 'Peach']);
        $this->_postItem('item3', ['color' => 'red', 'size' => 1, 'name' => 'Cherry']);
        $this->_postItem('item4', ['color' => 'green', 'size' => 1, 'name' => 'Grape']);

        $recommendations = $this->_getRecommendation(['item1']);
        $this->assertEquals(
            ['item2' => 0.4, 'item3' => 0.16667, 'item4' => 0.16667], $recommendations
        );
    }

    /**
     * @param string $collection
     */
    protected function _setCollection($collection)
    {
        $this->_collection = $collection;
        // clear collection before using
        /** @var \Doctrine\ODM\MongoDB\DocumentManager $dm */
        $dm = $this->app['doctrine.odm.mongodb.dm'];
        $dm->getClassMetadata('docs:Item')->setCollection($collection);
        $this->app['doctrine.odm.mongodb.dm']->createQueryBuilder('docs:Item')
            ->remove()
            ->getQuery()
            ->execute();
    }

    /**
     * @return Client
     */
    protected function _getClient()
    {
        if ($this->_client === null) {
            $this->_client = $this->createClient();
        }
        return $this->_client;
    }

    /**
     * @param string $itemId
     * @param array  $attributes
     */
    protected function _postItem($itemId, $attributes)
    {
        $this->_getClient()->request(
            'POST', '/' . $this->_collection, [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['item_id' => $itemId, 'attributes' => $attributes])
        );
        $this->assertEquals(204, $this->_getClient()->getResponse()->getStatusCode());
    }

    /**
     * @param string $itemId
     * @return array
     */
    protected function _deleteItem($itemId)
    {
        $this->_getClient()->request('DELETE', sprintf('/%s/%s', $this->_collection, $itemId));
        $this->assertEquals(204, $this->_getClient()->getResponse()->getStatusCode());
    }

    /**
     * @param array    $itemIds
     * @param bool|int $limit
     * @return array
     */
    protected function _getRecommendation($itemIds, $limit = false)
    {
        $params = ['itemIds' => $itemIds];
        if ($limit !== false) {
            $params['limit'] = $limit;
        }
        $this->_getClient()->request('GET', '/' . $this->_collection, $params);
        $this->assertEquals(200, $this->_getClient()->getResponse()->getStatusCode());

        $recommendations = json_decode($this->_getClient()->getResponse()->getContent(), true);
        $this->assertNotFalse($recommendations);

        return $recommendations;
    }
}
