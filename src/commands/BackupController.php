<?php
/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/26/16
 * Time: 9:43 PM
 */
namespace navatech\backup\commands;

use navatech\backup\helpers\MysqlHelper;
use navatech\backup\Module;
use PharData;
use Yii;
use yii\console\Controller;

/**
 * @property Module $module
 */
class BackupController extends Controller {

	/**
	 * Backup directory
	 *
	 * @param string $path Force the path which needs to be backed up
	 */
	public function actionFolder($path = null) {
		/**@var Module $module */
		$module = Yii::$app->getModule('backup');
		if ($module->backupFolderEnable()) {
			$paths = $module->backupFolderData();
			if ($path != null) {
				$paths = [$path];
			}
			foreach ($paths as $folder) {
				$folder = Yii::getAlias($folder);
				if (file_exists($folder)) {
					$archiveFile = $module->backupPath . DIRECTORY_SEPARATOR . 'folder_' . date('Y.m.d_H.i.s') . '.tar';
					$archive     = new PharData($archiveFile);
					$archive->buildFromDirectory($folder);
					if ($module->mail->enable) {
						if (filesize($archiveFile) < 30000000) {
							$module->mail->setFile($archiveFile)->setType(Module::TYPE_FOLDER)->send();
						} else {
							echo 'Can not send file, file size is too big!' . PHP_EOL;
						}
					}
					if ($module->ftp->enable) {
						$module->ftp->setFile($archiveFile)->push();
					}
					echo 'Folder "' . $folder . '" backed up!' . PHP_EOL;
				} else {
					echo 'Folder "' . $folder . '" does not exists' . PHP_EOL;
				}
			}
		}
	}

	/**
	 * Backup db
	 *
	 * @param string $db Force the db which needs to be backed up
	 */
	public function actionDb($db = null) {
		/**@var Module $module */
		$module = Yii::$app->getModule('backup');
		if ($module->backupDbEnable()) {
			$dbs = $module->backupDbData();
			if ($db != null) {
				$dbs = [$db];
			}
			foreach ($dbs as $db) {
				$sql    = new MysqlHelper($db);
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
				if ($module->mail->enable) {
					$module->mail->setFile($sqlFile)->setType(Module::TYPE_DB)->send();
				}
				if ($module->ftp->enable) {
					$module->ftp->setFile($sqlFile)->push();
				}
				echo 'Backup success!' . PHP_EOL;
			}
		} else {
			echo 'DB Backup not enable!' . PHP_EOL;
		}
	}

	/**
	 * Remove files was created x days ago
	 *
	 * @param int $days Days old of files
	 */
	public function actionClear($days = 3) {
		/**@var Module $module */
		$module = Yii::$app->getModule('backup');
		$files  = array_diff(scandir($module->backupPath), [
			'.',
			'..',
		]);
		foreach ($files as $file) {
			$filePath = $module->backupPath . DIRECTORY_SEPARATOR . $file;
			$old      = (time() - filemtime($filePath)) / (3600 * 24);
			if ($old >= $days) {
				echo unlink($filePath) ? 'Removed "' . $file . '"' . PHP_EOL : 'Can not remove "' . $file . '"' . PHP_EOL;
			}
		}
	}

	public function actionTest() {
	}
}