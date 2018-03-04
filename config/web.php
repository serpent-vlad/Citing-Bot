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
    'version'    => '1.2.0-alpha',
    'name'       => 'Citing Bot',
    'aliases'    => [
        '@bower'  => '@vendor/bower-asset',
        '@npm'    => '@vendor/npm-asset',
        '@models' => '@app/models',
    ],
    'components' => [
        'request'      => [
            'baseUrl'             => 'https://tools.wmflabs.org/citing-bot',
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
            'messageConfig'    => [
                'charset' => 'UTF-8',
                'from'    => 'tools.citing-bot@wmflabs.org',
            ],
        ],
        'log'          => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class'   => 'yii\log\EmailTarget',
                    'levels'  => ['error', 'warning'],
                    'logVars' => ['_GET'],
                    'message' => [
                        'from'    => ['tools.citing-bot@wmflabs.org'],
                        'to'      => ['kekaadrenalin@tools.wmflabs.org'],
                        'subject' => 'Ошибка citing-bot',
                    ],
                    'except'  => [
                        'yii\web\HttpException:404',
                        'yii\db\*',
                    ],
                ],
            ],
        ],
        'db'           => $db,
        'urlManager'   => [
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
            'rules'           => [
                'api/pmid/<pmid:\d+>' => 'api/pmid',
                'api/doi/<doi:.+>' => 'api/doi',
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
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
