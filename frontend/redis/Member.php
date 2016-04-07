<?php
namespace frontend\redis;

use yii\redis\ActiveRecord;

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
class Member extends ActiveRecord
{
    public function rules()
    {
        return [ 
             [array_keys($this->attributes)  , 'trim' ]
        ];
    }
    
    
    
    public function attributes()
    {
        return [
                'id', 'username', 'userpwd', 'nickname', 'tel', 'qq', 'sex', 'age', 'regtime', 
                'lastlogin', 'desc', 'integral', 'grade', 'regtype', 'picurl', 'email'
        ];
    }
    
    
}