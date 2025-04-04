<?php

use app\commands\patterns\behaviour\paymentStrategy\PaymentStrategyController;
use app\commands\patterns\structure\pizzaDecorator\PizzaDecoratorController;

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db-local.php';

$dbLocal = __DIR__ . '/db-local.php';
if (file_exists($dbLocal)) {
    $db = require $dbLocal;
}

$paramsLocal = __DIR__ . '/params-local.php';
if (file_exists($paramsLocal)) {
    $params = require $paramsLocal;
}

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
    ],
    'params' => $params,

    'controllerMap' => [
        'payment-strategy' => PaymentStrategyController::class,
        'pizza-decorator' => PizzaDecoratorController::class,
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],

];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
