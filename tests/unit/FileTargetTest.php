<?php
declare(strict_types = 1);

use Codeception\Test\Unit;
use cusodede\log\FileTarget;
use yii\helpers\FileHelper;
use yii\log\Dispatcher;
use yii\log\Logger;

/**
 * FileTargetTest class
 */
class FileTargetTest extends Unit {
	private const TEST_MESSAGE = 'This is commander Sheppard and it is my favorite test!';

	/**
	 * @return void
	 */
	public function testLogFileCallable():void {
		FileHelper::removeDirectory(Yii::getAlias('@app/runtime/logs/'));
		$timestamp = time();
		$logFile = Yii::getAlias('@app/runtime/logs/'.date('YmdH', $timestamp).'/ot-'.date('YmdHi', $timestamp).'.log');
		$logger = new Logger();
		$dispatcher = new Dispatcher([
			'logger' => $logger,
			'targets' => [
				'file' => [
					'class' => FileTarget::class,
					'logFile' => fn():string => '@app/runtime/logs/'.date('YmdH', $timestamp).'/ot-'.date('YmdHi', $timestamp).'.log',
					'levels' => [],
					'maxFileSize' => 1024, // 1 MB
					'maxLogFiles' => 1, // one file for rotation and one normal log file
					'logVars' => [],
				],
			],
		]);

		$dispatcher->logger->log(self::TEST_MESSAGE, Logger::LEVEL_WARNING);
		$dispatcher->logger->flush(true);
		clearstatcache();
		self::assertFileExists($logFile);
		/** @var array $logContents */
		$fileContent = file_get_contents($logFile);
		self::assertStringContainsString(self::TEST_MESSAGE, $fileContent);
	}

}