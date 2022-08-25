<?php
declare(strict_types = 1);

namespace cusodede\log;

use yii\log\FileTarget as YiiFileTarget;

/**
 * Class FileTarget
 */
class FileTarget extends YiiFileTarget {

	/**
	 * {@inheritdoc}
	 */
	public function init():void {
		if (is_callable($this->logFile)) {
			$this->logFile = call_user_func($this->logFile);
		}

		parent::init();
	}

}