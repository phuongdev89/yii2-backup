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
<?php $form = ActiveForm::begin([
	'id' => 'nava-backup-transport',
]) ?>
<div class="row">
	<div class="col-sm-4">
		<legend>Mail</legend>
		<div class="form-group">
			<label class="control-label" for="backup_config-mail-name">Enable</label>
			<?= Html::dropDownList(BackupConfig::TYPE_TRANSPORT . '[email][enable]', BackupConfig::getTransport('email_enable'), [
				BackupConfig::STATUS_DISABLE => 'Disable',
				BackupConfig::STATUS_ENABLE  => 'Enable',
			], [
				'class' => 'form-control',
				'id'    => 'backup_config-mail-name',
			]) ?>
		</div>
		<div class="form-group">
			<label class="control-label" for="backup_config-mail-to">To email</label>
			<?= Html::input('email', BackupConfig::TYPE_TRANSPORT . '[email][to]', BackupConfig::getTransport('email_to'), [
				'class' => 'form-control',
				'id'    => 'backup_config-mail-to',
			]) ?>
		</div>
		<div class="form-group">
			<label class="control-label" for="backup_config-mail-system">Integrate with?</label>
			<?= Html::dropDownList(BackupConfig::TYPE_TRANSPORT . '[email][system]', BackupConfig::getTransport('email_system'), [
				BackupConfig::MAIL_SYSTEM_YIIMAILER => 'Yii2 Mailer',
				BackupConfig::MAIL_SYSTEM_NAVAEMAIL => 'Nava Email Manager',
			], [
				'class' => 'form-control',
				'id'    => 'backup_config-mail-system',
			]) ?>
			<div class="help-block">
				<div class="yii2-mailer" style="display: <?= BackupConfig::getTransport('email_system') == BackupConfig::MAIL_SYSTEM_YIIMAILER ? 'block' : 'none' ?>;">
					See
					<a target="_blank" href="https://www.yiiframework.com/extension/yiisoft/yii2-swiftmailer/doc/api/2.1/yii-swiftmailer-mailer">yii-swiftmailer-mailer</a>
				</div>
				<div class="nava-email-manager" style="display:<?= BackupConfig::getTransport('email_system') == BackupConfig::MAIL_SYSTEM_NAVAEMAIL ? 'block' : 'none' ?>;">
					See
					<a target="_blank" href="https://github.com/navatech/yii2-email-manager#simple-configuration">yii2-email-manager</a>
				</div>
			</div>
		</div>
		<div class="yii2-mailer" style="display: <?= BackupConfig::getTransport('email_system') == BackupConfig::MAIL_SYSTEM_YIIMAILER ? 'block' : 'none' ?>;">
			<div class="form-group">
				<label class="control-label" for="backup_config-mail-from">From email</label>
				<?= Html::input('email', BackupConfig::TYPE_TRANSPORT . '[email][from]', BackupConfig::getTransport('email_from'), [
					'class' => 'form-control',
					'id'    => 'backup_config-mail-from',
				]) ?>
			</div>
		</div>
		<div class="nava-email-manager" style="display:<?= BackupConfig::getTransport('email_system') == BackupConfig::MAIL_SYSTEM_NAVAEMAIL ? 'block' : 'none' ?>;">
			<div class="form-group">
				<label class="control-label" for="backup_config-mail-shortcut">Email template shortcut</label>
				<?= Html::input('text', BackupConfig::TYPE_TRANSPORT . '[email][shortcut]', BackupConfig::getTransport('email_shortcut'), [
					'class' => 'form-control',
					'id'    => 'backup_config-mail-shortcut',
				]) ?>
			</div>
		</div>
		<button type="submit" class="btn btn-primary">Save</button>
	</div>
	<div class="col-sm-4">
		<legend>FTP</legend>
		<div class="form-group">
			<label class="control-label" for="backup_config-ftp-name">Enable</label>
			<?= Html::dropDownList(BackupConfig::TYPE_TRANSPORT . '[ftp][enable]', BackupConfig::getTransport('ftp_enable'), [
				BackupConfig::STATUS_DISABLE => 'Disable',
				BackupConfig::STATUS_ENABLE  => 'Enable',
			], [
				'class' => 'form-control',
				'id'    => 'backup_config-ftp-name',
			]) ?>
		</div>
		<div class="form-group">
			<label class="control-label" for="backup_config-ftp-host">Host & port</label>
			<div class="row">
				<div class="col-sm-9">
					<?= Html::input('text', BackupConfig::TYPE_TRANSPORT . '[ftp][host]', BackupConfig::getTransport('ftp_host'), [
						'class' => 'form-control',
						'id'    => 'backup_config-ftp-host',
					]) ?>
				</div>
				<div class="col-sm-3">
					<?= Html::input('number', BackupConfig::TYPE_TRANSPORT . '[ftp][port]', BackupConfig::getTransport('ftp_port'), [
						'class' => 'form-control',
						'id'    => 'backup_config-ftp-port',
					]) ?>
				</div>
			</div>
			<div class="help-block">
				See <a href="https://github.com/yii2mod/yii2-ftp" target="_blank">yii2-ftp</a>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label" for="backup_config-ftp-user">User & Pass</label>
			<div class="row">
				<div class="col-sm-6">
					<?= Html::input('text', BackupConfig::TYPE_TRANSPORT . '[ftp][user]', BackupConfig::getTransport('ftp_user'), [
						'class' => 'form-control',
						'id'    => 'backup_config-ftp-user',
					]) ?>
				</div>
				<div class="col-sm-6">
					<?= Html::input('text', BackupConfig::TYPE_TRANSPORT . '[ftp][pass]', BackupConfig::getTransport('ftp_pass'), [
						'class' => 'form-control',
						'id'    => 'backup_config-ftp-pass',
					]) ?>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label" for="backup_config-ftp-directory">Directory</label>
			<?= Html::input('text', BackupConfig::TYPE_TRANSPORT . '[ftp][directory]', BackupConfig::getTransport('ftp_directory'), [
				'class'       => 'form-control',
				'id'          => 'backup_config-ftp-directory',
				'placeholder' => '/var/www/backup',
			]) ?>
		</div>

	</div>
	<div class="col-sm-4">
		<legend>S3</legend>
		<div class="form-group">
			<label class="control-label" for="backup_config-s3-name">Enable</label>
			<?= Html::dropDownList(BackupConfig::TYPE_TRANSPORT . '[s3][enable]', BackupConfig::getTransport('s3_enable'), [
				BackupConfig::STATUS_DISABLE => 'Disable',
				BackupConfig::STATUS_ENABLE  => 'Enable',
			], [
				'class' => 'form-control',
				'id'    => 'backup_config-s3-name',
			]) ?>
		</div>
		<div class="form-group">
			<label class="control-label" for="backup_config-s3-endpoint">Endpoint & bucket</label>
			<div class="row">
				<div class="col-sm-6">
					<?= Html::input('text', BackupConfig::TYPE_TRANSPORT . '[s3][endpoint]', BackupConfig::getTransport('s3_endpoint'), [
						'class' => 'form-control',
						'id'    => 'backup_config-s3-endpoint',
					]) ?>
				</div>
				<div class="col-sm-6">
					<?= Html::input('text', BackupConfig::TYPE_TRANSPORT . '[s3][bucket]', BackupConfig::getTransport('s3_bucket'), [
						'class' => 'form-control',
						'id'    => 'backup_config-s3-bucket',
					]) ?>
				</div>
			</div>
			<div class="help-block">
				See <a href="https://github.com/tpyo/amazon-s3-php-class" target="_blank">amazon-s3-php-class</a>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label" for="backup_config-s3-access_key">Access key</label>
			<?= Html::input('text', BackupConfig::TYPE_TRANSPORT . '[s3][access_key]', BackupConfig::getTransport('s3_access_key'), [
				'class' => 'form-control',
				'id'    => 'backup_config-s3-access_key',
			]) ?>
		</div>
		<div class="form-group">
			<label class="control-label" for="backup_config-s3-secret_key">Secret key</label>
			<?= Html::input('text', BackupConfig::TYPE_TRANSPORT . '[s3][secret_key]', BackupConfig::getTransport('s3_secret_key'), [
				'class' => 'form-control',
				'id'    => 'backup_config-s3-secret_key',
			]) ?>
		</div>
	</div>
</div>
<?php ActiveForm::end(); ?>
<script>
	$(document).on("change", '#backup_config-mail-system', function() {
		let th = $(this);
		if(th.children('option:selected').val() == 0) {
			$(".yii2-mailer").show();
			$(".nava-email-manager").hide();
		} else {
			$(".yii2-mailer").hide();
			$(".nava-email-manager").show();
		}
	});
</script>
