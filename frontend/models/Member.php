<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "member".
 *
 * @property integer $id
 * @property string $username
 * @property string $userpwd
 * @property string $nickname
 * @property string $tel
 * @property string $qq
 * @property integer $sex
 * @property integer $age
 * @property string $regtime
 * @property string $lastlogin
 * @property string $desc
 * @property integer $integral
 * @property string $grade
 * @property integer $regtype
 * @property string $picurl
 * @property string $email
 */
class Member extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'member';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'userpwd', 'regtime'], 'required'],
            [['sex', 'age', 'integral', 'regtype'], 'integer'],
            [['regtime', 'lastlogin'], 'safe'],
            [['username', 'userpwd'], 'string', 'max' => 50],
            [['nickname', 'tel', 'grade'], 'string', 'max' => 30],
            [['qq'], 'string', 'max' => 20],
            [['desc'], 'string', 'max' => 300],
            [['picurl', 'email'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'userpwd' => 'Userpwd',
            'nickname' => 'Nickname',
            'tel' => 'Tel',
            'qq' => 'Qq',
            'sex' => 'Sex',
            'age' => 'Age',
            'regtime' => 'Regtime',
            'lastlogin' => 'Lastlogin',
            'desc' => 'Desc',
            'integral' => 'Integral',
            'grade' => 'Grade',
            'regtype' => 'Regtype',
            'picurl' => 'Picurl',
            'email' => 'Email',
        ];
    }
}
