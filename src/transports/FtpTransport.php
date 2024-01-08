<?php
/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/26/16
 * Time: 10:17 PM
 */

namespace phuongdev89\backup\transports;

use phuongdev89\backup\models\BackupConfig;
use Yii;
use yii\base\BaseObject;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\web\Application;
use yii2mod\ftp\FtpClient;
use yii2mod\ftp\FtpException;

class FtpTransport extends BaseObject
{

    public $timeOut = 90;

    public $appendTime = true;

    private $client;

    private $file;

    /**
     * @param array $config
     *
     * @throws FtpException
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
        try {
            $this->client = new FtpClient();
            $this->client->connect(BackupConfig::getConfig('ftp_host'), BackupConfig::getConfig('ftp_port') == 22, BackupConfig::getConfig('ftp_port'), $this->timeOut);
            $this->client->pasv(true);
            $this->client->login(BackupConfig::getConfig('ftp_user'), BackupConfig::getConfig('ftp_pass'));
            $directory = BackupConfig::getConfig('ftp_directory');
            if ($this->appendTime) {
                $directory .= (substr($directory, -1) != '/' ? DIRECTORY_SEPARATOR : '') . date('Y-m-d');
            }
            if (!$this->client->isDir($directory)) {
                $this->client->mkdir($directory);
            }
            $this->client->chdir($directory);
        } catch (ErrorException $e) {
            echo "Can not create folder. Make sure folder is existed" . PHP_EOL;
        }
    }

    /**
     * @param string $file
     *
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function push()
    {
        $hasError = true;
        $tryCount = 0;
        $lastError = '';
        while ($hasError && $tryCount != 9) {
            try {
                $this->client->fput(basename($this->file), fopen($this->file, 'r'), FTP_BINARY);
                $ftpFileSize = $this->client->size(basename($this->file));
                $realFileSize = filesize($this->file);
                if ($ftpFileSize !== $realFileSize) {
                    $lastError = 'Size of file ' . basename($this->file) . ' is ' . $ftpFileSize . ' but real is ' . $realFileSize;
                    $tryCount++;
                } else {
                    $hasError = false;
                }
            } catch (Exception $e) {
                $tryCount++;
                $lastError = mb_convert_encoding($e->getMessage(), 'utf8', 'cp1251');
            }
        }
        if ($hasError && Yii::$app instanceof Application) {
            throw new Exception($lastError);
        }
        return !$hasError;
    }
}
