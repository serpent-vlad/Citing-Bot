<?php

$params = array_merge(
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);
$db = require __DIR__ . '/db.php';

$config = [
    'id'                  => 'citing-bot',
    'basePath'            => dirname(__DIR__),
    'bootstrap'           => ['log'],
    'version'             => '1.4.5',
    'name'                => 'Citing Bot',
    'controllerNamespace' => 'app\commands',
    'aliases'             => [
        '@models' => '@app/models',
    ],
    'components'          => [
        'cache'  => [
            'class' => 'yii\caching\FileCache',
        ],
        'mailer' => [
            'class'         => 'yii\swiftmailer\Mailer',
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from'    => 'tools.citing-bot@wmflabs.org',
            ],
        ],
        'log'    => [
            'targets' => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class'   => 'yii\log\EmailTarget',
                    'levels'  => ['error', 'warning'],
                    'logVars' => [],
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
        'db'     => $db,
    ],
    'params'              => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
