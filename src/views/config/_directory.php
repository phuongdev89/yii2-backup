<?php
/**
 * Created by Navatech.
 * @project yii2-backup
 * @author  Phuong
 * @email   notteen[at]gmail.com
 * @date    1/24/2019
 * @time    10:17 AM
 */

/** @var array $directories */

use navatech\backup\helpers\FileHelper;
use navatech\backup\models\BackupConfig;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

?>
<div class="row">
	<?php $form = ActiveForm::begin([
		'id' => 'nava-backup-directory',
	]) ?>
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
						<td><?= Html::checkbox(BackupConfig::TYPE_DIRECTORY . '[' . $directory['name'] . ']', true) ?></td>
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
