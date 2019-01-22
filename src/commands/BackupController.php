<?php
/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/26/16
 * Time: 9:43 PM
 */

namespace navatech\backup\commands;

use navatech\backup\components\MysqlBackup;
use navatech\backup\Module;
use PharData;
use Swift_TransportException;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * @property Module $this->module
 */
class BackupController extends Controller {

	/**@var Module */
	public $module;

	/**
	 * @param \yii\base\Action $action
	 *
	 * @return bool
	 */
	public function beforeAction($action) {
		$this->module->clean();
		return parent::beforeAction($action);
	}

	/**
	 * Backup directory
	 *
	 * @param string $path Force the path which needs to be backed up
	 *
	 * @throws \yii\base\Exception
	 */
	public function actionDirectory($path = null) {
		if ($this->module->isDirectoryBackupEnable()) {
			$paths = $this->module->backupDirectoryData();
			if ($path != null) {
				$paths = [$path];
			}
			foreach ($paths as $folder) {
				$folder = Yii::getAlias($folder);
				if (file_exists($folder)) {
					$archiveFile = $this->module->backupPath . DIRECTORY_SEPARATOR . Module::TYPE_DIRECTORY . '_' . date('Y.m.d_H.i.s') . '.tar';
					$archive     = new PharData($archiveFile);
					$archive->buildFromDirectory($folder);
					if ($this->module->mail->enable) {
						if (filesize($archiveFile) < 20 * 1024 * 1024) {
							try {
								$this->module->mail->setFile($archiveFile)->setType(Module::TYPE_DIRECTORY)->send();
							} catch (Swift_TransportException $e) {
								Console::output('Can not send email. ' . $e->getMessage());
							}
						} else {
							echo 'Can not send file, file size is too big!' . PHP_EOL;
						}
					}
					if ($this->module->ftp->enable) {
						$this->module->ftp->setFile($archiveFile)->push();
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
	 *
	 * @throws \yii\base\Exception
	 * @throws \yii\db\Exception
	 */
	public function actionDatabase($db = null) {
		if ($this->module->isDatabaseBackupEnable()) {
			$dbs = $this->module->backupDatabaseData();
			if ($db != null) {
				$dbs = [$db];
			}
			Console::output('Starting backup database.');
			foreach ($dbs as $db) {
				Console::output('Backing up `' . $db . '`');
				Console::startProgress(0, 100);
				$sql    = new MysqlBackup($db);
				$tables = $sql->getTables();
				if (!$sql->StartBackup()) {
					die;
				}
				foreach ($tables as $tableKey => $tableName) {
					Console::updateProgress(($tableKey + 1) / 2, count($tables));
					$sql->getColumns($tableName);
				}
				foreach ($tables as $tableKey => $tableName) {
					Console::updateProgress(count($tables) / 2 + ($tableKey + 1) / 2, count($tables));
					$sql->getData($tableName);
				}
				$sqlFile = $sql->EndBackup();
				Console::endProgress(true, false);
				Console::output('Backed up `' . $db . '`');
				if ($this->module->mail->enable) {
					if (filesize($sqlFile) < 20 * 1024 * 1024) {
						try {
							$this->module->mail->setFile($sqlFile)->setType(Module::TYPE_DATABASE)->send();
						} catch (Swift_TransportException $e) {
							Console::output('Can not send email. ' . $e->getMessage());
						}
					} else {
						Console::output('Can not attach filesize > 20MB.');
					}
				}
				if ($this->module->ftp->enable) {
					$this->module->ftp->setFile($sqlFile)->push();
				}
				Console::output('Backed up `' . $db . '`');
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
		$files = array_diff(scandir($this->module->backupPath), [
			'.',
			'..',
		]);
		foreach ($files as $file) {
			$filePath = $this->module->backupPath . DIRECTORY_SEPARATOR . $file;
			$old      = (time() - filemtime($filePath)) / (3600 * 24);
			if ($old >= $days) {
				echo unlink($filePath) ? 'Removed "' . $file . '"' . PHP_EOL : 'Can not remove "' . $file . '"' . PHP_EOL;
			}
		}
	}
}
