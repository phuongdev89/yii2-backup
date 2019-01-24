<?php

namespace navatech\backup\models;

/**
 * This is the model class for table "backup_history_queue".
 *
 * @property int           $id
 * @property int           $backup_history_id
 * @property int           $status
 * @property string        $description
 *
 * @property BackupHistory $backupHistory
 */
class BackupHistoryQueue extends \yii\db\ActiveRecord {

	/**
	 * {@inheritdoc}
	 */
	public static function tableName() {
		return 'backup_history_queue';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules() {
		return [
			[
				[
					'backup_history_id',
					'description',
				],
				'required',
			],
			[
				[
					'backup_history_id',
					'status',
				],
				'integer',
			],
			[
				['description'],
				'string',
			],
			[
				['backup_history_id'],
				'unique',
			],
			[
				['backup_history_id'],
				'exist',
				'skipOnError'     => true,
				'targetClass'     => BackupHistory::className(),
				'targetAttribute' => ['backup_history_id' => 'id'],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels() {
		return [
			'id'                => 'ID',
			'backup_history_id' => 'Backup History ID',
			'status'            => 'Status',
			'description'       => 'Description',
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getBackupHistory() {
		return $this->hasOne(BackupHistory::className(), ['id' => 'backup_history_id']);
	}
}
