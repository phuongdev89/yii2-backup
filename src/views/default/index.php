<?php

use kartik\grid\DataColumn;
use kartik\grid\GridView;
use navatech\backup\Module;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/**@var \yii\web\View $this */
/**@var \yii\data\ArrayDataProvider $dataProvider */
/**@var \navatech\backup\models\Backup $searchModel */
/**@var Module $module */
/**@var \navatech\backup\components\MysqlBackup $mysqlBackup */
$this->title                     = 'Backup management';
$this->params ['breadcrumbs'] [] = [
	'label' => 'Manage',
	'url'   => [
		'index',
	],
];
?>
<div class="backup-default-index">
	<p>
		<a data-toggle="modal" href="#create-database" class="btn btn-primary"><i class="glyphicon glyphicon-duplicate"></i> Backup database</a>
		<a href="<?= Url::to(['/backup/default/create-directory']) ?>" class="btn btn-success" data-confirm="This may take a few minutes. Do you want to continue?"><i class="glyphicon glyphicon-folder-open"></i> Backup directory</a>
		<!--		<a href="--><? //= Url::to(['/backup/default/upload']) ?><!--" class="btn btn-warning"><i class="glyphicon glyphicon-open"></i> Upload database</a>-->
	</p>
	<div class="modal fade" id="create-database">
		<div class="modal-dialog">
			<div class="modal-content">
				<?php $form = ActiveForm::begin(['action' => Url::to(['/backup/default/create-database'])]) ?>
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title">Create Database backup</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-xs-12">
							<div class="col-sm-6">
								<?= Html::checkbox('schema_all', true, ['id' => 'table-all']) ?>
								<label for="table-all">Table name</label>
							</div>
							<div class="col-sm-3"><?= Html::checkbox('schema_all', true, ['id' => 'schema-all']) ?>
								<label for="schema-all">Schema</label>
							</div>
							<div class="col-sm-3"><?= Html::checkbox('data_all', true, ['id' => 'data-all']) ?>
								<label for="data-all">Data</label>
							</div>
						</div>
						<?php foreach ($mysqlBackup->getTables() as $table) : ?>
							<div class="col-xs-12">
								<div class="col-sm-6"><?= $table ?></div>
								<div class="col-sm-3" data-checkbox="schema"><?= Html::checkbox('table[' . $table . '][schema]', true) ?></div>
								<div class="col-sm-3" data-checkbox="data"><?= Html::checkbox('table[' . $table . '][data]', true) ?></div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-primary">Save changes</button>
				</div>
				<?php ActiveForm::end() ?>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<div class="row">
		<div class="col-xs-12">
			<div class="info-box">
				<span class="info-box-icon bg-aqua"><i class="fa fa-spinner fa-spin"></i></span>

				<div class="info-box-content">
					<span class="info-box-text">Messages</span>
					<span class="info-box-number">1,410</span>
				</div>
				<!-- /.info-box-content -->
			</div>
			<!-- /.info-box -->
		</div>
	</div>
	<?php
	Pjax::begin();
	echo GridView::widget([
		'id'           => 'install-grid',
		'filterModel'  => $searchModel,
		'dataProvider' => $dataProvider,
		'columns'      => [
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
				'attribute'           => 'modified_at',
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
					Module::TYPE_DATABASE  => 'Database',
					Module::TYPE_DIRECTORY => 'Directory',
				],
				'format'    => 'raw',
				'value'     => function($data) {
					if ($data['type'] == Module::TYPE_DATABASE) {
						return '<span class="label label-info" style="font-size: 14px">Database</span>';
					} else {
						return '<span class="label label-warning" style="font-size: 14px">Directory</span>';
					}
				},
			],
			[
				'class'          => 'yii\grid\ActionColumn',
				'template'       => '{download}{restore}{delete}',
				'visibleButtons' => [
					'restore' => function($model) {
						return $model['type'] == Module::TYPE_DATABASE;
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
						'/backup/default/' . $action,
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
<script>
	$(document).on('change', '#schema-all', function() {
		if($(this).is(':checked')) {
			$('[data-checkbox="schema"]').find('input[type="checkbox"]').prop('checked', true);
		} else {
			$('[data-checkbox="schema"]').find('input[type="checkbox"]').prop('checked', false);
		}
	});
	$(document).on('change', '#data-all', function() {
		if($(this).is(':checked')) {
			$('[data-checkbox="data"]').find('input[type="checkbox"]').prop('checked', true);
		} else {
			$('[data-checkbox="data"]').find('input[type="checkbox"]').prop('checked', false);
		}
	});
	$(document).on('change', '#table-all', function() {
		if($(this).is(':checked')) {
			$('[data-checkbox="data"]').find('input[type="checkbox"]').prop('checked', true);
			$('[data-checkbox="schema"]').find('input[type="checkbox"]').prop('checked', true);
		} else {
			$('[data-checkbox="data"]').find('input[type="checkbox"]').prop('checked', false);
			$('[data-checkbox="schema"]').find('input[type="checkbox"]').prop('checked', false);
		}
	});
	progress();

	function progress() {
		$.ajax({
			method  : 'GET',
			url     : '<?=Url::to(['/backup/default/percent'])?>',
			dataType: 'json',
			success : function(response) {
				// if(response.error === 0) {
				$('.info-box-number').html(response.message);
				setTimeout(function() {
					progress();
				}, 5000)
				// }
			}
		});
	}
</script>
