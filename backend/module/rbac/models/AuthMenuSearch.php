<?php

namespace backend\module\rbac\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\module\rbac\models\AuthMenu;
use backend\module\rbac\component\CacheDependency;
use yii\helpers\Url;

/**
 * AuthMenuSearch represents the model behind the search form about `backend\module\rbac\models\AuthMenu`.
 */
class AuthMenuSearch extends AuthMenu
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $attributes = array_keys($this->attributes);
        $integers = ['id', 'pid', 'created_at', 'level'];
        $rules = [
            [ $integers, 'integer'],
            [ array_diff($attributes, $integers), 'trim'],
            [['name', 'uri', 'id', 'pid', 'created_at', 'level'], 'safe'],
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
            'sort' => [
                'defaultOrder' => [
                    'level' => SORT_ASC,
                    'pid' => SORT_ASC,
                    'sort' => SORT_DESC,
                    'created_at' => SORT_DESC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->filterWhere([
            'id' => $this->id,
            'pid' => $this->pid,
            'created_at' => $this->created_at,
            'level' => $this->level,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'uri', $this->uri]);

        return $dataProvider;
    }
    
    /**
     * 获取某一级菜单
     * @param integer $level
     * @param array $select
     * @param boolean $cached
     * @return array
     */
    static  public function getLevelMenu($level=0, $select=['id', 'name'], $cached=true)
    {
        $return = [];
        if(!$level || !is_numeric($level)){
            return $return ;
        }elseif ($cached){
            $data = self::getAllMenu();
            $return = $data[$level-1];
            $oldSelect = array_keys(current($return));
            $diffSelect = array_diff($oldSelect, $select) || array_diff($select, $oldSelect);
            if(!$diffSelect)
                return $return;
        }
        
        $return = self::find()
           ->select($select)
           ->indexBy('id')
           ->where(['level'=>$level])
           ->orderBy(['pid' => SORT_ASC, 'sort' => SORT_DESC, 'created_at' => SORT_DESC])
           ->asArray()
           ->all();
        
        return $return;
    }
    
    /**
     * 获取全部菜单
     * @param   boolean $cached 缓存设置
     * @return  array
     */
    static public function getAllMenu($cached=true)
    {
        $return   = [];
        $cache    = Yii::$app->getCache();
        $cachekey = Yii::$app->params['cacheKey']['AuthMenuSearch']['allMenu'];
        if($cached && $cache->get($cachekey))
            return $cache->get($cachekey);
        
        $level1 = self::getLevelMenu($level=1, $select=['id', 'name'], false);
        $level2 = self::getLevelMenu($level=2, $select=['id', 'name', 'pid'], false);
        $level3 = self::getLevelMenu($level=3, $select=['id', 'name', 'pid','uri'], false);
        $level4 = self::getLevelMenu($level=4, $select=['id', 'name', 'pid','uri'], false);
        $return = [ $level1, $level2, $level3, $level4 ];
        
        if($cached){
            $dependency = new CacheDependency();
            $cache->set($cachekey, $return, 0, $dependency);
        }
        
        return $return;
    }
    
    /**
     * 根据pid获取子级菜单
     * @param integer $pid 父id
     * @param array $select 选择字段
     * @return array
     */
    static public function getByPid($pid=0, $select=['id', 'name', 'uri'])
    {
        $return = [];
        if(!$pid || !is_numeric($pid))
            return $return;
    
        $return = self::find()->select($select)->where(['pid' => $pid])->all();
        return $return;
    }
    
    /**
     * 根据id获取一条记录
     * @param   integer $id id
     * @param   array $select 选择字段
     * @param   boolean $cached
     * @return  array
     */
    static public function getByid($id=0, $select=['id', 'name', 'uri'], $cached=true)
    {
        $return = [];
        $data = self::getAllMenu();
        if(!$id || !is_numeric($id))
            return $return;
        elseif($cached && $data){
            $data = $data[0]+$data[1]+$data[2]+$data[3];
            $return = $data[$id];
            return $return;
        }
      
        $return = self::find()
            ->select($select)
            ->where(['id' => $id])
            ->one();
        
        return $return;
    }
    
    /**
     * 获取菜单列表
     * @return array
     */
    static  public function getMenuList()
    {
        $return = [];
        $user = Yii::$app->getUser();
        list ($level1, $level2, $level3) = self::getAllMenu();
        foreach ($level3 as $v){
            if(!$user->can($v['uri'])){
                continue;
            }
            $items = [];
            $items['id'] = $v['id'];
            $items['text'] = $v['name'];
            $items['href'] = Url::to([ '/' . $v['uri']]);
            $level2[$v['pid']]['items'][] = $items ;
        }
        
        foreach ($level2 as $k=>$v){
            $menu = [];
            if(empty($v['items']))
                continue;
            $menu['collapsed'] = true;
            $menu['text'] = $v['name'];
            $menu['items'] = $v['items'];
            $level1[$v['pid']]['menu'][] = $menu;
        }
      
        foreach ($level1 as $k=>$v){
            if(empty($v['menu']))
                continue;
            $return[] = $v;
        }
        
        return $return;
    }
    
    /**
     * 获取某角色所有权限
     * @return array
     */
    static  public function getPemissions($role='')
    {
        $auth = Yii::$app->getAuthManager();
        $rolePermissions = $auth->getChildren($role);
        $return = [];
        list ($level1, $level2, $level3, $level4) = self::getAllMenu();
        foreach ($level4 as $v){
            $action = [];
            if(!empty($role) && !empty($rolePermissions[$v['uri']])){
                $action['state'] = ['checked'=>true];
                $level3[$v['pid']]['state'] = ['checked'=>true];
            }
            $action['text'] = $v['name'];
            $action['href'] = $v['uri'];
            $action['level'] = 4;
            $level3[$v['pid']]['nodes'][] = $action ;
        }
        
        foreach ($level3 as $v){
            $item = [];
            if(!empty($role) && !empty($rolePermissions[$v['uri']])){
                $item['state'] = ['checked'=>true];
                $level2[$v['pid']]['state'] = ['checked'=>true];
                $level1[$level2[$v['pid']]['pid']]['state'] = ['checked'=>true];
            }elseif(!empty($v['state'])){
                $item['state'] = $v['state'];
                $level2[$v['pid']]['state'] = ['checked'=>true];
                $level1[$level2[$v['pid']]['pid']]['state'] = ['checked'=>true];
            }
            $item['text'] = $v['name'];
            $item['href'] = $v['uri'];
            $item['level'] = 3;
            if(!empty($v['nodes']))
                $item['nodes'] = $v['nodes'];
            $level2[$v['pid']]['nodes'][] = $item ;
        }
        
        foreach ($level2 as $k=>$v){
            $menu = [];
            if(empty($v['nodes']))
                continue;
            elseif(!empty($v['state'])){
                $menu['state'] = $v['state'];
            }
            $menu['text'] = $v['name'];
            $menu['nodes'] = $v['nodes'];
            $level1[$v['pid']]['nodes'][] = $menu;
        }
    
        foreach ($level1 as $k=>$v){
            if(empty($v['nodes']))
                continue;
            $v['text'] = $v['name'];
            $v['state']['expanded'] = false;
            unset($v['name']);
            $return[] = $v;
        }

        return $return;
    }
    
}
