<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Track;

class TrackSearch extends Track
{
    public function rules()
    {
        return [
            [['id', 'artist_id', 'album_id', 'audio_asset_id'], 'integer'],
            [['title'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = null)
    {
        $query = Track::find();

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
                    'album_id',
                    'title',
                    'audio_asset_id',
                ],
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // exact match filters
        $query->andFilterWhere([
            'id' => $this->id,
            'artist_id' => $this->artist_id,
            'album_id' => $this->album_id,
            'audio_asset_id' => $this->audio_asset_id,
        ]);

        // like filters
        $query->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }
}
