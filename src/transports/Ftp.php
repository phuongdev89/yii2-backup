<?php
/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/26/16
 * Time: 10:17 PM
 */

namespace navatech\backup\transports;

use Yii;
use yii\base\BaseObject;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\web\Application;
use yii2mod\ftp\FtpClient;

class Ftp extends BaseObject {

	public  $enable     = false;

	public  $host;

	public  $port;

	public  $user;

	public  $pass;

	public  $ssl        = false;

	public  $dir;

	public  $timeOut    = 90;

	public  $appendTime = true;

	private $client;

	private $file;

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
		if ($this->enable) {
			try {
				$this->client = new FtpClient();
				$this->client->connect($this->host, $this->ssl, $this->port, $this->timeOut);
				$this->client->pasv(true);
				$this->client->login($this->user, $this->pass);
				if ($this->appendTime) {
					$this->dir .= DIRECTORY_SEPARATOR . date('Y-m-d');
				}
				if (!$this->client->isDir($this->dir)) {
					$this->client->mkdir($this->dir);
				}
				$this->client->chdir($this->dir);
			} catch (ErrorException $e) {
				echo "Can not create folder. Make sure folder is existed" . PHP_EOL;
			}
		}
	}

	/**
	 * @param string $file
	 *
	 * @return $this
	 */
	public function setFile($file) {
		$this->file = $file;
		return $this;
	}

	/**
	 * @throws Exception
	 * @return bool
	 */
	public function push() {
		$hasError  = true;
		$tryCount  = 0;
		$lastError = '';
		while ($hasError && $tryCount != 9) {
			try {
				$this->client->fput(basename($this->file), fopen($this->file, 'r'), FTP_BINARY);
				$ftpFileSize  = $this->client->size(basename($this->file));
				$realFileSize = filesize($this->file);
				if ($ftpFileSize !== $realFileSize) {
					$lastError = 'Size of file ' . basename($this->file) . ' is ' . $ftpFileSize . ' but real is ' . $realFileSize;
					$tryCount ++;
				} else {
					$hasError = false;
				}
			} catch (Exception $e) {
				$tryCount ++;
				$lastError = mb_convert_encoding($e->getMessage(), 'utf8', 'cp1251');
			}
		}
		if ($hasError && Yii::$app instanceof Application) {
			throw new Exception($lastError);
		}
		return !$hasError;
	}
}
