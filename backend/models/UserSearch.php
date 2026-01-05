<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\User;

class UserSearch extends User
{
    // control deleted vs active
    public bool $onlyDeleted = false;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['username', 'email', 'role'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * search logic
     */
    public function search($params)
    {
        // base query
        $query = User::find();

        // deleted / non-deleted separation
        if ($this->onlyDeleted) {
            $query->andWhere(['not', ['deleted_at' => null]]);
        } else {
            $query->andWhere(['deleted_at' => null]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);

        // load filters
        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // exact filters
        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
        ]);

        // partial filters
        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'role', $this->role]);

        return $dataProvider;
    }
}
