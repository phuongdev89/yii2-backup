<?php
/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/26/16
 * Time: 10:46 AM
 */

namespace phuongdev89\backup;

use bpsys\yii2\aws\s3\Service;
use phuongdev89\backup\helpers\FileHelper;
use phuongdev89\backup\models\BackupConfig;
use phuongdev89\backup\transports\FtpTransport;
use phuongdev89\backup\transports\MailTransport;
use phuongdev89\backup\transports\S3Transport;
use Yii;
use yii\base\ErrorException;
use yii\console\Application as ConsoleApplication;
use yii\web\NotFoundHttpException;

/**
 * @property MailTransport $mailTransport
 * @property FtpTransport $ftpTransport
 * @property S3Transport $s3Transport
 */
class Module extends \phuongdev89\base\Module
{

    const TYPE_DATABASE = 'database';

    const TYPE_DIRECTORY = 'directory';

    public $defaultRoute = 'history/index';

    public $transport = [
        'mail' => [
            'class' => '\phuongdev89\backup\transports\MailTransport',
        ],
        'ftp' => [
            'class' => '\phuongdev89\backup\transports\FtpTransport',
        ],
        's3' => [
            'class' => '\phuongdev89\backup\transports\S3Transport',
        ],
    ];

    public $databases = [];

    public $directories = [];

    /**
     * {@inheritDoc}
     * @throws NotFoundHttpException
     */
    public function init()
    {
        parent::init();
        if (Yii::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'phuongdev89\backup\commands';
        } else {
            $this->controllerNamespace = 'phuongdev89\backup\controllers';
            $this->defaultRoute = 'history/index';
        }
        if (BackupConfig::getCronjob('backupPath') !== false && !file_exists(BackupConfig::getCronjob('backupPath'))) {
            mkdir(BackupConfig::getCronjob('backupPath'), 0777, true);
        }
        if (isset($this->transport['s3']) && !class_exists(Service::class)) {
            throw new NotFoundHttpException('S3 does not exist. Please add to composer.json <code>"bp-sys/yii2-aws-s3" : "@dev"</code>');
        }
    }

    /**
     * @return MailTransport
     */
    public function getMailTransport()
    {
        $mailClass = $this->transport['mail']['class'];
        return new $mailClass($this->transport['mail']);
    }

    /**
     * @return FtpTransport
     */
    public function getFtpTransport()
    {
        $ftpClass = $this->transport['ftp']['class'];
        return new $ftpClass($this->transport['ftp']);
    }

    /**
     * @return S3Transport
     */
    public function getS3Transport()
    {
        $s3Class = $this->transport['s3']['class'];
        return new $s3Class($this->transport['s3']);
    }

    /**
     * Clean file
     */
    public function clean()
    {
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
