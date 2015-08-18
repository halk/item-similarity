<?php

require_once __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '../config/config.php';

$app = new Silex\Application();
$app['debug'] = $config['debug'];

// logging
$app->register(new Silex\Provider\MonologServiceProvider(), [
    'monolog.logfile' => __DIR__ . '/../log/itemSimilarity.log'
]);

// Doctrine MongoDB ODM

use Neutron\Silex\Provider\MongoDBODMServiceProvider;

$app->register(new MongoDBODMServiceProvider(), [
    'doctrine.odm.mongodb.connection_options' => [
        'database' => 'ITEM_SIMILARITY',
        'host' => $config['mongo-dsn']
    ],
    'doctrine.odm.mongodb.documents' => [
        [
            'type'      => 'annotation',
            'path'      => [__DIR__ . '/Koklu/Entity'],
            'namespace' => 'Koklu\Entity',
            'alias'     => 'docs'
        ]
    ]
]);

// Recommender service

use Koklu\Service\RecommenderService;

$app['koklu.recommender'] = $app->share(function() use ($app) {
    return new RecommenderService($app['doctrine.odm.mongodb.dm']);
});
