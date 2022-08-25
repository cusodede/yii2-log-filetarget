<?php
declare(strict_types = 1);

use Codeception\Test\Unit;
use cusodede\log\FileTarget;
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
		$timestamp = time();
		Yii::getLogger()->log(self::TEST_MESSAGE.'@'.$timestamp, Logger::LEVEL_ERROR, 'tests');
		Yii::getLogger()->flush();
		$logFile = Yii::getAlias('@app/runtime/logs/'.date('YmdH', $timestamp).'/ot-'.date('YmdHi', $timestamp).'.log');
		self::assertFileExists($logFile);
		/** @var array $logContents */
		self::assertStringEqualsFile(self::TEST_MESSAGE.'@'.$timestamp, $logFile);
	}
}