<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$dbLocal = __DIR__ . '/db-local.php';
if (file_exists($dbLocal)) {
    $db = require $dbLocal;
}

$paramsLocal = __DIR__ . '/params-local.php';
if (file_exists($paramsLocal)) {
    $params = require $paramsLocal;
}

$config = [
    'id' => 'basic',
    'language' => 'ru',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'modules' => [
        'shop' => [
            'class' => 'app\modules\shop\Module',
        ],
    ],
    'components' => [

        'elasticsearch' => [
            'class' => 'yii\elasticsearch\Connection',
            'nodes' => [
                ['http_address' => '127.0.0.1:9200'],
                //настройте несколько хостов, если у вас есть кластер
            ],
            // установите autodetectCluster = false, чтобы не определять адреса узлов в кластере автоматически
            // 'autodetectCluster' => false,
            'dslVersion' => 7, // default is 5
        ],

        'request' => [
            'cookieValidationKey' => 'qPOGnNCZjuFkEmsxlRfZJDnReyBC_hgs',
            'baseUrl' => '',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => '/auth/login',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.yandex.ru',
                'username' => 'WhiteRomka@yandex.ru',
                'password' => '?',
                'port' => '465',
                'encryption' => 'ssl',
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                '/' => 'user/filter',
                't/t' => 't/t',
                't' => 't/index',
                'shop/category/<catId:\d+>' => 'shop/category/category',
                # REST API
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'api/auth',
                        'api/user',
                    ],
                    'prefix' => 'api',
                ],
                # Получение токенов
                'POST api/auth/login' => 'api/auth/login',
                'POST api/auth/logout' => 'api/auth/logout',
                # Пользователи
                'GET api/user' => 'api/user/index', // ?page=10&per-page=100
                'GET api/user/<id:\d+>' => 'api/user/view',
                'POST api/user/create' => 'api/user/create',
                'PUT api/user/update/<id:\d+>' =>'api/user/update',
                'PATCH api/user/update/<id:\d+>' =>'api/user/update',
                'DELETE api/user/delete/<id:\d+>' => 'api/user/delete',
                'OPTIONS api/user/options' => 'api/user/options',
                # Крипта
                'GET api/altcoin' => 'api/altcoin/index',
                'GET api/altcoin/<id:\d+>' => 'api/altcoin/view',
                'POST api/altcoin/create' => 'api/altcoin/create',
                'PUT api/altcoin/update/<id:\d+>' =>'api/altcoin/update',
                'DELETE api/altcoin/delete/<id:\d+>' => 'api/altcoin/delete',
            ],
        ],

    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
