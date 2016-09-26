<?php
/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/26/16
 * Time: 10:50 AM
 */
namespace navatech\backup\commands;

use navatech\backup\helpers\MysqlBackup;
use navatech\backup\Module;
use Yii;
use yii\base\Exception;
use yii\console\Controller;
use yii\db\Connection;
use yii\helpers\FileHelper;
use yii2mod\ftp\FtpClient;

/**
 * @property Module $module
 */
class IndexController extends Controller {

	/**
	 * Redefine default dump commands.
	 *
	 * Value in commands is used by defined order. Params is setted by associative values. For example param"-u user"
	 * setted by define pair "u" => "user".
	 *
	 * @var array
	 */
	public $dumpCommands = [];

	/**
	 * Forders for adding in backup
	 *
	 * @var string
	 */
	public $folders = [
		'frontend/web/files',
		'frontend/web/i',
	];

	/**
	 * Db components keys for dumps
	 *
	 * @var array
	 */
	public $dbKeys = [
		'db',
	];

	/**
	 * Host for ftp
	 *
	 * @var string
	 */
	public $ftpHost = 'localhost';

	/**
	 * User name for ftp
	 *
	 * @var string
	 */
	public $ftpLogin = 'root';

	/**
	 * Password for ftp
	 *
	 * @var string
	 */
	public $ftpPassword = 'anonymous';

	/**
	 * @var int
	 */
	public $ftpPort = 21;

	/**
	 * @var int
	 */
	public $ftpTimeout = 60;

	/**
	 * @var bool
	 */
	public $ftpSsl = false;

	/**
	 * Main backups folder on ftp
	 *
	 * @var string
	 */
	public $ftpDir = 'backup';

	/**
	 * Subfolder prefix name for ftp. Following a prefix is put date. For example: "backup_Y-m-d".
	 *
	 * @var string
	 */
	public $folderPrefix = 'backup';

	/**
	 * Admin email for errors reporting.
	 *
	 * @var string
	 */
	public $adminMail = 'root@localhost.localdomain';

	/**
	 * Backup arhive is splitted by parts for reliability copy process.
	 *
	 * @var string
	 */
	public $filePartSize = '300MiB';

	/**
	 * Cache dir name.
	 *
	 * @var string
	 */
	public    $cacheDir  = 'backups';

	protected $dumpFiles = [];

	/**
	 * Default dump commands definitions. Keys of array this name of database driver.
	 *
	 * @var array
	 */
	protected $defaultDumpCommands = [
		'mysql' => [
			'mysqldump',
			'u ' => '{user}',
			'p'  => '{password}',
			'h ' => '{host}',
			'P ' => '{port}',
			'{dbname} > {file}',
		],
		'pgsql' => [
			'PGPASSWORD="{password}" pg_dump',
			'U ' => '{user}',
			'h ' => '{host}',
			'p ' => '{port}',
			'{dbname} > {file}',
		],
	];

	public    $formUser;

	public    $siteName;

	public function init() {
		parent::init();
		$this->formUser = Yii::$app->params['supportEmail'];
		$this->siteName = Yii::$app->name;
		if (Yii::$app->has('setting')) {
			$this->formUser = Yii::$app->setting->get('smtpUser');
			$this->siteName = Yii::$app->setting->get('siteName');
			Yii::$app->set('mailer', [
				'class'     => 'yii\swiftmailer\Mailer',
				'viewPath'  => '@common/mail',
				'transport' => [
					'class'      => 'Swift_SmtpTransport',
					'host'       => Yii::$app->setting->get('smtpHost'),
					'username'   => Yii::$app->setting->get('smtpUser'),
					'password'   => Yii::$app->setting->get('smtpPassword'),
					'port'       => Yii::$app->setting->get('smtpPort'),
					// 'mail' => Yii::$app->setting->get('smtpMail'), // 显示地址
					'encryption' => 'tls',
				],
			]);
		}
	}

	public function sendEmail($sqlFile) {
		return Yii::$app->mailer->compose('backup')
			->setFrom([$this->formUser => $this->siteName . '- 机器人'])
			->setTo(Yii::$app->params['backupEmail'])
			->setSubject('数据库定时备份系统-' . $this->siteName)
			->attach($sqlFile)
			->send();
	}


	public function actionIndex() {
		$sql    = new MysqlBackup();
		$tables = $sql->getTables();
		Yii::info('数据库备份失败', 'backups');
		if (!$sql->StartBackup()) {
			//render error
			Yii::info('数据库备份失败', 'backup');
			die;
		}
		foreach ($tables as $tableName) {
			$sql->getColumns($tableName);
		}
		foreach ($tables as $tableName) {
			$sql->getData($tableName);
		}
		$sqlFile = $sql->EndBackup();
		$this->sendEmail($sqlFile);
		Yii::info('数据库备份成功', 'backup');
	}

	/**
	 * @inheritdoc
	 */
	public function options($actionID) {
		return array_merge(parent::options($actionID), [
			'dumpCommands',
			'folders',
			'dbKeys',
			'ftpHost',
			'ftpLogin',
			'ftpPassword',
			'ftpPort',
			'ftpTimeout',
		]);
	}

	/**
	 * Index action
	 */
	public function actionIndex1() {
		try {
			$this->makeDbDumps();
			$uploadedFiles = $this->zipFiles();
			$this->uploadFiles($uploadedFiles);
		} catch (Exception $e) {
			$this->sendError($e->getMessage());
		}
		$this->clearFiles();
	}

