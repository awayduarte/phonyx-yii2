<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Artist;

class ArtistSearch extends Artist
{
    public function rules()
    {
        return [
            [['id', 'user_id', 'avatar_asset_id'], 'integer'],
            [['stage_name', 'bio', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = null)
    {
        $query = Artist::find()
            ->alias('artist')
            ->joinWith(['user u'])
            ->where(['artist.deleted_at' => null]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_ASC],
                'attributes' => [
                    'id',
                    'stage_name',
                    'created_at',
                    'updated_at',

                    'user.username' => [
                        'asc'  => ['u.username' => SORT_ASC],
                        'desc' => ['u.username' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'artist.id' => $this->id,
            'artist.user_id' => $this->user_id,
            'artist.avatar_asset_id' => $this->avatar_asset_id,
            'artist.created_at' => $this->created_at,
            'artist.updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'artist.stage_name', $this->stage_name])
            ->andFilterWhere(['like', 'artist.bio', $this->bio])
            ->andFilterWhere(['like', 'u.username', $this->getAttribute('user.username')]);

        return $dataProvider;
    }
}
