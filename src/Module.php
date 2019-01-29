<?php
/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/26/16
 * Time: 10:46 AM
 */

namespace navatech\backup;

use navatech\backup\helpers\FileHelper;
use navatech\backup\models\BackupConfig;
use navatech\backup\transports\Ftp;
use navatech\backup\transports\Mail;
use yii\console\Application as ConsoleApplication;
use yii\web\NotFoundHttpException;

/**
 * @property Mail $mail
 * @property Ftp  $ftp
 */
class Module extends \navatech\base\Module {

	const TYPE_DATABASE  = 'database';

	const TYPE_DIRECTORY = 'directory';

	public $defaultRoute = 'history/index';

	public $transport    = [
		'mail' => [
			'class' => '\navatech\backup\transports\Mail',
		],
		'ftp'  => [
			'class' => '\navatech\backup\transports\Ftp',
		],
		's3'   => [
			'class' => '\navatech\backup\transports\S3',
		],
	];

	public $databases    = [];

	public $directories  = [];

	/**
	 * {@inheritDoc}
	 * @throws NotFoundHttpException
	 */
	public function init() {
		parent::init();
		if (\Yii::$app instanceof ConsoleApplication) {
			$this->controllerNamespace = 'navatech\backup\commands';
		} else {
			$this->controllerNamespace = 'navatech\backup\controllers';
			$this->defaultRoute        = 'history/index';
		}
		if (BackupConfig::getCronjob('backupPath') !== false && !file_exists(BackupConfig::getCronjob('backupPath'))) {
			mkdir(BackupConfig::getCronjob('backupPath'), 0777, true);
		}
		if (isset($this->transport['s3']) && !class_exists(\S3::class)) {
			throw new NotFoundHttpException('S3 does not exist. Please add to composer.json <code>"tpyo/amazon-s3-php-class" : "@dev"</code>');
		}
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
		if (BackupConfig::getCronjob('backupPath') !== false) {
			$list = FileHelper::findFiles(BackupConfig::getCronjob('backupPath'));
			foreach ($list as $id => $filename) {
				if (filectime($filename) < strtotime(BackupConfig::getCronjob('cleanAfterDays') . ' days ago')) {
					FileHelper::unlink($filename);
				}
			}
		}
	}
}
