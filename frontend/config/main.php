<?php
$params = array_merge(
	require(__DIR__ . '/../../common/config/params.php'),
	require(__DIR__ . '/../../common/config/params-local.php'),
	require(__DIR__ . '/params.php'),
	require(__DIR__ . '/params-local.php')
);

return [
	'id' => 'app-frontend',
	'basePath' => dirname(__DIR__),
	'bootstrap' => ['log'],
	'controllerNamespace' => 'frontend\controllers',
	'components' => [
		'user' => [
			'identityClass' => 'frontend\models\UserSession',
			'enableAutoLogin' => false,
			'enableSession' => true,
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
		'errorHandler' => [
			'errorAction' => 'site/error',
		],
		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'enableStrictParsing' => false,
			'suffix' => '.html',
			'rules' => [
				'<controller:(\w+-*\w+)*>/<action:(\w+-*\w+)*>/<id:\d+>' => '<controller>/<action>',
				'<controller:(\w+-*\w+)*>/<action:(\w+-*\w+)*>' => '<controller>/<action>',
			],
		],
		'assetManager' => [
			'bundles' => [
				'dosamigos\google\maps\MapAsset' => [
					'options' => [
						// 'key' => 'this_is_my_key',
						'language' => 'id',
						'version' => '3.1.18'
					]
				]
			]
		],
		'authClientCollection' => [
			'class' => 'yii\authclient\Collection',
			'clients' => [
				'facebook' => [
					'class' => 'yii\authclient\clients\Facebook',
					'clientId' => '337486879780717',
					'clientSecret' => '041eb5b954a06c585853149a64ac2ddc',
				],
				'twitter' => [
					'class' => 'yii\authclient\clients\Twitter',
					'consumerKey' => 'G8T0msuiVJhqROfI3RZxdT92A',
					'consumerSecret' => 'wov8ezV2KRXrvCPfLlcRBNPKHIrgFtN46lHScZItomSUqheT50',
				],
			],
		],
	],
	'params' => $params,
];
