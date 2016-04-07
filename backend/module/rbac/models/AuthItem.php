<?php

namespace backend\module\rbac\models;

use Yii;
use yii\base\Exception;
use common\models\Common;
use yii\db\Connection;

/**
 * This is the model class for table "auth_item".
 *
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $rule_name
 * @property string $data
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property AuthAssignment[] $authAssignments
 * @property AuthRule $ruleName
 * @property AuthItemChild[] $authItemChildren
 * @property AuthItemChild[] $authItemChildren0
 */
class AuthItem extends \yii\db\ActiveRecord
{
    public $permissions;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'auth_item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $attributes = array_keys($this->attributes);
        $integers = ['type', 'created_at', 'updated_at'];
        array_push($attributes, 'permissions');
        $rules = [
            [['name', 'description'], 'required'],
            [ $integers, 'integer'],
            [ array_diff($attributes, $integers), 'trim'],
            [['description', 'data'], 'string'],
            [['name', 'rule_name', 'description'], 'string', 'max' => 64],
            ['name', 'unique', 'targetClass' => self::className(), 'message' => 'ID已经存在'],
            ['type', 'default', 'value' => 1, 'on' => 'createRole'],
            [['created_at', 'updated_at'], 'default', 'value' => time(), 'on' => 'createRole'],
        ];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['createRole'] = ['name', 'description', 'type', 'created_at', 'updated_at', 'permissions'];
        $scenarios['updateRole'] = ['name', 'description', 'updated_at', 'permissions'];
        
        return $scenarios;
    }
    
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '角色ID',
            'type' => 'Type',
            'description' => '角色名',
            'rule_name' => '所用规则',
            'data' => 'Data',
            'created_at' => '创建时间',
            'updated_at' => '更改时间',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::className(), ['item_name' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRuleName()
    {
        return $this->hasOne(AuthRule::className(), ['name' => 'rule_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemChildren()
    {
        return $this->hasMany(AuthItemChild::className(), ['parent' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemChildren0()
    {
        return $this->hasMany(AuthItemChild::className(), ['child' => 'name']);
    }
    
    /**
     * 配合事务保存当前角色与权限
     * (non-PHPdoc)
     * @see \yii\db\BaseActiveRecord::save($runValidation, $attributeNames)
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if(!empty($this->permissions)){
            $this->permissions = explode(',', $this->permissions);
        }
        
        $connection = Yii::$app->getDb();
        $transaction = $connection->beginTransaction();
        try{
            if($this->isNewRecord){
                if(!parent::save($runValidation, $attributeNames)){
                    $transaction->rollBack();
                    return false;
                }
                
                if(!empty($this->permissions[0])){
                    $result = $this->savePermissions($connection);
                    if(!$result){
                        throw new \Exception('');
                    }
                }
            }else{
                if(!parent::save($runValidation, $attributeNames)){
                    $transaction->rollBack();
                    return false;
                }
                
                $auth = Yii::$app->getAuthManager();
                $oldPermissions = array_keys($auth->getChildren($this->name));
                if(empty($this->permissions[0])){ //移除角色所有权限
                    if($oldPermissions && !$auth->removeChildren($this)){
                        throw new \Exception('');
                    }
                }elseif(array_diff($this->permissions, $oldPermissions)
                        || array_diff($oldPermissions, $this->permissions)){
                    //修改角色权限
                    if($oldPermissions && !$auth->removeChildren($this)){
                        throw new \Exception('');
                    }
                    
                    $result = $this->savePermissions($connection);
                    if(!$result){
                        throw new \Exception('');
                    }
                }
            }
            $transaction->commit();
        }catch (Exception $e){
            $transaction->rollBack();
            $this->addError('description', '系统异常');
            return false;
        }
        
        //重新缓存
        Common::reloadRbacCache();
        return true;
    }
    
    /**
     * 保存当前对象(角色对象)菜单权限
     * @param array $permissions
     * @param Connection $connection
     */
    public function savePermissions(Connection $connection)
    {
        $rows = [];
        foreach ($this->permissions as $v){
            $row = [];
            $row['parent'] = $this->name;
            $row['child'] = $v;
            $rows[] = $row;
        }
        
        return $connection->createCommand()
           ->batchInsert('{{%auth_item_child}}', array_keys($row), $rows)->execute();  
    }
}
