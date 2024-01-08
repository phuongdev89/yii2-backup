<?php
/**
 * Created by phuongdev89.
 * @project yii2-backup
 * @author  Phuong
 * @email   phuongdev89[at]gmail.com
 * @date    1/24/2019
 * @time    10:17 AM
 */

/** @var array $directories */

use phuongdev89\backup\helpers\FileHelper;
use phuongdev89\backup\models\BackupConfig;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<div class="row">
    <?php $form = ActiveForm::begin([
        'id' => 'nava-backup-directory',
    ]) ?>
    <div class="col-sm-12">
        <div class="form-group">
            <label class="control-label" for="backup_config-directory">Enable</label>
            <div class="row">
                <div class="col-sm-3">
                    <?= Html::dropDownList(BackupConfig::TYPE_DIRECTORY . '[enable]', BackupConfig::isDirectoryEnable(), [
                        BackupConfig::STATUS_DISABLE => 'Disable',
                        BackupConfig::STATUS_ENABLE => 'Enable',
                    ], [
                        'class' => 'form-control',
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="col-sm-12">
        <legend>Directory</legend>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>
                    </th>
                    <th>
                        <label>Directory name</label>
                    </th>
                    <th>
                        <label>Number of files</label>
                    </th>
                    <th>
                        <label>Total size</label>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($directories as $directory) : ?>
                    <tr>
                        <td><?= Html::checkbox(BackupConfig::TYPE_DIRECTORY . '[data][' . $directory['name'] . ']', BackupConfig::getDirectory($directory['name'])) ?></td>
                        <td><?= $directory['name'] ?></td>
                        <td><?= $directory['count'] ?></td>
                        <td><?= FileHelper::humanFileSize($directory['size']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-sm-12" style="margin: 10px;">
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
    <?php ActiveForm::end(); ?>
</div>
