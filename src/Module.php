<?php
/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/26/16
 * Time: 10:46 AM
 */

namespace navatech\backup;

use navatech\backup\helpers\FileHelper;
use navatech\backup\transports\Ftp;
use navatech\backup\transports\Mail;
use Yii;
use yii\console\Application as ConsoleApplication;
use yii\helpers\ArrayHelper;

/**
 * @property Mail $mail
 * @property Ftp  $ftp
 */
class Module extends \navatech\base\Module {

	const TYPE_DATABASE  = 'database';

	const TYPE_DIRECTORY = 'directory';

	public $defaultRoute  = 'default/index';

	public $backupPath    = '@runtime/backup';

	public $transport     = [];

	public $backup        = [];

	public $clearAfterDay = 3;

	/**
	 * {@inheritDoc}
	 */
	public function init() {
		parent::init();
		if (\Yii::$app instanceof ConsoleApplication) {
			$this->controllerNamespace = 'navatech\backup\commands';
		} else {
			$this->controllerNamespace = 'navatech\backup\controllers';
			$this->defaultRoute        = 'default/index';
		}
		$this->backupPath = Yii::getAlias($this->backupPath);
		if (!file_exists($this->backupPath)) {
			mkdir($this->backupPath, 0777, true);
		}
		$this->backup    = ArrayHelper::merge([
			'database'  => [
				'enable' => true,
				'data'   => [
					'db',
				],
			],
			'directory' => [
				'enable' => false,
				'data'   => [],
			],
		], $this->backup);
		$this->transport = ArrayHelper::merge([
			'mail' => [
				'class' => '\navatech\backup\transports\Mail',
			],
			'ftp'  => [
				'class' => '\navatech\backup\transports\Ftp',
			],
		], $this->transport);
	}

	/**
	 * @return bool
	 */
	public function isDatabaseBackupEnable() {
		return $this->backup[self::TYPE_DATABASE]['enable'];
	}

	/**
	 * @return array
	 */
	public function backupDatabaseData() {
		return array_unique($this->backup[self::TYPE_DATABASE]['data']);
	}

	/**
	 * @return bool
	 */
	public function isDirectoryBackupEnable() {
		return $this->backup[self::TYPE_DIRECTORY]['enable'];
	}

	/**
	 * @return array
	 */
	public function backupDirectoryData() {
		return array_unique($this->backup[self::TYPE_DIRECTORY]['data']);
	}

	/**
	 * @return Mail
	 */
	public function getMail() {
		$mailClass = $this->transport['mail']['class'];
		return new $mailClass($this->transport['mail']);
	}

	/**
	 * @return Ftp
	 */
	public function getFtp() {
		$ftpClass = $this->transport['ftp']['class'];
		return new $ftpClass($this->transport['ftp']);
	}

	/**
	 * Clean file
	 */
	public function clean() {
		$list = FileHelper::findFiles($this->backupPath);
		foreach ($list as $id => $filename) {
			if (filectime($filename) < strtotime($this->clearAfterDay . ' days ago')) {
				FileHelper::unlink($filename);
			}
		}
	}
}
