yii2-helpers
============
Some useful helpers for Yii2 framework

![GitHub Workflow Status](https://img.shields.io/github/workflow/status/cusodede/yii2-log-filetarget/CI%20with%20PostgreSQL)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Run

```
php composer.phar require cusodede/yii2-log-filetarget "^1.0.0"
```

or add

```
"cusodede/yii2-log-filetarget": "^1.0.0"
```

to the require section of your `composer.json` file.

Requirements
------------

PHP >= 8.0

Usage
-----

This log target is totally like default Yii2 file target, but it can accept function as the `logFile`
parameter.

```php
[
	'components' => [
		'log' => [
			'targets' => [
				[
					'class' => cusodede\log\FileTarget\FileTarget::class,
					'logFile' => fn():string => '@app/runtime/logs/'.date('YmdH').'/ot-'.date('YmdHi').'.log',
				]
			],
		],
		...
];

```

Note that log file name will be generated, when Yii logger is flushed (see `\yii\log\Logger::flush()`), and
not when message is logged. 