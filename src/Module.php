<?php
/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/26/16
 * Time: 10:46 AM
 */
namespace navatech\backup;

use Yii;
use yii\mail\BaseMailer;

class Module extends \navatech\base\Module {

	public $controllerNamespace = 'navatech\backup\controllers';

	public $backupPath          = '@runtime/backup';

	public $ftp                 = [
		'enable' => true,
		'config' => [
			'directory' => '',
			'host'      => '',
			'port'      => '',
			'login'     => '',
			'password'  => '',
		],
	];

	public $mail                = [
		'enable' => true,
		'config' => [
			'fromEmail' => '',
			'toEmail'   => '',
		],
	];

	public $db                  = [
		'enable' => true,
		'config' => [
			'db',
		],
	];

	public $folder              = [
		'enable' => true,
		'config' => [
			'@app/web/uploads',
		],
	];

	/**
	 * {@inheritDoc}
	 */
	public function init() {
		parent::init();
		$this->backupPath = Yii::getAlias($this->backupPath);
		if (!file_exists($this->backupPath)) {
			mkdir($this->backupPath, 0777, true);
		}
	}

	/**
	 * @param $sqlFile
	 *
	 * @return bool
	 */
	public static function sendEmail($sqlFile) {
		/**@var Module $module */
		$module = Yii::$app->module;
		/**@var BaseMailer $mailer */
		$mailer           = Yii::$app->mailer;
		$mailer->viewPath = '@vendor/navatech/yii2-backup/src/views/mail';
		return $mailer->compose('backup')
			->setFrom([$module->mail->config->fromEmail => Yii::$app->name])
			->setTo($module->mail->config->toEmail)
			->setSubject(Yii::$app->name)
			->attach($sqlFile)
			->send();
	}
}