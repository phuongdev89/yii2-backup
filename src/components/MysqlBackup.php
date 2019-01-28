<?php
/**
 * author     : forecho <caizhenghai@gmail.com>
 * createTime : 2015/6/29 19:43
 * description:
 */

namespace navatech\backup\components;

use navatech\backup\helpers\FileHelper;
use navatech\backup\models\BackupConfig;
use navatech\backup\Module;
use Yii;
use yii\base\BaseObject;
use yii\db\Command;
use yii\db\Exception;
use ZipArchive;

class MysqlBackup extends BaseObject {

	private   $_path;

	public    $enableZip = true;

	protected $tables    = [];

	protected $fp;

	protected $file_name;

	public    $db        = 'db';

	/**@var Module */
	protected $module;

	/**
	 * Constructor
	 *
	 * @param null $config
	 */
	public function __construct($config = null) {
		parent::__construct($config);
		if (isset($config['db']) && $config['db'] != null) {
			$this->db = $config['db'];
		}
		$this->module = Yii::$app->getModule('backup');
	}

	/**
	 * @return string|null
	 */
	public function getName() {
		return $this->db;
	}

	/**
	 * @param $tableName
	 *
	 * @return mixed|string
	 * @throws Exception
	 */
	public function getColumns($tableName) {
		/**@var Command $cmd */
		$sql          = 'SHOW CREATE TABLE ' . $tableName;
		$cmd          = Yii::$app->{$this->db}->createCommand($sql);
		$table        = $cmd->queryOne();
		$create_query = $table['Create Table'] . ';';
		$create_query = preg_replace('/^CREATE TABLE/', 'CREATE TABLE IF NOT EXISTS', $create_query);
		$create_query = preg_replace('/AUTO_INCREMENT\s*=\s*([0-9])+/', '', $create_query);
		if ($this->fp) {
			$this->writeComment('TABLE `' . addslashes($tableName) . '`');
			$final = 'DROP TABLE IF EXISTS `' . addslashes($tableName) . '`;' . PHP_EOL . $create_query . PHP_EOL . PHP_EOL;
			fwrite($this->fp, $final);
			return true;
		} else {
			$this->tables[$tableName]['create'] = $create_query;
			return $create_query;
		}
	}

	/**
	 * @param $string
	 */
	public function writeComment($string) {
		fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
		fwrite($this->fp, '-- ' . $string . PHP_EOL);
		fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
	}

	/**
	 * @param $tableName
	 *
	 * @return null|string
	 * @throws Exception
	 */
	public function getData($tableName) {
		/**@var Command $cmd */
		$sql         = 'SELECT * FROM ' . $tableName;
		$cmd         = Yii::$app->{$this->db}->createCommand($sql);
		$dataReader  = $cmd->query();
		$data_string = '';
		foreach ($dataReader as $data) {
			$itemNames   = array_keys($data);
			$itemNames   = array_map("addslashes", $itemNames);
			$items       = join('`,`', $itemNames);
			$itemValues  = array_values($data);
			$itemValues  = array_map("addslashes", $itemValues);
			$valueString = join("','", $itemValues);
			$valueString = "('" . $valueString . "'),";
			$values      = "\n" . $valueString;
			if ($values != "") {
				$data_string .= "INSERT INTO `$tableName` (`$items`) VALUES" . rtrim($values, ",") . ";" . PHP_EOL;
			}
		}
		if ($data_string == '') {
			return null;
		}
		if ($this->fp) {
			$this->writeComment('TABLE DATA ' . $tableName);
			$final = $data_string . PHP_EOL . PHP_EOL . PHP_EOL;
			fwrite($this->fp, $final);
			return true;
		} else {
			$this->tables[$tableName]['data'] = $data_string;
			return $data_string;
		}
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	public function getTables() {
		/**@var Command $cmd */
		$sql    = 'SHOW TABLES';
		$cmd    = Yii::$app->{$this->db}->createCommand($sql);
		$tables = $cmd->queryColumn();
		return $tables;
	}

	/**
	 * @param bool $addCheck
	 *
	 * @return bool
	 */
	public function startBackup($addCheck = true) {
		$this->file_name = BackupConfig::getCronjob('backupPath') . DIRECTORY_SEPARATOR . Module::TYPE_DATABASE . '_' . $this->getName() . '_' . date('Y.m.d_H.i.s') . '.sql';
		$this->fp        = fopen($this->file_name, 'w+');
		if ($this->fp == null) {
			return false;
		}
		fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
		if ($addCheck) {
			fwrite($this->fp, 'SET AUTOCOMMIT=0;' . PHP_EOL);
			fwrite($this->fp, 'START TRANSACTION;' . PHP_EOL);
			fwrite($this->fp, 'SET SQL_QUOTE_SHOW_CREATE = 1;' . PHP_EOL);
		}
		fwrite($this->fp, 'SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;' . PHP_EOL);
		fwrite($this->fp, 'SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;' . PHP_EOL);
		fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
		$this->writeComment('START BACKUP');
		return true;
	}

	/**
	 * @param bool $addCheck
	 *
	 * @return mixed
	 */
	public function endBackup($addCheck = true) {
		fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
		fwrite($this->fp, 'SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;' . PHP_EOL);
		fwrite($this->fp, 'SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;' . PHP_EOL);
		if ($addCheck) {
			fwrite($this->fp, 'COMMIT;' . PHP_EOL);
		}
		fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
		$this->writeComment('END BACKUP');
		fclose($this->fp);
		$this->fp = null;
		if ($this->enableZip) {
			$this->createZipBackup();
		}
		return $this->file_name;
	}

	/**
	 * @param      $sql_file
	 * @param bool $force_remove
	 *
	 * @return string
	 */
	public function execSqlFile($sql_file, $force_remove = false) {
		/**@var Command $cmd */
		if (file_exists($sql_file)) {
			$sqlArray = file_get_contents($sql_file);
			$cmd      = Yii::$app->{$this->db}->createCommand($sqlArray);
			try {
				$cmd->execute();
				if ($force_remove) {
					FileHelper::unlink($sql_file);
				}
			} catch (Exception $e) {
				return $e->getMessage();
			}
		} else {
			return 'File not existed';
		}
		return true;
	}

	/**
	 * Zip file execution
	 *
	 * @param $sqlZipFile
	 *
	 * @return mixed
	 */
	public function unzip($sqlZipFile) {
		if (file_exists($sqlZipFile)) {
			$zip    = new ZipArchive();
			$result = $zip->open($sqlZipFile);
			if ($result === true) {
				$zip->extractTo(dirname($sqlZipFile));
				$zip->close();
				$sqlZipFile = str_replace(".zip", "", $sqlZipFile);
			}
		}
		return $sqlZipFile;
	}

	/**
	 * @return string
	 */
	protected function getPath() {
		$this->_path = Yii::$app->basePath . '/db/';
		if (!file_exists($this->_path)) {
			@mkdir($this->_path, 0775, true);
		}
		return $this->_path;
	}

	/**
	 * Charge method to backup and create a zip with this
	 */
	private function createZipBackup() {
		if (class_exists(ZipArchive::class)) {
			$zip       = new ZipArchive();
			$file_name = $this->file_name . '.zip';
			if ($zip->open($file_name, ZipArchive::CREATE) === true) {
				$zip->addFile($this->file_name, basename($this->file_name));
				$zip->close();
				@unlink($this->file_name);
				$this->file_name = $file_name;
			}
		} else {
			echo "ZipArchive missing class ";
		}
	}
}
