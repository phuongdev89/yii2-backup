<?php
/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/26/16
 * Time: 10:46 AM
 */

namespace phuongdev89\backup;

use phuongdev89\backup\helpers\FileHelper;
use phuongdev89\backup\models\BackupConfig;
use phuongdev89\backup\transports\Ftp;
use phuongdev89\backup\transports\Mail;
use yii\base\ErrorException;
use yii\console\Application as ConsoleApplication;
use yii\web\NotFoundHttpException;

/**
 * @property Mail $mail
 * @property Ftp  $ftp
 */
class Module extends \phuongdev89\base\Module {

	const TYPE_DATABASE  = 'database';

	const TYPE_DIRECTORY = 'directory';

	public $defaultRoute = 'history/index';

	public $transport    = [
		'mail' => [
			'class' => '\phuongdev89\backup\transports\Mail',
		],
		'ftp'  => [
			'class' => '\phuongdev89\backup\transports\Ftp',
		],
		's3'   => [
			'class' => '\phuongdev89\backup\transports\S3',
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
			$this->controllerNamespace = 'phuongdev89\backup\commands';
		} else {
			$this->controllerNamespace = 'phuongdev89\backup\controllers';
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
					try {
						FileHelper::unlink($filename);
					} catch (ErrorException $e) {
						continue;
					}
				}
			}
		}
	}
}
