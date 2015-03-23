<?php
$params = array_merge(
	require(__DIR__ . '/../../common/config/params.php'),
	require(__DIR__ . '/../../common/config/params-local.php'),
	require(__DIR__ . '/params.php'),
	require(__DIR__ . '/params-local.php')
);

return [
	'id' => 'app-backend',
	'basePath' => dirname(__DIR__),
	'controllerNamespace' => 'backend\controllers',
	'bootstrap' => ['log'],
	'modules' => [],
	'components' => [
		'user' => [
			'identityClass' => 'common\models\AppUser',
			'enableAutoLogin' => true,
			'enableSession' => false,
			'loginUrl' => null,
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
		'db' => require(__DIR__ . '/db.php'),
		'errorHandler' => [
			'errorAction' => 'site/error',
		],
		'request' => [
			'parsers' => [
				'application/json' => 'yii\web\JsonParser',
			]
		],
		'urlManager' => [
			'enablePrettyUrl' => true,
			'enableStrictParsing' => true,
			'showScriptName' => false,
			// 'suffix' => '.html',
			'rules' => [
				[
					'class' => 'yii\rest\UrlRule', 
					'controller' => 'app-user',
					'tokens' => ['{id}' => '<id:\\d(\w*(-\w*)*)*>'],
					'extraPatterns' => [
						'PUT inactivate/{id}' => 'inactivate',
						'PUT allow-device/{id}' => 'allow-device',
						'PUT filter' => 'filter',
						'POST login' => 'login',
						'POST synchronize/{id}' => 'synchronize',
						'GET logout/{id}' => 'logout',
						'GET user/{id}' => 'user',
						'GET devices/{id}' => 'devices',
                                                'GET register-admin' => 'register-admin',
					],
				],
				[
					'class' => 'yii\rest\UrlRule', 
					'controller' => 'alarm',
					'tokens' => ['{id}' => '<id:\\d(\w*(-\w*)*)*>'],
				],
				[
					'class' => 'yii\rest\UrlRule', 
					'controller' => 'chat',
					'tokens' => ['{id}' => '<id:\\d(\w*(-\w*)*)*>'],
					'extraPatterns' => [
						'POST create-group' => 'create-group',
						'POST add-users' => 'add-users',
						'POST individual' => 'individual',
						'GET groups' => 'groups',
						'GET group/{id}' => 'group',
						'GET globals' => 'globals',
						'GET global/{id}' => 'global',
						'GET get-last-messages/{id}' => 'get-last-messages',
					],
				],
				[
					'class' => 'yii\rest\UrlRule', 
					'controller' => 'meeting',
					'tokens' => ['{id}' => '<id:\\d(\w*(-\w*)*)*>'],
					'extraPatterns' => [
						'GET supervisors/{id}' => 'supervisors',
						'GET supervisor/{id}' => 'supervisor',
					],
				],
				[
					'class' => 'yii\rest\UrlRule', 
					'controller' => 'location',
					'extraPatterns' => [
						'GET states' => 'states',
						'GET towns/{id}' => 'towns',
						'GET settlings/{id}' => 'settlings',
					],
				],
				[
					'class' => 'yii\rest\UrlRule', 
					'controller' => 'meeting-supervisor',
					'tokens' => ['{id}' => '<id:\\d(\w*(-\w*)*)*>'],
				],
				'<controller:(\w+-*\w+)*>/<action:(\w+-*\w+)*>/<id:(\w*(-\w*)*)*>' => '<controller>/<action>',
				'<controller:(\w+-*\w+)*>/<action:(\w+-*\w+)*>' => '<controller>/<action>',
			],
		],
	],
	'params' => $params,
];
