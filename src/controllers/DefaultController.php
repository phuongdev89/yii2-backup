<?php
namespace navatech\backup\controllers;

use yii\web\Controller;

/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/30/16
 * Time: 3:03 PM
 */
class DefaultController extends Controller {

	public $defaultAction = 'index';

	public function actionIndex() {
		return $this->render('index');
	}
}