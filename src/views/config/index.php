<?php
/**
 * Created by Navatech.
 * @project Default (Template) Project
 * @author  Phuong
 * @email   notteen[at]gmail.com
 * @date    1/24/2019
 * @time    10:03 AM
 */
/* @var $this \yii\web\View */
/* @var $module Module */
/* @var $databases MysqlBackup[] */

/* @var $directories array */

use kartik\tabs\TabsX;
use navatech\backup\components\MysqlBackup;
use navatech\backup\Module;

$this->title                   = 'Backup configure';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="navatech-setting">
	<div class="box">
		<!-- /.box-header -->
		<div class="box-body">
			<div class="col-sm-12">
				<?= TabsX::widget([
					'items'        => [
						[
							'label'   => 'Transport setting',
							'content' => $this->render('_transport'),
						],
						[
							'label'   => 'Database setting',
							'visible' => isset($module->backup[Module::TYPE_DATABASE]) && $databases != null,
							'content' => $this->render('_database', ['databases' => $databases]),
						],
						[
							'label'   => 'Directory setting',
							'visible' => isset($module->backup[Module::TYPE_DIRECTORY]),
							'content' => $this->render('_directory', ['directories' => $directories]),
						],
					],
					'bordered'     => true,
					'position'     => TabsX::POS_ABOVE,
					'encodeLabels' => false,
				]); ?>
			</div>
		</div>
	</div>
	<!-- /.box-body -->
</div>
