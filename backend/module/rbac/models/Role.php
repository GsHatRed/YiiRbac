<?php

namespace backend\module\rbac\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\module\rbac\models\AuthItem;
//use backend\module\rbac\component\CacheDependency;


/**
 * Role represents the model behind the search form about `backend\module\rbac\models\AuthItem`.
 */
class Role extends AuthItem
{
    private $type = 1;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [ array_keys($this->attributes), 'trim'],
            [['type'], 'integer'],
            [['updated_at', 'created_at'], 'filter', 'filter'=>function ($value){
                return strtotime($value); }, 'skipOnEmpty'=>true],
            [['name', 'description', 'rule_name', 'data', 'updated_at', 'created_at'], 'safe'],
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
        $query = AuthItem::find();

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
            'type' => $this->type,
            //'created_at' => $this->created_at,
            //'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'rule_name', $this->rule_name])
            ->andFilterWhere(['like', 'data', $this->data])
            ->andFilterWhere(['>', 'created_at', $this->created_at])
            ->andFilterWhere(['<', 'updated_at', $this->updated_at]);

        return $dataProvider;
    }
    
    /**
     * 获取所有角色描述
     * @return array
     */
    static public function getDeses()
    {
        $return = [];
        $auth = Yii::$app->getAuthManager();
        $data = $auth->getRoles();
        foreach ($data as $v){
            $return[ $v->name ] = $v->description;
        }
        return $return;
    }
    
    /**
     * 获取某用户所有角色描述
     * @param int $userId 
     * @param boolean $cached
     * @return array
     */
    static public function getDesByUid($userId, $cached=true)
    { 
        $return = [];
        $cache = Yii::$app->getCache();
        $cachekey = Yii::$app->params['cacheKey']['role']['uRoleDes'].'_'.$userId;
        if(!is_numeric($userId) || !$userId){
            return $return;
        }elseif ($cached && $cache->get($cachekey)){
            return $cache->get($cachekey);
        }
        
        $query = static::find();
        $query->select('item_name,user_id')
            ->from('{{%auth_assignment}}')
            ->where(['user_id'=> $userId])
            ->asArray();
        $userRoles = [];
        $roleNames = [];
        foreach ($query->all() as $v){
            if(empty($userRoles[$v['user_id']])){
                $userRoles[$v['user_id']] = [];
                $userRoles[$v['user_id']][$v['item_name']] = true;
            }else{
                $userRoles[$v['user_id']][$v['item_name']] = true;
            }
            if(empty($roleNames[ $v['item_name'] ])){
                $roleNames[$v['item_name']] = $v['item_name'];
            }
        }
        
        $roles = $query->select('name, description')
            ->from('{{%auth_item}}')
            ->where(['name'=>$roleNames])
            ->asArray()
            ->indexBy('name')
            ->all();
        foreach ($userRoles as $k=>$v){
            foreach ($v as $kk=>$vv){
                $return[$k][$kk] = $roles[$kk]['description'];
            }
        }
        
        if ($cached){
            $dependency = new \backend\module\rbac\component\CacheDependency();
            $cache->set($cachekey, $return, 0, $dependency);
        }
        
        return  $return;
    }
    
    /**
     * 获取用户所有角色name值
     * @param int $userId
     * @param boolean $cached
     * @return array
     */
    static public function getRoleNameByUid($userId, $cahed=true)
    {
        $cahe = Yii::$app->getCache();
        $cahekey = Yii::$app->params['cacheKey']['role']['uRoleNames'].'_'.$userId;
        if(!$userId || !is_numeric($userId)){
            return [];
        }elseif($cahed && $cahe->get($cahekey)){
            return $cahe->get($cahekey);
        }
        
        $role = Yii::$app->getAuthManager()->getAssignments($userId) ;
        $role = array_keys($role);
        if($role && $cahed){
            $dependency = new \backend\module\rbac\component\CacheDependency();
            $cahe->set($cahekey, $role, 0, $dependency);
        }
        return $role;
    }
}
