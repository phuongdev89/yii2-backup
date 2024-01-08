<?php
/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/26/16
 * Time: 10:14 PM
 */

namespace phuongdev89\backup\transports;

use phuongdev89\backup\models\BackupConfig;
use phuongdev89\email\models\EmailTemplate;
use phuongdev89\email\Module;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\mail\BaseMailer;
use yii\swiftmailer\Message;

/**
 *
 * @property-write mixed $file
 * @property-write mixed $type
 */
class MailTransport extends BaseObject
{

    public $viewPath = '@vendor/phuongdev89/yii2-backup/src/views/mail';

    /**@var EmailTemplate|Message */
    private $message;

    private $mailerParams = [];

    /**
     * @param array $config
     *
     * @throws InvalidConfigException
     */
    public function __construct($config = [])
    {
        parent::__construct(null);
        unset($config['class']);
        foreach ($config as $key => $value) {
            if ($this->hasProperty($key)) {
                $this->$key = $value;
            }
        }
        /**@var BaseMailer $mailer */
        $mailer = Yii::$app->mailer;
        if (Yii::$app->hasModule('mailer') && Yii::$app->getModule('mailer') instanceof Module) {
            $email = EmailTemplate::findByShortcut('backup');
            $mailer->viewPath = false;
            $this->mailerParams['to'] = BackupConfig::getConfig('email_to');
            $this->message = $email;
        } else {
            $mailer->viewPath = $this->viewPath;
            $this->message = $mailer->compose('backup')->setFrom([BackupConfig::getConfig('email_from') => Yii::$app->name])->setTo(BackupConfig::getConfig('email_to'));
        }
    }

    /**
     * @return bool
     * @throws InvalidConfigException
     */
    public function send()
    {
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
    public function setFile($file)
    {
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
    public function setType($type)
    {
        if ($this->mailerParams == null) {
            $this->message->setSubject(Yii::$app->name . ' | Backup ' . $type);
        }
        return $this;
    }
}
