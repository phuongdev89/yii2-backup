<?php
/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/26/16
 * Time: 10:14 PM
 */

namespace navatech\backup\transports;

use navatech\backup\models\BackupConfig;
use navatech\email\models\EmailTemplate;
use Yii;
use yii\base\BaseObject;
use yii\mail\BaseMailer;
use yii\swiftmailer\Message;

class Mail extends BaseObject {

	public $viewPath = '@vendor/navatech/yii2-backup/src/views/mail';

	/**@var EmailTemplate|Message */
	private $message;

	private $mailerParams = [];

	/**
	 * @param array $config
	 *
	 * @throws \yii\base\InvalidConfigException
	 */
	public function __construct($config = []) {
		parent::__construct(null);
		foreach ($config as $key => $value) {
			if ($this->hasProperty($key)) {
				$this->$key = $value;
			}
		}
		/**@var BaseMailer $mailer */
		$mailer = Yii::$app->mailer;
		if (Yii::$app->hasModule('mailer') && Yii::$app->getModule('mailer') instanceof \navatech\email\Module) {
			$email                    = EmailTemplate::findByShortcut('backup');
			$mailer->viewPath         = false;
			$this->mailerParams['to'] = BackupConfig::getTransport('email_to');
			$this->message            = $email;
		} else {
			$mailer->viewPath = $this->viewPath;
			$this->message    = $mailer->compose('backup')->setFrom([BackupConfig::getTransport('email_from') => Yii::$app->name])->setTo(BackupConfig::getTransport('email_to'));
		}
	}

	/**
	 * @return bool
	 * @throws \yii\base\InvalidConfigException
	 */
	public function send() {
		if ($this->mailerParams != null) {
			return $this->message->send($this->mailerParams['to'], [], [$this->mailerParams['file']]);
		} else {
			return $this->message->send();
		}
	}

	/**
	 * @param mixed $file
	 *
	 * @return $this
	 */
	public function setFile($file) {
		if ($this->mailerParams != null) {
			$this->mailerParams['file'] = $file;
		} else {
			$this->message->attach($file);
		}
		return $this;
	}

	/**
	 * @param mixed $type
	 *
	 * @return $this
	 */
	public function setType($type) {
		if ($this->mailerParams == null) {
			$this->message->setSubject(Yii::$app->name . ' | Backup ' . $type);
		}
		return $this;
	}
}
