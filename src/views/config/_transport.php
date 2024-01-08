<?php
/**
 * Created by phuongdev89.
 * @project yii2-backup
 * @author  Phuong
 * @email   phuongdev89[at]gmail.com
 * @date    1/24/2019
 * @time    10:17 AM
 */

/**
 * @var View $this
 * @var BackupConfig $backupConfig
 */

use phuongdev89\backup\models\BackupConfig;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

?>
<?php $form = ActiveForm::begin([
    'id' => 'nava-backup-transport',
]) ?>
<div class="row">
    <div class="col-sm-4">
        <h1>Mail</h1>
        <div class="form-group">
            <label class="control-label" for="backup_config-mail-name">Enable</label>
            <?= Html::dropDownList(BackupConfig::TYPE_TRANSPORT . '[email][enable]', BackupConfig::getConfig('email_enable'), [
                BackupConfig::STATUS_DISABLE => 'Disable',
                BackupConfig::STATUS_ENABLE => 'Enable',
            ], [
                'class' => 'form-control',
                'id' => 'backup_config-mail-name',
            ]) ?>
        </div>
        <div class="form-group">
            <label class="control-label" for="backup_config-mail-to">To email</label>
            <?= Html::input('email', BackupConfig::TYPE_TRANSPORT . '[email][to]', BackupConfig::getConfig('email_to'), [
                'class' => 'form-control',
                'id' => 'backup_config-mail-to',
            ]) ?>
        </div>
        <div class="form-group">
            <label class="control-label" for="backup_config-mail-system">Integrate with?</label>
            <?= Html::dropDownList(BackupConfig::TYPE_TRANSPORT . '[email][system]', BackupConfig::getConfig('email_system'), [
                BackupConfig::MAIL_SYSTEM_YIIMAILER => 'Yii2 Mailer',
                BackupConfig::MAIL_SYSTEM_NAVAEMAIL => 'Nava Email Manager',
            ], [
                'class' => 'form-control',
                'id' => 'backup_config-mail-system',
            ]) ?>
            <div class="help-block">
                <div class="yii2-mailer"
                     style="display: <?= BackupConfig::getConfig('email_system') == BackupConfig::MAIL_SYSTEM_YIIMAILER ? 'block' : 'none' ?>;">
                    See
                    <a target="_blank"
                       href="https://www.yiiframework.com/extension/yiisoft/yii2-swiftmailer/doc/api/2.1/yii-swiftmailer-mailer">yii-swiftmailer-mailer</a>
                </div>
                <div class="nava-email-manager"
                     style="display:<?= BackupConfig::getConfig('email_system') == BackupConfig::MAIL_SYSTEM_NAVAEMAIL ? 'block' : 'none' ?>;">
                    See
                    <a target="_blank" href="https://github.com/phuongdev89/yii2-email-manager#simple-configuration">yii2-email-manager</a>
                </div>
            </div>
        </div>
        <div class="yii2-mailer"
             style="display: <?= BackupConfig::getConfig('email_system') == BackupConfig::MAIL_SYSTEM_YIIMAILER ? 'block' : 'none' ?>;">
            <div class="form-group">
                <label class="control-label" for="backup_config-mail-from">From email</label>
                <?= Html::input('email', BackupConfig::TYPE_TRANSPORT . '[email][from]', BackupConfig::getConfig('email_from'), [
                    'class' => 'form-control',
                    'id' => 'backup_config-mail-from',
                ]) ?>
            </div>
        </div>
        <div class="nava-email-manager"
             style="display:<?= BackupConfig::getConfig('email_system') == BackupConfig::MAIL_SYSTEM_NAVAEMAIL ? 'block' : 'none' ?>;">
            <div class="form-group">
                <label class="control-label" for="backup_config-mail-shortcut">Email template shortcut</label>
                <?= Html::input('text', BackupConfig::TYPE_TRANSPORT . '[email][shortcut]', BackupConfig::getConfig('email_shortcut'), [
                    'class' => 'form-control',
                    'id' => 'backup_config-mail-shortcut',
                ]) ?>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
    <div class="col-sm-4">
        <h1>FTP</h1>
        <div class="form-group">
            <label class="control-label" for="backup_config-ftp-name">Enable</label>
            <?= Html::dropDownList(BackupConfig::TYPE_TRANSPORT . '[ftp][enable]', BackupConfig::getConfig('ftp_enable'), [
                BackupConfig::STATUS_DISABLE => 'Disable',
                BackupConfig::STATUS_ENABLE => 'Enable',
            ], [
                'class' => 'form-control',
                'id' => 'backup_config-ftp-name',
            ]) ?>
        </div>
        <div class="form-group">
            <label class="control-label" for="backup_config-ftp-host">Host & port</label>
            <div class="row">
                <div class="col-sm-9">
                    <?= Html::input('text', BackupConfig::TYPE_TRANSPORT . '[ftp][host]', BackupConfig::getConfig('ftp_host'), [
                        'class' => 'form-control',
                        'id' => 'backup_config-ftp-host',
                    ]) ?>
                </div>
                <div class="col-sm-3">
                    <?= Html::input('number', BackupConfig::TYPE_TRANSPORT . '[ftp][port]', BackupConfig::getConfig('ftp_port'), [
                        'class' => 'form-control',
                        'id' => 'backup_config-ftp-port',
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
                    <?= Html::input('text', BackupConfig::TYPE_TRANSPORT . '[ftp][user]', BackupConfig::getConfig('ftp_user'), [
                        'class' => 'form-control',
                        'id' => 'backup_config-ftp-user',
                    ]) ?>
                </div>
                <div class="col-sm-6">
                    <?= Html::input('text', BackupConfig::TYPE_TRANSPORT . '[ftp][pass]', BackupConfig::getConfig('ftp_pass'), [
                        'class' => 'form-control',
                        'id' => 'backup_config-ftp-pass',
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label" for="backup_config-ftp-directory">Directory</label>
            <?= Html::input('text', BackupConfig::TYPE_TRANSPORT . '[ftp][directory]', BackupConfig::getConfig('ftp_directory'), [
                'class' => 'form-control',
                'id' => 'backup_config-ftp-directory',
                'placeholder' => '/var/www/backup',
            ]) ?>
        </div>

    </div>
    <div class="col-sm-4">
        <h1>S3</h1>
        <div class="form-group">
            <label class="control-label" for="backup_config-s3-name">Enable</label>
            <?= Html::dropDownList(BackupConfig::TYPE_TRANSPORT . '[s3][enable]', BackupConfig::getConfig('s3_enable'), [
                BackupConfig::STATUS_DISABLE => 'Disable',
                BackupConfig::STATUS_ENABLE => 'Enable',
            ], [
                'class' => 'form-control',
                'id' => 'backup_config-s3-name',
            ]) ?>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="control-label" for="backup_config-s3-endpoint">Endpoint</label>
                    <?= Html::input('text', BackupConfig::TYPE_TRANSPORT . '[s3][endpoint]', BackupConfig::getConfig('s3_endpoint'), [
                        'class' => 'form-control',
                        'id' => 'backup_config-s3-endpoint',
                        'placeholder' => 'Endpoint',
                    ]) ?>
                    <div class="help-block">
                        See <a href="https://github.com/bp-sys/yii2-aws-s3" target="_blank">yii2-aws-s3</a>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="control-label" for="backup_config-s3-bucket">Bucket</label>
                    <?= Html::input('text', BackupConfig::TYPE_TRANSPORT . '[s3][bucket]', BackupConfig::getConfig('s3_bucket'), [
                        'class' => 'form-control',
                        'id' => 'backup_config-s3-bucket',
                        'placeholder' => 'Bucket',
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="control-label" for="backup_config-s3-region">Region</label>
                    <?= Html::input('text', BackupConfig::TYPE_TRANSPORT . '[s3][region]', BackupConfig::getConfig('s3_region'), [
                        'class' => 'form-control',
                        'id' => 'backup_config-s3-region',
                        'placeholder' => 'Region',
                    ]) ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="control-label" for="backup_config-s3-path">Path</label>
                    <?= Html::input('text', BackupConfig::TYPE_TRANSPORT . '[s3][path]', BackupConfig::getConfig('s3_path'), [
                        'class' => 'form-control',
                        'id' => 'backup_config-s3-path',
                        'placeholder' => 'Path',
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="control-label" for="backup_config-s3-access_key">Access key</label>
                    <?= Html::input('text', BackupConfig::TYPE_TRANSPORT . '[s3][access_key]', BackupConfig::getConfig('s3_access_key'), [
                        'class' => 'form-control',
                        'placeholder' => 'Access key',
                        'id' => 'backup_config-s3-access_key',
                    ]) ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="control-label" for="backup_config-s3-secret_key">Secret key</label>
                    <?= Html::input('text', BackupConfig::TYPE_TRANSPORT . '[s3][secret_key]', BackupConfig::getConfig('s3_secret_key'), [
                        'class' => 'form-control',
                        'placeholder' => 'Secret key',
                        'id' => 'backup_config-s3-secret_key',
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
<script>
    $(document).on("change", '#backup_config-mail-system', function () {
        let th = $(this);
        if (th.children('option:selected').val() == 0) {
            $(".yii2-mailer").show();
            $(".nava-email-manager").hide();
        } else {
            $(".yii2-mailer").hide();
            $(".nava-email-manager").show();
        }
    });
</script>
