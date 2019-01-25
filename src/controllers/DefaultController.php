<?php

namespace navatech\backup\controllers;

use navatech\backup\components\MysqlBackup;
use navatech\backup\models\Backup;
use navatech\backup\models\BackupConfig;
use navatech\backup\models\UploadForm;
use navatech\backup\Module;
use PharData;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\UploadedFile;

/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/30/16
 * Time: 3:03 PM
 */
class DefaultController extends Controller {

	public $menu      = [];

	public $tables    = [];

	public $fp;

	public $file_name;

	public $enableZip = true;

	/**@var Module */
	public  $module;

	private $progress_message = null;

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
	 * @return array
	 */
	public function behaviors() {
		return [
			'access' => [
				'class' => AccessControl::class,
				'only'  => [
					'index',
					'create-database',
					'delete',
					'download',
					'restore',
				],
				'rules' => [
					[
						'actions' => [
							'index',
							'create-database',
							'delete',
							'download',
							'restore',
						],
						'allow'   => true,
						'roles'   => ['@'],
					],
				],
			],
			'verbs'  => [
				'class'   => VerbFilter::class,
				'actions' => [
					'delete'          => ['post'],
					'restore'         => ['post'],
					'download'        => ['post'],
					'create-database' => ['post'],
				],
			],
		];
	}

	/**
	 *
	 * @return mixed
	 * @throws \yii\db\Exception
	 */
	public function actionCreateDatabase() {
		if ($this->module->databases != null && isset($_POST['table'])) {
			$post = $_POST;
			$sql  = new MysqlBackup();
			if (isset($post['table'])) {
				$tables = array_keys($post['table']);
			} else {
				$tables = $sql->getTables();
			}
			if (!$sql->startBackup()) {
				Yii::$app->session->setFlash('error', "Error");
				return $this->redirect(['index']);
			}
			$this->progress_message = 'Backup started!';
			foreach ($tables as $tableName) {
				if (isset($post['table'][$tableName]['schema']) && $post['table'][$tableName]['schema'] == 1) {
					$sql->getColumns($tableName);
					$this->progress_message = 'table ' . $tableName;
				}
			}
			foreach ($tables as $tableName) {
				if (isset($post['table'][$tableName]['data']) && $post['table'][$tableName]['data'] == 1) {
					$sql->getData($tableName);
					$this->progress_message = 'data ' . $tableName;
				}
			}
			$sql->endBackup();
			Yii::$app->session->setFlash('success', 'Backed up database');
		} else {
			Yii::$app->session->setFlash('error', 'Backup database has not been enabled');
		}
		return $this->redirect([
			'index',
		]);
	}

	/**
	 * @return mixed
	 */
	public function actionCreateDirectory() {
		if ($this->module->directories != null) {
			$paths = $this->module->directories;
			foreach ($paths as $folder) {
				$folder = Yii::getAlias($folder);
				if (file_exists($folder)) {
					$archiveFile = BackupConfig::getCronjob('backupPath') . DIRECTORY_SEPARATOR . Module::TYPE_DIRECTORY . '_' . date('Y.m.d_H.i.s') . '.tar';
					$archive     = new PharData($archiveFile);
					$archive->buildFromDirectory($folder);
					Yii::$app->session->setFlash('success', 'Backed up directory');
				} else {
					Yii::$app->session->setFlash('error', 'Directory does not existed');
				}
			}
		} else {
			Yii::$app->session->setFlash('error', 'Backup directory has not been enabled');
		}
		return $this->redirect([
			'index',
		]);
	}

	/**
	 * @param $file
	 *
	 * @return mixed
	 */
	public function actionDelete($file) {
		$sqlFile = Yii::getAlias(BackupConfig::getCronjob('backupPath'). DIRECTORY_SEPARATOR . basename($file));
		if (file_exists($sqlFile)) {
			unlink($sqlFile);
			Yii::$app->session->setFlash('success', 'File was deleted');
		} else {
			Yii::$app->session->setFlash('error', 'File not found');
		}
		return $this->redirect(['index']);
	}

	/**
	 * @return mixed
	 */
	public function actionIndex() {
		$searchModel  = new Backup();
		$dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
		$mysqlBackup  = new MysqlBackup();
		return $this->render('index', [
			'module'       => $this->module,
			'searchModel'  => $searchModel,
			'dataProvider' => $dataProvider,
			'mysqlBackup'  => $mysqlBackup,
		]);
	}

	/**
	 * @param $file
	 *
	 * @return mixed
	 */
	public function actionRestore($file) {
		if (strpos(basename($file), Module::TYPE_DIRECTORY) !== false) {
			Yii::$app->session->setFlash('warning', 'The DIRECTORY backup can not be restored automatically, please download and restore it manual!');
		} else {
			ini_set('max_execution_time', 0);
			$sql         = new MysqlBackup();
			$backup_file = BackupConfig::getCronjob('backupPath') . DIRECTORY_SEPARATOR . basename($file);
			$ext         = pathinfo($backup_file, PATHINFO_EXTENSION);
			if (in_array($ext, [
				'zip',
				'gz',
				'tar',
				'gzip',
				'7z',
			])) {
				$force_remove = true;
			} else {
				$force_remove = false;
			}
			$sql_file = $sql->unzip($backup_file);
			$message  = $sql->execSqlFile($sql_file, $force_remove);
			if ($message) {
				Yii::$app->session->setFlash('success', 'Restored database successfully.');
			} else {
				Yii::$app->session->setFlash('error', $message);
			}
		}
		return $this->redirect(['index']);
	}

	/**
	 * @return mixed
	 */
	public function actionUpload() {
		$model = new UploadForm();
		if (isset ($_POST ['UploadForm'])) {
			$model->attributes  = $_POST ['UploadForm'];
			$model->upload_file = UploadedFile::getInstance($model, 'upload_file');
			if ($model->upload_file->saveAs($this->path . $model->upload_file)) {
				return $this->redirect(array(
					'index',
				));
			}
		}
		return $this->render('upload', array(
			'model' => $model,
		));
	}

	/**
	 * @param $file
	 */
	public function actionDownload($file) {
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename=' . basename($file));
		header('Pragma: no-cache');
		readfile(BackupConfig::getCronjob('backupPath') . DIRECTORY_SEPARATOR . basename($file));
	}

	public function actionPercent($type = Module::TYPE_DATABASE) {
		Yii::$app->response->format = 'json';
		//		if ($this->progress_message != null) {
		return [
			'error'   => 0,
			'message' => $this->progress_message,
		];
		//		}
	}
}
