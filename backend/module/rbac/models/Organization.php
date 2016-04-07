<?php

namespace backend\module\rbac\models;

use Yii;

/**
 * This is the model class for table "organization".
 *
 * @property integer $id
 * @property string $name
 * @property integer $status
 * @property integer $created_at
 */
class Organization extends \yii\db\ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_STOP    = 1;
    const STATUS_ACTIVE  = 10;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'organization';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $attributes = array_keys($this->attributes);
        $integers = ['id', 'status', 'created_at'];
        $rules =  [
            [['name', 'status'], 'required'],
            [ array_keys($this->attributes), 'trim'],
            [ $integers, 'integer'],
            [['name'], 'string', 'max' => 60],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['created_at', 'default', 'value' => time()],
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
            'name' => '机构名称',
            'status' => '状态',
            'created_at' => '创建时间',
        ];
    }
}
