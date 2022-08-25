<?php /** @noinspection UsingInclusionReturnValueInspection */
declare(strict_types = 1);

use cusodede\log\FileTarget;
use yii\caching\DummyCache;

return [
	'id' => 'basic',
	'basePath' => dirname(__DIR__),
	'bootstrap' => ['log'],
	'aliases' => [
		'@vendor' => './vendor',
		'@tests' => './tests'
	],
	'components' => [
		'request' => [
			'cookieValidationKey' => 'sosijopu',
		],
		'cache' => [
			'class' => DummyCache::class,
		],
		'log' => [
			'traceLevel' => 3,
			'flushInterval' => 1,
			'targets' => [
				[
					'class' => FileTarget::class,
					'categories' => ['tests'],
					'exportInterval' => 1,//выключаю буферизацию
					'logVars' => [],
					'enableRotation' => false,
					'logFile' => fn():string => '@app/runtime/logs/ot-'.date('YmdHi').'.log'
				]
			]
		]
	],
];