<?php
/**
 * Created by phuongdev89.
 * @project Default (Template) Project
 * @author  Phuong
 * @email   phuongdev89[at]gmail.com
 * @date    1/24/2019
 * @time    10:03 AM
 */

/**
 * @var $this View
 * @var $module Module
 * @var $databases MysqlBackup[]
 * @var $directories array
 */

use kartik\tabs\TabsX;
use phuongdev89\backup\components\MysqlBackup;
use phuongdev89\backup\Module;
use yii\web\View;

$this->title = 'Backup configure';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="phuongdev89-setting">
    <div class="box">
        <!-- /.box-header -->
        <div class="box-body">
            <div class="col-sm-12">
                <?= TabsX::widget([
                    'items' => [
                        [
                            'label' => 'Cronjob setting',
                            'content' => $this->render('_cronjob'),
                        ],
                        [
                            'label' => 'Transport setting',
                            'content' => $this->render('_transport'),
                        ],
                        [
                            'label' => 'Database setting',
                            'visible' => $module->databases != null && $databases != null,
                            'content' => $this->render('_database', ['databases' => $databases]),
                        ],
                        [
                            'label' => 'Directory setting',
                            'visible' => $module->directories != null && $directories != null,
                            'content' => $this->render('_directory', ['directories' => $directories]),
                        ],
                    ],
                    'bordered' => true,
                    'position' => TabsX::POS_ABOVE,
                    'encodeLabels' => false,
                ]); ?>
            </div>
        </div>
    </div>
    <!-- /.box-body -->
</div>
