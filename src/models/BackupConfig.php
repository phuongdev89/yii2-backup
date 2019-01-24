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
}
