<?php

$params = array_merge(
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);
$db = require __DIR__ . '/db.php';

$config = [
    'id'         => 'citing-bot',
    'basePath'   => dirname(__DIR__),
    'bootstrap'  => ['log'],
    'version'    => '1.1.1-alpha',
    'name'       => 'Citing Bot',
    'aliases'    => [
        '@bower'  => '@vendor/bower-asset',
        '@npm'    => '@vendor/npm-asset',
        '@models' => '@app/models',
    ],
    'components' => [
        'request'      => [
            'baseUrl'             => 'citing-bot/',
            'cookieValidationKey' => 'ugIMIWZ-4xkQMbxRlVNX68NjBjF_-sbe',
        ],
        'cache'        => [
            'class' => 'yii\caching\FileCache',
        ],
        'user'         => [
            'identityClass'   => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer'       => [
            'class'            => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
        ],
        'log'          => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db'           => $db,
        'urlManager'   => [
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
            'rules'           => [
                'api/pmid/<pmid:\d+>' => 'api/pmid',
            ],
        ],
    ],
    'params'     => $params,
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
