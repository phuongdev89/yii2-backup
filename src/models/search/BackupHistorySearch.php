<?php

namespace phuongdev89\backup\models\search;

use kartik\daterange\DateRangeBehavior;
use phuongdev89\backup\models\BackupHistory;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * BackupHistorySearch represents the model behind the search form about `phuongdev89\backup\models\BackupHistorySearch`.
 */
class BackupHistorySearch extends BackupHistory
{

    public $createTimeStart;

    public $createTimeEnd;

    public $updateTimeStart;

    public $updateTimeEnd;

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => DateRangeBehavior::class,
                'attribute' => 'created_at',
                'dateStartAttribute' => 'createTimeStart',
                'dateEndAttribute' => 'createTimeEnd',
            ],
            [
                'class' => DateRangeBehavior::class,
                'attribute' => 'updated_at',
                'dateStartAttribute' => 'updateTimeStart',
                'dateEndAttribute' => 'updateTimeEnd',
            ],
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [
                [
                    'id',
                    'size',
                    'status',
                    'mail_status',
                    'ftp_status',
                    's3_status',
                ],
                'integer',
            ],
            [
                [
                    'created_at',
                    'updated_at',
                    'name',
                    'type',
                ],
                'safe',
            ],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = BackupHistory::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'id' => $this->id,
            'size' => $this->size,
            'status' => $this->status,
            'mail_status' => $this->mail_status,
            'ftp_status' => $this->ftp_status,
            's3_status' => $this->s3_status,
        ]);
        $query->andFilterWhere([
            '>=',
            'created_at',
            $this->createTimeStart,
        ])->andFilterWhere([
            '<',
            'created_at',
            $this->createTimeEnd,
        ]);
        $query->andFilterWhere([
            '>=',
            'updated_at',
            $this->updateTimeStart,
        ])->andFilterWhere([
            '<',
            'updated_at',
            $this->updateTimeEnd,
        ]);
        $query->andFilterWhere([
            'like',
            'name',
            $this->name,
        ])->andFilterWhere([
            'like',
            'type',
            $this->type,
        ]);
        return $dataProvider;
    }
}
