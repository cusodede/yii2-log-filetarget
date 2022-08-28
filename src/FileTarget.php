<?php /** @noinspection PhpUsageOfSilenceOperatorInspection */
declare(strict_types = 1);

namespace cusodede\log;

use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\log\FileTarget as YiiFileTarget;
use yii\log\LogRuntimeException;

/**
 * Class FileTarget
 */
class FileTarget extends YiiFileTarget {

	/**
	 * @var callable|string|null log file path or [path alias](guide:concept-aliases). If not set, it will use the "@runtime/logs/app.log" file.
	 * The directory containing the log files will be automatically created if not existing.
	 */
	public $logFile;

	/**
	 * Initializes the route.
	 * This method is invoked after the route is created by the route manager.
	 */
	public function init():void {
		if (null === $this->logFile) {
			$this->logFile = Yii::$app->getRuntimePath().'/logs/app.log';
		} elseif (is_string($this->logFile)) {
			$this->logFile = Yii::getAlias($this->logFile);
		}
		if ($this->maxLogFiles < 1) {
			$this->maxLogFiles = 1;
		}
		if ($this->maxFileSize < 1) {
			$this->maxFileSize = 1;
		}
	}

	/**
	 * Writes log messages to a file.
	 * Starting from version 2.0.14, this method throws LogRuntimeException in case the log can not be exported.
	 * @throws InvalidConfigException if unable to open the log file for writing
	 * @throws LogRuntimeException if unable to write complete log to file
	 * @throws Exception
	 */
	public function export():void {
		$logFile = Yii::getAlias((is_callable($this->logFile))?call_user_func($this->logFile):$this->logFile);

		if (false === strpos($logFile, '://') || 0 === strncmp($logFile, 'file://', 7)) {
			$logPath = dirname($logFile);
			FileHelper::createDirectory($logPath, $this->dirMode);
		}

		$text = implode("\n", array_map([$this, 'formatMessage'], $this->messages))."\n";
		if (false === ($fp = @fopen($logFile, 'ab'))) {
			throw new InvalidConfigException("Unable to append to log file: {$logFile}");
		}
		@flock($fp, LOCK_EX);
		if ($this->enableRotation) {
			// clear stat cache to ensure getting the real current file size and not a cached one
			// this may result in rotating twice when cached file size is used on subsequent calls
			clearstatcache();
		}
		if ($this->enableRotation && @filesize($logFile) > $this->maxFileSize * 1024) {
			$this->rotateFiles();
		}
		$writeResult = @fwrite($fp, $text);
		if (false === $writeResult) {
			$error = error_get_last();
			throw new LogRuntimeException("Unable to export log through file ({$logFile})!: {$error['message']}");
		}
		$textSize = strlen($text);
		if ($writeResult < $textSize) {
			throw new LogRuntimeException("Unable to export whole log through file ({$logFile})! Wrote $writeResult out of $textSize bytes.");
		}
		@fflush($fp);
		@flock($fp, LOCK_UN);
		@fclose($fp);

		if (null !== $this->fileMode) {
			@chmod($logFile, $this->fileMode);
		}
	}

	/**
	 * Rotates log files.
	 */
	protected function rotateFiles():void {
		$file = Yii::getAlias((is_callable($this->logFile))?call_user_func($this->logFile):$this->logFile);
		for ($i = $this->maxLogFiles; $i >= 0; --$i) {
			// $i == 0 is the original log file
			$rotateFile = $file.(0 === $i?'':'.'.$i);
			if (is_file($rotateFile)) {
				// suppress errors because it's possible multiple processes enter into this section
				if ($i === $this->maxLogFiles) {
					@unlink($rotateFile);
					continue;
				}
				$newFile = $file.'.'.($i + 1);
				$this->rotateByCopy($rotateFile, $newFile);
				if (0 === $i) {
					$this->clearLogFile($rotateFile);
				}
			}
		}
	}

	/***
	 * Clear log file without closing any other process open handles
	 * @param string $rotateFile
	 */
	private function clearLogFile(string $rotateFile):void {
		if ($filePointer = @fopen($rotateFile, 'ab')) {
			@ftruncate($filePointer, 0);
			@fclose($filePointer);
		}
	}

	/***
	 * Copy rotated file into new file
	 * @param string $rotateFile
	 * @param string $newFile
	 */
	private function rotateByCopy(string $rotateFile, string $newFile):void {
		@copy($rotateFile, $newFile);
		if (null !== $this->fileMode) {
			@chmod($newFile, $this->fileMode);
		}
	}

}