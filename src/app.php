<?php

require __DIR__ . '/bootstrap.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// set assertion rule to all routes
$app['controllers']
  ->assert('collection', '[\w_]+')
  ->assert('itemId', '[[:ascii:]]+');

// decode json if content type is json
$app->before(function (Request $request) use ($app) {
    if (strpos($request->headers->get('Content-Type'), 'application/json') === 0) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : []);
    }
});

// index
$app->get('/', function () {
    return 'Welcome to the Item Similarity Recommender';
});

// adding / updating
$app->post('{collection}', function (Application $app, Request $request, $collection) {
    $itemId = $request->request->get('item_id');
    $attributes = $request->request->get('attributes');

    $app['koklu.recommender']->post($itemId, $attributes, $collection);

    return new Response('', 204);
});

// deleting
$app->delete('{collection}/{itemId}', function (Application $app, $collection, $itemId) {
    $app['koklu.recommender']->delete($itemId, $collection);
    return new Response('', 204);
});

// recommending
$app->get('{collection}', function (Application $app, Request $request, $collection) {
    $itemIds = $request->query->get('itemIds');
    $limit = $request->query->get('limit', false);
    $recommendations = $app['koklu.recommender']->recommend($itemIds, $collection, $limit);

    return $app->json($recommendations, 200);
});

return $app;
