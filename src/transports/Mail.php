<?php
/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/26/16
 * Time: 10:14 PM
 */
namespace navatech\backup\transports;

use Yii;
use yii\mail\BaseMailer;
use yii\swiftmailer\Message;

class Mail extends Base {

	public $fromEmail = 'support@gmail.com';

	public $toEmail   = 'backup@gmail.com';

	public $viewPath  = '@vendor/navatech/yii2-backup/src/views/mail';

	/**@var Message $message */
	private $message;

	/**
	 * @param array $config
	 */
	public function __construct($config = []) {
		parent::__construct(null);
		foreach ($config as $key => $value) {
			if ($this->hasProperty($key)) {
				$this->$key = $value;
			}
		}
		/**@var BaseMailer $mailer */
		$mailer           = Yii::$app->mailer;
		$mailer->viewPath = $this->viewPath;
		$this->message    = $mailer->compose('backup')
			->setFrom([$this->fromEmail => Yii::$app->name])
			->setTo($this->toEmail);
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