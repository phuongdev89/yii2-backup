<?php
/**
 * Created by Navatech.
 * @project yii2-backup
 * @author  Phuong
 * @email   notteen[at]gmail.com
 * @date    1/24/2019
 * @time    10:17 AM
 */

/** @var BackupConfig $backupConfig */

use navatech\backup\models\BackupConfig;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

?>
<div class="row">
	<div class="col-sm-12">
		<?php $form = ActiveForm::begin([
			'id' => 'nava-backup-cronjob',
		]) ?>
		<legend>Cronjob</legend>
		<div class="form-group">
			<label class="control-label" for="backup_config-cronjob">Store backed-up path</label>
			<?= Html::input('text', BackupConfig::TYPE_CRONJOB . '[backupPath]', BackupConfig::getCronjob('backupPath'), [
				'class' => 'form-control',
				'id'    => 'backup_config-cronjob-backupPath',
			]) ?>
		</div>
		<div class="form-group">
			<label class="control-label" for="backup_config-cronjob">Clean old backup after days</label>
			<div class="row">
				<div class="col-sm-3">
					<?= Html::input('number', BackupConfig::TYPE_CRONJOB . '[cleanAfterDays]', BackupConfig::getCronjob('cleanAfterDays'), [
						'class' => 'form-control',
						'id'    => 'backup_config-cronjob-cleanAfterDays',
					]) ?>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label" for="backup_config-mail-to">Run backup every</label>
			<div class="row">
				<div class="col-sm-3">
					<?= Html::dropDownList(BackupConfig::TYPE_CRONJOB . '[runEvery]', BackupConfig::getCronjob('runEvery'), [
						'Minutes' => [
							900  => 'Every 15 minutes',
							1800 => 'Every 30 minutes',
							2700 => 'Every 45 minutes',
						],
						'Hourly'  => [
							3600  => 'Every hour',
							7200  => 'Every 2 hours',
							10800 => 'Every 3 hours',
							21600 => 'Every 6 hours',
							32400 => 'Every 9 hours',
							43200 => 'Every 12 hours',
						],
					], [
						'class' => 'form-control',
						'id'    => 'backup_config-cronjob-runEvery',
					]) ?>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label" for="backup_config-cronjob">Command</label>
			<?= Html::input('text', BackupConfig::TYPE_CRONJOB . '[command]', '*/5 * * * * cd ' . dirname(Yii::getAlias('@app')) . ' && php yii backup/daemon', [
				'class'    => 'form-control',
				'readonly' => true,
				'id'       => 'backup_config-cronjob-command',
				'onclick'  => 'this.select()',
			]) ?>
			<div class="help-block">Please copy above command to your crontab.</div>
		</div>
		<button type="submit" class="btn btn-primary">Save</button>
		<?php ActiveForm::end(); ?>
	</div>
</div>
