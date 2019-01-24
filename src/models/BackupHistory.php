<?php

namespace navatech\backup\models;

/**
 * This is the model class for table "backup_history".
 *
 * @property int                $id
 * @property string             $name
 * @property int                $size
 * @property int                $created_at
 * @property int                $modified_at
 * @property string             $type
 * @property int                $status
 * @property int                $mail_status
 * @property int                $ftp_status
 * @property int                $s3_status
 *
 * @property BackupHistoryQueue $backupHistoryQueue
 */
class BackupHistory extends \yii\db\ActiveRecord {

	/**
	 * {@inheritdoc}
	 */
	public static function tableName() {
		return 'backup_history';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules() {
		return [
			[
				[
					'name',
					'type',
				],
				'required',
			],
			[
				[
					'size',
					'created_at',
					'modified_at',
					'status',
					'mail_status',
					'ftp_status',
					's3_status',
				],
				'integer',
			],
			[
				['type'],
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
			'id'          => 'ID',
			'name'        => 'Name',
			'size'        => 'Size',
			'created_at'  => 'Created At',
			'modified_at' => 'Modified At',
			'type'        => 'Type',
			'status'      => 'Status',
			'mail_status' => 'Mail Status',
			'ftp_status'  => 'Ftp Status',
			's3_status'   => 'S3 Status',
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getBackupHistoryQueue() {
		return $this->hasOne(BackupHistoryQueue::className(), ['backup_history_id' => 'id']);
	}
}
