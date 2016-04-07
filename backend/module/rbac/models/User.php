<?php

namespace backend\module\rbac\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\User as UserModel;

/**
 * User represents the model behind the search form about `backend\models\User`.
 */
class User extends UserModel
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $attributes = array_keys($this->attributes);
        array_push($attributes, 'role');
        $integers = ['id', 'status', 'created_at', 'updated_at'];
        $rules = [
            [ $integers, 'integer'],
            [ array_diff($attributes, $integers), 'trim'],
            [['username', 'auth_key', 'password_hash', 'password_reset_token', 'email', 'realname', 'mobile', 'role'], 'safe'],
        ];
        return $rules;
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
        $query = self::find();
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        $ids = self::getUidsByDesKword($this->role);
        if($this->id || ($this->role && !$ids))
            array_push($ids, $this->id);
        
        $query->filterWhere([
            'id' => $ids,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['like', 'mobile', $this->mobile])
            ->andFilterWhere(['like', 'realname', $this->realname])
            ->andFilterWhere(['like', 'email', $this->email]);

        return $dataProvider;
    }
    
    /**
     * 根据角色描述关键词获取用户id
     * @param string $keyword
     */
    public static  function getUidsByDesKword($keyword='')
    {
        $return = [];
        if(empty($keyword) || !is_string($keyword)){
            return $return;
        }
        
        $return = self::find()
            ->select('a.`user_id`')
            ->from(['{{%auth_assignment}} AS a', '{{%auth_item}} AS b'])
            ->where(['like', 'b.`description`', $keyword])
            ->andWhere('a.`item_name`=b.`name` AND b.`type`=1')
            ->groupBy('a.`user_id`')
            ->asArray()
            ->column();
        
        return $return;
    }
}
