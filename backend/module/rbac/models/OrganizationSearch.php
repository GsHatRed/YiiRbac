<?php

namespace backend\module\rbac\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\module\rbac\models\Organization;

/**
 * OrganizationSearch represents the model behind the search form about `backend\module\rbac\models\Organization`.
 */
class OrganizationSearch extends Organization
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $attributes = array_keys($this->attributes);
        return [
            [ $attributes, 'trim'],
            [['id', 'status'], 'integer'],
            [['created_at'], 'filter', 'filter'=>function ($value){
                return strtotime($value); }, 'skipOnEmpty'=>true],
            [ $attributes, 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Organization::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
            //'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
             ->andFilterWhere(['>', 'created_at', $this->created_at]);

        return $dataProvider;
    }
}
