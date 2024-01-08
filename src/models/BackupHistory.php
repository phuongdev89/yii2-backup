<?php

namespace phuongdev89\backup\models;

use Throwable;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;

/**
 * This is the model class for table "backup_history".
 *
 * @property int $id
 * @property string $name
 * @property int $size
 * @property int $created_at
 * @property int $updated_at
 * @property string $type
 * @property string $data
 * @property int $status
 * @property int $mail_status
 * @property int $ftp_status
 * @property int $s3_status
 *
 */
class BackupHistory extends ActiveRecord
{

    const TYPE_DATABASE = 'DATABASE';

    const TYPE_DIRECTORY = 'DIRECTORY';

    const STATUS_DONE = 1;

    const STATUS_DRAFT = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'backup_history';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
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
                    'updated_at',
                    'status',
                    'mail_status',
                    'ftp_status',
                    's3_status',
                ],
                'integer',
            ],
            [
                [
                    'type',
                    'data',
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
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'size' => 'Size',
            'created_at' => 'Created At',
            'updated_at' => 'Modified At',
            'type' => 'Type',
            'status' => 'Status',
            'mail_status' => 'MailTransport Status',
            'ftp_status' => 'FtpTransport Status',
            's3_status' => 'S3 Status',
        ];
    }

    /**
     * @return false|int
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function delete()
    {
        $path = BackupConfig::getCronjob('backupPath');
        @unlink($path . DIRECTORY_SEPARATOR . $this->name);
        return parent::delete();
    }
}
