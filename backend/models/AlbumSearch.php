<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Album;

class AlbumSearch extends Album
{
    public function rules()
    {
        return [
            [['id', 'artist_id', 'cover_asset_id'], 'integer'],
            [['title', 'release_date', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = null)
    {
        $query = Album::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_ASC],
                'attributes' => [
                    'id',
                    'artist_id',
                    'title',
                    'cover_asset_id',
                    'release_date',
                    'created_at',
                ],
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'artist_id' => $this->artist_id,
            'cover_asset_id' => $this->cover_asset_id,
            'release_date' => $this->release_date,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }
}
