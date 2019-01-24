<?php
/**
 * Created by Navatech.
 * @project yii2-backup
 * @author  Phuong
 * @email   notteen[at]gmail.com
 * @date    1/24/2019
 * @time    10:01 AM
 */

namespace navatech\backup\controllers;

use navatech\backup\components\MysqlBackup;
use navatech\backup\models\BackupConfig;
use navatech\backup\Module;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;

class ConfigController extends Controller {

	/**@var Module */
	public $module;

	/**
	 * @return string
	 */
	public function actionIndex() {
		if (isset($_POST[BackupConfig::TYPE_TRANSPORT])) {
			foreach ($_POST[BackupConfig::TYPE_TRANSPORT] as $keyTransport => $postTransport) {
				foreach ($postTransport as $name => $value) {
					$backupConfig = BackupConfig::findOne(['name' => $keyTransport . '_' . $name]);
					if ($backupConfig !== null) {
						$backupConfig->updateAttributes(['value' => $value]);
					}
				}
			}
			Yii::$app->session->setFlash('success', 'Updated!');
		}
		if (isset($_POST[BackupConfig::TYPE_DATABASE])) {
			$backupConfig = BackupConfig::findOne(['name' => 'database_config']);
			if ($backupConfig === null) {
				$backupConfig       = new BackupConfig();
				$backupConfig->name = 'database_config';
				$backupConfig->type = BackupConfig::TYPE_DATABASE;
				$backupConfig->save();
			}
			$backupConfig->updateAttributes(['value' => Json::encode($_POST[BackupConfig::TYPE_DATABASE])]);
			Yii::$app->session->setFlash('success', 'Updated!');
		}
		if (isset($_POST[BackupConfig::TYPE_DIRECTORY])) {
			$backupConfig = BackupConfig::findOne(['name' => 'directory_config']);
			if ($backupConfig === null) {
				$backupConfig       = new BackupConfig();
				$backupConfig->name = 'directory_config';
				$backupConfig->type = BackupConfig::TYPE_DIRECTORY;
				$backupConfig->save();
			}
			$backupConfig->updateAttributes(['value' => Json::encode($_POST[BackupConfig::TYPE_DIRECTORY])]);
			Yii::$app->session->setFlash('success', 'Updated!');
		}
		$databases = [];
		if (isset($this->module->backup['database'])) {
			foreach ($this->module->backup['database']['data'] as $datum) {
				$databases[] = new MysqlBackup(['db' => $datum]);
			}
		}
		$directories = [];
		if (isset($this->module->backup['directory'])) {
			foreach ($this->module->backup['directory']['data'] as $datum) {
				$iterator  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(Yii::getAlias($datum)));
				$totalSize = 0;
				$count     = 0;
				foreach ($iterator as $file) {
					$totalSize += $file->getSize();
					$count ++;
				}
				$directories[] = [
					'name'  => $datum,
					'count' => $count,
					'size'  => $totalSize,
				];
			}
		}
		return $this->render('index', [
			'module'      => $this->module,
			'databases'   => $databases,
			'directories' => $directories,
		]);
	}
}
