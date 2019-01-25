<?php

namespace navatech\backup\models;

use yii\helpers\Json;

/**
 * This is the model class for table "backup_config".
 *
 * @property int    $id
 * @property string $name
 * @property string $value
 * @property string $type
 */
class BackupConfig extends \yii\db\ActiveRecord {

	const TYPE_CRONJOB          = 'CRONJOB';

	const TYPE_DATABASE         = 'DATABASE';

	const TYPE_DIRECTORY        = 'DIRECTORY';

	const TYPE_TRANSPORT        = 'TRANSPORT';

	const STATUS_ENABLE         = 1;

	const STATUS_DISABLE        = 0;

	const MAIL_SYSTEM_YIIMAILER = 0;

	const MAIL_SYSTEM_NAVAEMAIL = 1;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName() {
		return 'backup_config';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules() {
		return [
			[
				[
					'name',
				],
				'required',
			],
			[
				[
					'value',
					'type',
				],
				'string',
			],
			[
				['name'],
				'string',
				'max' => 255,
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels() {
		return [
			'id'    => 'ID',
			'name'  => 'Name',
			'value' => 'Value',
			'type'  => 'Type',
		];
	}

	/**
	 * @param        $name
	 *
	 * @return string
	 */
	public static function getTransport($name) {
		$model = self::findOne(['name' => $name]);
		if ($model === null) {
			$model       = new self();
			$model->name = $name;
			$model->type = BackupConfig::TYPE_TRANSPORT;
			$model->save();
		}
		return $model->value;
	}

	/**
	 * @param $db
	 * @param $table
	 * @param $type
	 *
	 * @return bool
	 */
	public static function getDatabase($db, $table, $type) {
		$model = self::findOne(['name' => 'database_config']);
		if ($model !== null) {
			try {
				$data = Json::decode($model->value);
				if (isset($data[$db])) {
					return isset($data[$db][$table]) && isset($data[$db][$table][$type]) && $data[$db][$table][$type] == 1;
				}
			} catch (\Exception $e) {
			}
		}
		return false;
	}

	/**
	 * @param $directory
	 *
	 * @return bool
	 */
	public static function getDirectory($directory) {
		$model = self::findOne(['name' => 'directory_config']);
		if ($model !== null) {
			try {
				$data = Json::decode($model->value);
				return isset($data[$directory]) && $data[$directory] == 1;
			} catch (\Exception $e) {
			}
		}
		return false;
	}

	/**
	 * @return bool
	 */
	public static function isDatabaseEnable() {
		$model = self::findOne(['name' => 'database_enable']);
		return $model !== null && (int) $model->value == 1;
	}

	/**
	 * @return bool
	 */
	public static function isDirectoryEnable() {
		$model = self::findOne(['name' => 'directory_enable']);
		return $model !== null && (int) $model->value == 1;
	}

	/**
	 * @param $name
	 * @param $type
	 *
	 * @return BackupConfig
	 */
	public static function getInstance($name, $type) {
		$model = BackupConfig::findOne(['name' => $name]);
		if ($model === null) {
			$model       = new BackupConfig();
			$model->name = $name;
			$model->type = $type;
			$model->save();
		}
		return $model;
	}

	/**
	 * @param $name
	 *
	 * @return bool
	 */
	public static function getCronjob($name) {
		$model = self::findOne(['name' => $name]);
		if ($model !== null) {
			if ($model->name == 'backupPath' && $model->value[0] == '@') {
				return \Yii::getAlias($model->value);
			}
			return $model->value;
		}
		return false;
	}
}
