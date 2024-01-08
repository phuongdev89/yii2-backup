<?php
/**
 * Created by phuongdev89.
 * @project yii2-backup
 * @author  Phuong
 * @email   phuongdev89[at]gmail.com
 * @date    1/23/2019
 * @time    9:23 AM
 */

namespace phuongdev89\backup\transports;

use Aws\Credentials\Credentials;
use Aws\ResultInterface;
use bpsys\yii2\aws\s3\Service;
use GuzzleHttp\Promise\PromiseInterface;
use phuongdev89\backup\models\BackupConfig;

class S3Transport extends Service
{
    /**@var string */
    public $file;

    /**@var string */
    public $path;

    /**
     * @param $config
     */
    public function __construct($config = [])
    {
        $credential = new Credentials(BackupConfig::getConfig('s3_access_key'), BackupConfig::getConfig('s3_secret_key'));
        $this->setCredentials($credential);
        $this->defaultBucket = BackupConfig::getConfig('s3_bucket');
        $this->setRegion(BackupConfig::getConfig('s3_region'));
        $this->path = BackupConfig::getConfig('s3_path');
        $this->defaultAcl = 'public-read';
        $this->defaultPresignedExpiration = '+1 hour';
        $this->endpoint = BackupConfig::getConfig('s3_endpoint');
        foreach ($config as $key => $value) {
            if ($this->hasProperty($key)) {
                $this->$key = $value;
            }
        }

        parent::__construct(null);
    }

    /**
     * @param $file
     * @return S3Transport
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return ResultInterface|PromiseInterface
     */
    public function push()
    {
        return $this->commands()->upload($this->path . DIRECTORY_SEPARATOR . basename($this->file), $this->file)->withAcl($this->defaultAcl)->execute();
    }
}
