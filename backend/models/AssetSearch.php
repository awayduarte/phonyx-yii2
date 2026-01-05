<?php

namespace backend\models;

use yii\data\ActiveDataProvider;
use yii\db\Expression;
use common\models\Asset;

class AssetSearch extends Asset
{
    public $used_count;

    public function rules()
    {
        return [
            [['id', 'used_count'], 'integer'],
            [['path', 'type'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return parent::scenarios();
    }

    public function search($params)
    {
        $query = Asset::find()
            ->select([
                'asset.*',
                new Expression('
                    (
                        (SELECT COUNT(*) FROM user WHERE profile_asset_id = asset.id) +
                        (SELECT COUNT(*) FROM artist WHERE avatar_asset_id = asset.id) +
                        (SELECT COUNT(*) FROM album WHERE cover_asset_id = asset.id) +
                        (SELECT COUNT(*) FROM playlist WHERE cover_asset_id = asset.id) +
                        (SELECT COUNT(*) FROM track WHERE audio_asset_id = asset.id)
                    ) AS used_count
                ')
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id',
                    'type',
                    'path',
                    'used_count' => [
                        'asc' => ['used_count' => SORT_ASC],
                        'desc' => ['used_count' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['asset.id' => $this->id])
            ->andFilterWhere(['asset.type' => $this->type])
            ->andFilterWhere(['like', 'asset.path', $this->path]);

        return $dataProvider;
    }
}
