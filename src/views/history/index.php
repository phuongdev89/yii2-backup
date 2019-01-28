<?php

use kartik\grid\DataColumn;
use kartik\grid\GridView;
use navatech\backup\components\MysqlBackup;
use navatech\backup\models\BackupHistory;
use navatech\backup\models\search\BackupHistorySearch;
use navatech\backup\Module;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\data\ArrayDataProvider;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

/**@var View $this */
/**@var ArrayDataProvider $dataProvider */
/**@var Module $module */
/**@var MysqlBackup $mysqlBackup */
/** @var BackupHistorySearch $searchModel */
$this->title                     = 'Backup history';
$this->params ['breadcrumbs'] [] = [
	'label' => 'Manage',
	'url'   => [
		'index',
	],
];
?>
<div class="backup-default-index">
	<?php
	Pjax::begin();
	echo GridView::widget([
		'id'           => 'install-grid',
		'filterModel'  => $searchModel,
		'dataProvider' => $dataProvider,
		'columns'      => [
			['class' => 'yii\grid\SerialColumn'],
			'name',
			'size:shortSize',
			[
				'class'               => DataColumn::class,
				'attribute'           => 'created_at',
				'filterType'          => GridView::FILTER_DATE_RANGE,
				'filterWidgetOptions' => [
					'readonly'      => 'readonly',
					'convertFormat' => true,
					'pluginOptions' => [
						'locale'    => ['format' => 'Y-m-d'],
						'autoclose' => true,
					],
					'pluginEvents'  => [
						"cancel.daterangepicker" => 'function(ev,picker){$(this).val("").trigger("change");}',
					],
				],
				'format'              => [
					'datetime',
				],
			],
			[
				'class'               => DataColumn::class,
				'attribute'           => 'updated_at',
				'filterType'          => GridView::FILTER_DATE_RANGE,
				'filterWidgetOptions' => [
					'readonly'      => 'readonly',
					'convertFormat' => true,
					'pluginOptions' => [
						'locale'    => ['format' => 'Y-m-d'],
						'autoclose' => true,
					],
					'pluginEvents'  => [
						"cancel.daterangepicker" => 'function(ev,picker){$(this).val("").trigger("change");}',
					],
				],
				'format'              => [
					'datetime',
				],
			],
			[
				'attribute' => 'type',
				'filter'    => [
					BackupHistory::TYPE_DATABASE  => 'Database',
					BackupHistory::TYPE_DIRECTORY => 'Directory',
				],
				'format'    => 'raw',
				'value'     => function(BackupHistory $data) {
					if ($data->type == BackupHistory::TYPE_DATABASE) {
						return '<span class="label label-info" style="font-size: 14px">Database</span>';
					} else {
						return '<span class="label label-warning" style="font-size: 14px">Directory</span>';
					}
				},
			],
			[
				'attribute' => 'status',
				'filter'    => [
					BackupHistory::STATUS_DRAFT => 'Draft',
					BackupHistory::STATUS_DONE  => 'Done',
				],
				'format'    => 'raw',
				'value'     => function(BackupHistory $data) {
					if ($data->status == BackupHistory::STATUS_DRAFT) {
						return '<span class="label label-warning" style="font-size: 14px">Draft</span>';
					} else {
						return '<span class="label label-primary" style="font-size: 14px">Done</span>';
					}
				},
			],
			[
				'attribute' => 'mail_status',
				'filter'    => [
					BackupHistory::STATUS_DRAFT => 'No',
					BackupHistory::STATUS_DONE  => 'Yes',
				],
				'format'    => 'raw',
				'value'     => function(BackupHistory $data) {
					if ($data->mail_status == BackupHistory::STATUS_DRAFT) {
						return '<span class="label label-warning" style="font-size: 14px">No</span>';
					} else {
						return '<span class="label label-primary" style="font-size: 14px">Yes</span>';
					}
				},
			],
			[
				'attribute' => 'ftp_status',
				'filter'    => [
					BackupHistory::STATUS_DRAFT => 'No',
					BackupHistory::STATUS_DONE  => 'Yes',
				],
				'format'    => 'raw',
				'value'     => function(BackupHistory $data) {
					if ($data->ftp_status == BackupHistory::STATUS_DRAFT) {
						return '<span class="label label-warning" style="font-size: 14px">No</span>';
					} else {
						return '<span class="label label-primary" style="font-size: 14px">Yes</span>';
					}
				},
			],
			[
				'attribute' => 's3_status',
				'filter'    => [
					BackupHistory::STATUS_DRAFT => 'No',
					BackupHistory::STATUS_DONE  => 'Yes',
				],
				'format'    => 'raw',
				'value'     => function(BackupHistory $data) {
					if ($data->s3_status == BackupHistory::STATUS_DRAFT) {
						return '<span class="label label-warning" style="font-size: 14px">No</span>';
					} else {
						return '<span class="label label-primary" style="font-size: 14px">Yes</span>';
					}
				},
			],
			[
				'class'          => 'yii\grid\ActionColumn',
				'template'       => '{download}{restore}{delete}',
				'visibleButtons' => [
					'restore' => function(BackupHistory $model) {
						return $model->type == BackupHistory::TYPE_DATABASE;
					},
				],
				'buttons'        => [
					'download' => function($url, $model) {
						return Html::a('<span class="glyphicon glyphicon-save"></span> ', $url, [
							'class'       => 'btn btn-sm btn-primary m-5',
							'title'       => Yii::t('app', 'Download this backup'),
							'data-method' => 'post',
						]);
					},
					'restore'  => function($url, $model) {
						return Html::a('<span class="glyphicon glyphicon-repeat"></span> ', $url, [
							'class'        => 'btn btn-sm btn-success m-5',
							'title'        => Yii::t('app', 'Restore this backup'),
							'data-confirm' => 'All data will be lost, are you sure?',
							'data-method'  => 'post',
						]);
					},
					'delete'   => function($url, $model) {
						return Html::a('<span class="glyphicon glyphicon-remove"></span>', $url, [
							'class'        => 'btn btn-sm btn-danger m-5',
							'title'        => Yii::t('app', 'Delete this backup'),
							'data-confirm' => 'Are you sure?',
							'data-method'  => 'post',
						]);
					},
				],
				'urlCreator'     => function($action, $model, $key, $index) {
					$url = Url::to([
						'/backup/history/' . $action,
						'file' => $model['name'],
					]);
					return $url;
				},
			],
		],
		'pjax'         => true,
		'responsive'   => true,
		'hover'        => true,
		'condensed'    => true,
		'floatHeader'  => true,
		'panel'        => [
			'heading'    => '',
			'type'       => 'info',
			'before'     => '',
			'after'      => Html::a('<i class="glyphicon glyphicon-repeat"></i> Reset List', ['index'], ['class' => 'btn btn-info']),
			'showFooter' => false,
		],
	]);
	Pjax::end();
	?>
</div>
