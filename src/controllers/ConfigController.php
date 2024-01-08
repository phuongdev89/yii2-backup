<?php
/**
 * Created by phuongdev89.
 * @project yii2-backup
 * @author  Phuong
 * @email   notteen[at]gmail.com
 * @date    1/24/2019
 * @time    10:01 AM
 */

namespace phuongdev89\backup\controllers;

use phuongdev89\backup\components\MysqlBackup;
use phuongdev89\backup\models\BackupConfig;
use phuongdev89\backup\Module;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;

class ConfigController extends Controller {

	/**@var Module */
	public $module;

	/**
	 * @param \yii\base\Action $action
	 *
	 * @return bool
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function beforeAction($action) {
		$this->module->clean();
		return parent::beforeAction($action);
	}

	/**
	 * @return string
	 */
	public function actionIndex() {
		if (isset($_POST[BackupConfig::TYPE_TRANSPORT])) {
			foreach ($_POST[BackupConfig::TYPE_TRANSPORT] as $keyTransport => $postTransport) {
				foreach ($postTransport as $name => $value) {
					$backupConfig = BackupConfig::getInstance($keyTransport . '_' . $name, BackupConfig::TYPE_TRANSPORT);
					$backupConfig->updateAttributes(['value' => $value]);
				}
			}
			Yii::$app->session->setFlash('success', 'Transport updated!');
		}
		if (isset($_POST[BackupConfig::TYPE_DATABASE])) {
			$backupEnable = BackupConfig::getInstance('database_enable', BackupConfig::TYPE_DATABASE);
			$backupEnable->updateAttributes(['value' => $_POST[BackupConfig::TYPE_DATABASE]['enable']]);
			$backupConfig = BackupConfig::getInstance('database_config', BackupConfig::TYPE_DATABASE);
			$backupConfig->updateAttributes(['value' => Json::encode($_POST[BackupConfig::TYPE_DATABASE]['data'])]);
			Yii::$app->session->setFlash('success', 'Database updated!');
		}
		if (isset($_POST[BackupConfig::TYPE_DIRECTORY])) {
			$backupEnable = BackupConfig::getInstance('directory_enable', BackupConfig::TYPE_DIRECTORY);
			$backupEnable->updateAttributes(['value' => $_POST[BackupConfig::TYPE_DIRECTORY]['enable']]);
			$backupConfig = BackupConfig::getInstance('directory_config', BackupConfig::TYPE_DIRECTORY);
			$backupConfig->updateAttributes(['value' => Json::encode($_POST[BackupConfig::TYPE_DIRECTORY]['data'])]);
			Yii::$app->session->setFlash('success', 'Directory updated!');
		}
		if (isset($_POST[BackupConfig::TYPE_CRONJOB])) {
			foreach ($_POST[BackupConfig::TYPE_CRONJOB] as $name => $value) {
				$backupConfig = BackupConfig::getInstance($name, BackupConfig::TYPE_CRONJOB);
				$backupConfig->updateAttributes(['value' => $value]);
			}
			Yii::$app->session->setFlash('success', 'Cronjob updated!');
		}
		$databases = [];
		if ($this->module->databases != null) {
			foreach ($this->module->databases as $database) {
				$databases[] = new MysqlBackup(['db' => $database]);
			}
		}
		$directories = [];
		if ($this->module->directories != null) {
			foreach ($this->module->directories as $directory) {
				$iterator  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(Yii::getAlias($directory)));
				$totalSize = 0;
				$count     = 0;
				foreach ($iterator as $file) {
					$totalSize += $file->getSize();
					$count ++;
				}
				$directories[] = [
					'name'  => $directory,
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
