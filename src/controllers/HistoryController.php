<?php

namespace navatech\backup\controllers;

use navatech\backup\components\MysqlBackup;
use navatech\backup\models\BackupConfig;
use navatech\backup\models\BackupHistory;
use navatech\backup\models\search\BackupHistorySearch as BackupHistorySearch;
use navatech\backup\Module;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/30/16
 * Time: 3:03 PM
 */
class HistoryController extends Controller {

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
					'delete',
					'download',
					'restore',
				],
				'rules' => [
					[
						'actions' => [
							'index',
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
					'delete'   => ['post'],
					'restore'  => ['post'],
					'download' => ['post'],
				],
			],
		];
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
	 * @param $file
	 */
	public function actionDownload($file) {
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename=' . basename($file));
		header('Pragma: no-cache');
		readfile(BackupConfig::getCronjob('backupPath') . DIRECTORY_SEPARATOR . basename($file));
	}

	/**
	 * Lists all BackupHistorySearch models.
	 * @return mixed
	 */
	public function actionIndex() {
		$searchModel  = new BackupHistorySearch;
		$dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
		return $this->render('index', [
			'module'       => $this->module,
			'dataProvider' => $dataProvider,
			'searchModel'  => $searchModel,
			'mysqlBackup'  => new MysqlBackup(),
		]);
	}

	/**
	 * Creates a new BackupHistorySearch model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 */
	public function actionCreate() {
		$model = new BackupHistory;
		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			return $this->redirect([
				'view',
				'id' => $model->id,
			]);
		} else {
			return $this->render('create', [
				'model' => $model,
			]);
		}
	}

	/**
	 * Deletes an existing BackupHistorySearch model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 * @throws NotFoundHttpException
	 * @throws \Throwable
	 * @throws \yii\db\StaleObjectException
	 */
	public function actionDelete($id) {
		$model   = $this->findModel($id);
		$sqlFile = Yii::getAlias(BackupConfig::getCronjob('backupPath') . DIRECTORY_SEPARATOR . basename($model->name));
		if (file_exists($sqlFile)) {
			unlink($sqlFile);
			Yii::$app->session->setFlash('success', 'File was deleted');
		} else {
			Yii::$app->session->setFlash('error', 'File not found');
		}
		$model->delete();
		return $this->redirect(['index']);
	}

	/**
	 * Finds the BackupHistorySearch model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param integer $id
	 *
	 * @return BackupHistory the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id) {
		if (($model = BackupHistory::findOne($id)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
	}
}
