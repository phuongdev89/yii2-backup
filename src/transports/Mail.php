<?php
/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/26/16
 * Time: 10:14 PM
 */

namespace navatech\backup\transports;

use navatech\backup\Module;
use navatech\email\models\EmailTemplate;
use Yii;
use yii\base\BaseObject;
use yii\mail\BaseMailer;
use yii\swiftmailer\Message;

class Mail extends BaseObject {

	public $enable    = false;

	public $fromEmail = 'support@gmail.com';

	public $toEmail   = 'backup@gmail.com';

	public $viewPath  = '@vendor/navatech/yii2-backup/src/views/mail';

	/**@var Message */
	private $message;

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
		if (Module::hasSetting()) {
			$this->enable    = Yii::$app->setting->get('backup_email_enable', 1) == 1;
			$this->fromEmail = Yii::$app->setting->get('backup_email_from', 'support@gmail.com');
			$this->toEmail   = Yii::$app->setting->get('backup_email_to', 'backup@gmail.com');
		}
		/**@var BaseMailer $mailer */
		$mailer = Yii::$app->mailer;
		if (Yii::$app->hasModule('mailer') && Yii::$app->getModule('mailer') instanceof \navatech\email\Module) {
			$email            = EmailTemplate::findByShortcut('backup');
			$email->from      = $this->fromEmail;
			$mailer->viewPath = false;
			$this->message    = $mailer->compose($email->renderAttribute('text', []))->setFrom([$this->fromEmail => Yii::$app->name])->setTo($this->toEmail);
		} else {
			$mailer->viewPath = $this->viewPath;
			$this->message    = $mailer->compose('backup')->setFrom([$this->fromEmail => Yii::$app->name])->setTo($this->toEmail);
		}
	}

	/**
	 * @return bool
	 */
	public function send() {
		return $this->message->send();
	}

	/**
	 * @param mixed $file
	 *
	 * @return $this
	 */
	public function setFile($file) {
		$this->message->attach($file);
		return $this;
	}

	/**
	 * @param mixed $type
	 *
	 * @return $this
	 */
	public function setType($type) {
		$this->message->setSubject(Yii::$app->name . ' | Backup ' . $type);
		return $this;
	}
}
