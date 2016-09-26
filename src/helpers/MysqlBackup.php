<?php
/**
 * author     : forecho <caizhenghai@gmail.com>
 * createTime : 2015/6/29 19:43
 * description:
 */
namespace navatech\backup\helpers;

use navatech\backup\Module;
use Yii;
use yii\base\Object;
use yii\db\Exception;

class MysqlBackup extends Object {

	/**
	 * @var Module $module
	 */
	protected $module;

	public    $menu           = [];

	public    $tables         = [];

	public    $fp;

	public    $file_name;

	public    $back_temp_file = 'db_backup_';

	/**
	 * {@inheritDoc}
	 */
	public function init() {
		parent::init();
		$this->module = Yii::$app->module;
	}

	/**
	 * @param $sqlFile
	 *
	 * @return string
	 */
	public function execSqlFile($sqlFile) {
		$message = "ok";
		if (file_exists($sqlFile)) {
			$sqlArray = file_get_contents($sqlFile);
			$cmd      = Yii::$app->db->createCommand($sqlArray);
			try {
				$cmd->execute();
			} catch (Exception $e) {
				$message = $e->getMessage();
			}
		}
		return $message;
	}

	/**
	 * @param $tableName
	 *
	 * @return mixed|string
	 */
	public function getColumns($tableName) {
		$sql          = 'SHOW CREATE TABLE ' . $tableName;
		$cmd          = Yii::$app->db->createCommand($sql);
		$table        = $cmd->queryOne();
		$create_query = $table['Create Table'] . ';';
		$create_query = preg_replace('/^CREATE TABLE/', 'CREATE TABLE IF NOT EXISTS', $create_query);
		$create_query = preg_replace('/AUTO_INCREMENT\s*=\s*([0-9])+/', '', $create_query);
		if ($this->fp) {
			$this->writeComment('TABLE `' . addslashes($tableName) . '`');
			$final = 'DROP TABLE IF EXISTS `' . addslashes($tableName) . '`;' . PHP_EOL . $create_query . PHP_EOL . PHP_EOL;
			fwrite($this->fp, $final);
		} else {
			$this->tables[$tableName]['create'] = $create_query;
			return $create_query;
		}
	}

	/**
	 * @param $tableName
	 *
	 * @return null|string
	 */
	public function getData($tableName) {
		$sql         = 'SELECT * FROM ' . $tableName;
		$cmd         = Yii::$app->db->createCommand($sql);
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
		} else {
			$this->tables[$tableName]['data'] = $data_string;
			return $data_string;
		}
	}

	/**
	 * @param null $dbName
	 *
	 * @return array
	 */
	public function getTables($dbName = null) {
		$sql    = 'SHOW TABLES';
		$cmd    = Yii::$app->db->createCommand($sql);
		$tables = $cmd->queryColumn();
		return $tables;
	}

	/**
	 * @param bool $addCheck
	 *
	 * @return bool
	 */
	public function StartBackup($addCheck = true) {
		$this->file_name = $this->module->backupPath . $this->back_temp_file . date('Y.m.d_H.i.s') . '.sql';
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
	public function EndBackup($addCheck = true) {
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
		return $this->file_name;
	}

	/**
	 * @param $string
	 */
	public function writeComment($string) {
		fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
		fwrite($this->fp, '-- ' . $string . PHP_EOL);
		fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
	}
}