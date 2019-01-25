<?php
/**
 * Created by Navatech.
 * @project yii2-backup
 * @author  Phuong
 * @email   notteen[at]gmail.com
 * @date    1/22/2019
 * @time    3:37 PM
 */

namespace navatech\backup\models;

use kartik\daterange\DateRangeBehavior;
use navatech\backup\helpers\FileHelper;
use navatech\backup\Module;
use yii\base\Model;
use yii\data\ArrayDataProvider;

class Backup extends Model {

	public $id;

	public $name;

	public $size;

	public $created_at;

	public $modified_at;

	public $type;

	public $createTimeStart;

	public $createTimeEnd;

	public $modifyTimeStart;

	public $modifyTimeEnd;

	public function behaviors() {
		return [
			[
				'class'              => DateRangeBehavior::class,
				'attribute'          => 'created_at',
				'dateStartAttribute' => 'createTimeStart',
				'dateEndAttribute'   => 'createTimeEnd',
			],
			[
				'class'              => DateRangeBehavior::class,
				'attribute'          => 'modified_at',
				'dateStartAttribute' => 'modifyTimeStart',
				'dateEndAttribute'   => 'modifyTimeEnd',
			],
		];
	}

	/**
	 * In this example we keep this special property to know if columns should be
	 * filtered or not. See search() method below.
	 */
	private $_filtered = false;

	/**
	 * @return array
	 */
	public function rules() {
		return [
			[
				[
					'name',
					'size',
					'type',
				],
				'string',
			],
			[
				[
					'id',
				],
				'integer',
			],
			[
				[
					'created_at',
					'modified_at',
				],
				'match',
				'pattern' => '/^.+\s\-\s.+$/',
			],
		];
	}

	/**
	 * This method returns ArrayDataProvider.
	 * Filtered and sorted if required.
	 *
	 * @param $params
	 *
	 * @return ArrayDataProvider
	 */
	public function search($params) {
		if ($this->load($params) && $this->validate()) {
			$this->_filtered = true;
		}
		return new ArrayDataProvider([
			'allModels' => $this->getData(),
			'sort'      => [
				'attributes'   => [
					'created_at',
					'size',
					'name',
					'modified_at',
					'type',
				],
				'defaultOrder' => ['created_at' => SORT_DESC],
			],
		]);
	}

	/**
	 * Here we are preparing the data source and applying the filters
	 * if _filtered property is set to true.
	 */
	protected function getData() {
		/**@var Module $module */
		$module    = \Yii::$app->getModule('backup');
		$list      = FileHelper::findFiles(BackupConfig::getCronjob('backupPath'));
		$dataArray = [];
		foreach ($list as $id => $filename) {
			$columns         = [];
			$columns['id']   = $id;
			$columns['name'] = basename($filename);
			if (strpos(basename($filename), Module::TYPE_DIRECTORY) !== false) {
				$columns['type'] = Module::TYPE_DIRECTORY;
			} else {
				$columns['type'] = Module::TYPE_DATABASE;
			}
			$columns['size']        = filesize($filename);
			$columns['created_at']  = filectime($filename);
			$columns['modified_at'] = filemtime($filename);
			$dataArray[]            = $columns;
		}
		if ($this->_filtered) {
			$dataArray = array_filter($dataArray, function($value) {
				$conditions = [true];
				if (!empty($this->name)) {
					$conditions[] = strpos($value['name'], $this->name) !== false;
				}
				if (!empty($this->size)) {
					$conditions[] = strpos($value['size'], $this->size) !== false;
				}
				if (!empty($this->created_at)) {
					$conditions[] = $value['created_at'] >= $this->createTimeStart;
					$conditions[] = $value['created_at'] < $this->createTimeEnd;
				}
				if (!empty($this->modified_at)) {
					$conditions[] = $value['modified_at'] >= $this->modifyTimeStart;
					$conditions[] = $value['modified_at'] < $this->modifyTimeEnd;
				}
				if (!empty($this->type)) {
					$conditions[] = strpos($value['type'], $this->type) !== false;
				}
				return array_product($conditions);
			});
		}
		return $dataArray;
	}
}
