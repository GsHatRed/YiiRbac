<?php

namespace backend\module\rbac\models;

use Yii;
use backend\module\rbac\models\AuthMenuSearch;
use common\models\Common;
use yii\base\Exception;

/**
 * This is the model class for table "auth_menu".
 *
 * @property integer $id
 * @property integer $pid
 * @property string $name
 * @property integer $created_at
 * @property integer $level
 * @property string $uri
 * @property integer $sort
 */
class AuthMenu extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'auth_menu';
    }
    
    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if($this->isNewRecord)
             $this->created_at = time();
        
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $attributes = array_keys($this->attributes);
        $integers = ['pid', 'created_at', 'level', 'sort'];
        $rules = [
            [['name', 'level'], 'required'],
            [ $integers, 'integer'],
            [ array_diff($attributes, $integers), 'trim'],  
            [['name', 'uri'], 'string', 'max' => 64],
            ['pid', 'required','when' => function($model) {
                return $model->level>1;
            }],
            ['uri', 'required','when' => function($model) {
                $model->uri = strtolower($model->uri);
                return $model->level>2;
            }],
        ];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pid' => '父级菜单',
            'name' => '菜单名称',
            'created_at' => '创建时间',
            'level' => '菜单级别',
            'uri' => '菜单路由',
            'sort' => $this->scenario=='uc'?'排序（从大到小排序）':'排序',
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['uc'] = array_keys($this->attributes);

        return $scenarios;
    }
//     public function fields()
//     {
//         return [
//                 'level',
//                 'pid',
//                 'name',
//                 'action',
//         ];
//     }

    /**
     * 配合事务保存菜单同时保存权限
     * @inheritdoc
     */
    public function save($runValidation = true, $attributeNames = NULL)
    {
        $connection = Yii::$app->getDb(); 
        $auth = Yii::$app->getAuthManager();
        if($this->id>0 && $this->id == $this->pid){
            $this->addError('pid', '不可以将自己修改为父级菜单');
            return false;
        }
        //禁止修改含有子级菜单的菜单的菜单级别
        elseif( !$this->isNewRecord && $this->oldAttributes['level'] != $this->level
            && !empty(AuthMenuSearch::getByPid($this->id)) ){
            $this->addError('level', '含有子级菜单的不可以修改菜单级别');
            return false;
        }
        //三四级菜单添加修改
        elseif(($this->isNewRecord || $this->oldAttributes['level']>=3) && $this->level>=3){
            if($this->uri=='null'){
                $this->addError('uri', '路由不能为null');
                return false;
            }
            $transaction = $connection->beginTransaction();
            try {
                if($this->isNewRecord){
                    $newMenu = $auth->createPermission($this->uri);
                    $newMenu->description = $this->name;
                    $auth->add($newMenu);
                }else{
                    if($this->oldAttributes['uri'] != $this->uri && $this->uri!='null'){
                        $connection->createCommand()->update('auth_item', [
                               'name' => $this->uri,
                               'updated_at' => time(),
                               'description' => $this->name
                        ], ['name' => $this->oldAttributes['uri']])->execute();
                    }
                }

                if(!parent::save($runValidation, $attributeNames)){
                    throw new Exception('');
                }
                
                $transaction->commit();
            }catch (Exception $e){
                $transaction->rollBack();
                if(strpos($e->getMessage(), '1062')!==false && stripos($e->getMessage(), 'Duplicate')!==false){
                    $this->addError('uri', '该路由已经存在');
                }
                return false;
            }
        }
        //一二级菜单转换三四级菜单
        elseif(!$this->isNewRecord && $this->oldAttributes['level']<3 &&  $this->level>=3 ){
            $transaction = $connection->beginTransaction();
            try {
                $newMenu = $auth->createPermission($this->uri);
                $newMenu->description = $this->name;
                $auth->add($newMenu);
                
                if(!parent::save($runValidation, $attributeNames)){
                    throw new Exception('');
                }
                
                $transaction->commit();
            }catch (Exception $e){
                $transaction->rollBack();
                if(strpos($e->getMessage(), '1062')!==false && stripos($e->getMessage(), 'Duplicate')!==false){
                    $this->addError('uri', '该路由已经存在');
                }
                return false;
            }
        }
        //三四级转换一二级
        elseif(!$this->isNewRecord && $this->oldAttributes['level']>=3 && $this->level<3){
            $transaction = $connection->beginTransaction();
            try {
                $this->pid = $this->level==1? 0 : $this->pid;
                $oldUri = $this->oldAttributes['uri'];
                $this->uri = 'null';
                if(!parent::save($runValidation, $attributeNames))
                    return false;
                
                $permission = $auth->createPermission($oldUri);
                if(!$auth->remove($permission)){
                    throw new Exception('');
                }
                $transaction->commit();
            }catch (Exception $e){
                $transaction->rollBack();
                return false;
            }
        }
        //以下为一二级菜单添加修改
        else{
            $this->uri = 'null';
            $this->pid = $this->level==1? 0 : $this->pid;
            if(!parent::save($runValidation, $attributeNames))
                return false;
        }
        
        //重新缓存
        Common::reloadRbacCache();
        return true;
    }
    
}