	/**
	 * Create sql databases dump files
	 *
	 * @throws Exception
	 */
	public function makeDbDumps() {
		foreach ($this->dbKeys as $key) {
			$file    = Yii::getAlias('@runtime') . '/dump-' . $key . '.sql';
			$command = $this->extractCommandFromParams($key, $file);
			exec($command);
			$this->dumpFiles[] = $file;
			$this->folders[]   = $file;
		}
	}

	/**
	 * Zipping list of files
	 *
	 * @return array
	 */
	protected function zipFiles() {
		$cacheDir   = $this->getCacheDir();
		$zipFile    = $cacheDir . '/' . date('Ymd-His');
		$zipCommand = 'zip - ' . implode(' ', $this->folders) . ' | split -b ' . $this->filePartSize . ' - ' . $zipFile;
		exec($zipCommand, $out);
		$files         = FileHelper::findFiles($cacheDir . '/');
		$uploadedFiles = [];
		foreach ($files as $file) {
			$this->dumpFiles[] = $uploadedFiles[] = $file;
		}
		return $uploadedFiles;
	}

	/**
	 * Get cache dir
	 */
	public function getCacheDir() {
		$cacheDir = Yii::getAlias('@runtime') . '/' . $this->cacheDir;
		if (!file_exists($cacheDir)) {
			mkdir($cacheDir);
		}
		return $cacheDir;
	}

	/**
	 * Clear temporary files
	 */
	protected function clearFiles() {
		foreach ($this->dumpFiles as $dumpFile) {
			unlink($dumpFile);
		}
	}

	/**
	 * Sending dump error report
	 *
	 * @param $error
	 */
	public function sendError($error) {
		mail($this->adminMail, 'Backup errors ' . date('Y-m-d H:i:s'), $error);
		$this->stderr($error . "\n");
	}

	/**
	 * Upload parts of zip arhive
	 *
	 * @param $uploadedFiles
	 *
	 * @throws Exception
	 */
	protected function uploadFiles($uploadedFiles) {
		$file = date('H_i_s');
		foreach ($uploadedFiles as $key => $uploadedFile) {
			$lastError = '';
			$ftp       = new FtpClient();
			$ftp->connect($this->ftpHost, $this->ftpSsl, $this->ftpPort, $this->ftpTimeout);
			$ftp->pasv(true);
			$ftp->login($this->ftpLogin, $this->ftpPassword);
			$this->goToBackupDir($ftp);
			$folder = $this->folderPrefix . '_' . date('Y-m-d');
			if (!$ftp->isDir($folder)) {
				$ftp->mkdir($folder);
			}
			$ftp->chdir($folder);
			$ftpName  = $file . '_' . $key . '.zip';
			$hasError = true;
			$tryCount = 0;
			while ($hasError && $tryCount != 9) {
				try {
					$ftp->fput($ftpName, fopen($uploadedFile, 'r'), FTP_BINARY);
					$ftpFileSize  = $ftp->size($ftpName);
					$realFileSize = filesize($uploadedFile);
					if ($ftpFileSize !== $realFileSize) {
						$lastError = 'Size of file ' . $ftpName . ' is ' . $ftpFileSize . ' but real is ' . $realFileSize;
						$tryCount ++;
					} else {
						$hasError = false;
					}
				} catch (Exception $e) {
					$tryCount ++;
					$lastError = mb_convert_encoding($e->getMessage(), 'utf8', 'cp1251');
				}
			}
			if ($hasError) {
				throw new Exception($lastError);
			}
		}
	}

	/**
	 * @param FtpClient $ftp
	 */
	protected function goToBackupDir($ftp) {
		if (!$ftp->isDir($this->ftpDir)) {
			$ftp->mkdir($this->ftpDir);
		}
		$ftp->chdir($this->ftpDir);
	}

	/**
	 * @param $key
	 * @param $file
	 *
	 * @return string
	 * @throws Exception
	 */
	protected function extractCommandFromParams($key, $file) {
		/**
		 * @var Connection $db
		 */
		$db         = Yii::$app->$key;
		$params     = [
			'{user}'     => $db->username,
			'{password}' => $db->password,
			'{file}'     => $file,
			'{host}'     => '',
			'{port}'     => '',
		];
		$driverName = $db->driverName;
		$dsn        = $db->dsn;
		$dsnParts   = explode(';', str_replace($driverName . ':', '', $dsn));
		if (count($dsnParts) < 2) {
			throw new Exception('Wrong dsn "' . $dsn . '" in "' . $key . '" db component');
		}
		foreach ($dsnParts as $part) {
			$parts             = explode('=', $part);
			$paramKey          = $parts[0];
			$paramValue        = $parts[1];
			$paramKey          = '{' . $paramKey . '}';
			$params[$paramKey] = $paramValue;
		}
		$commandsParams = array_merge($this->defaultDumpCommands, $this->dumpCommands);
		if (!array_key_exists($driverName, $commandsParams)) {
			throw new Exception('Driver by name "' . $driverName . '" is not supported, set it in "dumpCommands" param');
		}
		$commandParams = $commandsParams[$driverName];
		$command       = '';
		foreach ($commandParams as $paramKey => $paramValue) {
			if (is_int($paramKey)) {
				$paramKey = '';
			} else {
				$paramKey = '-' . $paramKey;
				if (empty($params[$paramValue])) {
					continue;
				}
			}
			$command .= ' ' . $paramKey . $paramValue;
		}
		$command = strtr($command, $params);
		return $command;
	}
}