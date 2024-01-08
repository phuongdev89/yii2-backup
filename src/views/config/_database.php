<?php
/**
 * Created by phuongdev89.
 * @project yii2-backup
 * @author  Phuong
 * @email   notteen[at]gmail.com
 * @date    1/24/2019
 * @time    10:17 AM
 */

/** @var MysqlBackup[] $databases */

use phuongdev89\backup\components\MysqlBackup;
use phuongdev89\backup\models\BackupConfig;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

?>
<div class="row">
	<?php $form = ActiveForm::begin([
		'id' => 'nava-backup-database',
	]) ?>
	<div class="col-sm-12">
		<div class="form-group">
			<label class="control-label" for="backup_config-database">Enable</label>
			<div class="row">
				<div class="col-sm-3">
					<?= Html::dropDownList(BackupConfig::TYPE_DATABASE . '[enable]', BackupConfig::isDatabaseEnable(), [
						BackupConfig::STATUS_DISABLE => 'Disable',
						BackupConfig::STATUS_ENABLE  => 'Enable',
					], [
						'class' => 'form-control',
					]) ?>
				</div>
			</div>
		</div>
	</div>
	<hr>
	<div class="col-sm-12">
		<?php foreach ($databases as $database) : ?>
			<legend>Database <?= $database->getName() ?></legend>
			<div class="table-responsive">
				<table class="table table-hover">
					<thead>
					<tr>
						<th>
							<?= Html::checkbox('schema_all', true, ['id' => 'table-all']) ?>
							<label for="table-all">Table name</label>
						</th>
						<th>
							<?= Html::checkbox('schema_all', true, ['id' => 'schema-all']) ?>
							<label for="schema-all">Schema</label>
						</th>
						<th>
							<?= Html::checkbox('data_all', true, ['id' => 'data-all']) ?>
							<label for="data-all">Data</label>
						</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($database->getTables() as $table) : ?>
						<tr>
							<td><?= $table ?></td>
							<td data-checkbox="schema"><?= Html::checkbox(BackupConfig::TYPE_DATABASE . '[data][' . $database->getName() . '][' . $table . '][schema]', BackupConfig::getDatabase($database->getName(), $table, 'schema')) ?></td>
							<td data-checkbox="data"><?= Html::checkbox(BackupConfig::TYPE_DATABASE . '[data][' . $database->getName() . '][' . $table . '][data]', BackupConfig::getDatabase($database->getName(), $table, 'data')) ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endforeach; ?>
		<div class="col-sm-12" style="margin: 10px;">
			<button type="submit" class="btn btn-primary">Save</button>
		</div>
	</div>
	<?php ActiveForm::end(); ?>
</div>
<script>
	$(document).on('change', '#schema-all', function() {
		let th = $(this);
		if($(this).is(':checked')) {
			th.closest('table').find('[data-checkbox="schema"]').find('input[type="checkbox"]').prop('checked', true);
		} else {
			th.closest('table').find('[data-checkbox="schema"]').find('input[type="checkbox"]').prop('checked', false);
		}
	});
	$(document).on('change', '#data-all', function() {
		let th = $(this);
		if($(this).is(':checked')) {
			th.closest('table').find('[data-checkbox="data"]').find('input[type="checkbox"]').prop('checked', true);
		} else {
			th.closest('table').find('[data-checkbox="data"]').find('input[type="checkbox"]').prop('checked', false);
		}
	});
	$(document).on('change', '#table-all', function() {
		let th = $(this);
		if($(this).is(':checked')) {
			th.closest('table').find('[data-checkbox="data"]').find('input[type="checkbox"]').prop('checked', true);
			th.closest('table').find('[data-checkbox="schema"]').find('input[type="checkbox"]').prop('checked', true);
		} else {
			th.closest('table').find('[data-checkbox="data"]').find('input[type="checkbox"]').prop('checked', false);
			th.closest('table').find('[data-checkbox="schema"]').find('input[type="checkbox"]').prop('checked', false);
		}
	});
</script>
