<?php
/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/26/16
 * Time: 11:37 AM
 */
namespace navatech\backup\commands;

use navatech\backup\helpers\MysqlBackup;
use navatech\backup\Module;
use yii\console\Controller;

/**
 * @property Module $module
 */
class BackupController extends Controller {

	/**
	 * Action backup db
	 */
	public function actionDb() {
		$sql    = new MysqlBackup();
		$tables = $sql->getTables();
		if (!$sql->StartBackup()) {
			die;
		}
		foreach ($tables as $tableName) {
			$sql->getColumns($tableName);
		}
		foreach ($tables as $tableName) {
			$sql->getData($tableName);
		}
		$sqlFile = $sql->EndBackup();
		Module::sendEmail($sqlFile);
	}
}