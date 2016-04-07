<?php
namespace backend\module\rbac\models;

use backend\models\User;
use yii\base\Model;
use Yii;
use common\models\Common;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username,$email,$password,$realname,$role,$mobile;
    public $isNewRecord=true;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'email', 'password', 'realname', 'role', 'mobile'], 'required'],
            [ array_keys($this->attributes), 'trim'],
            ['mobile', 'string', 'min' => 11, 'max' => 11],
            [['username', 'email', 'password', 'realname'], 'string', 'min' => 6, 'max' => 64],
            [['mobile'], 'integer'],
            ['email', 'email'],
            [['email', 'username', 'mobile'], 'unique', 'targetClass' => '\backend\models\User'],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $connection = Yii::$app->getDb();
            $transaction = $connection->beginTransaction();
            try{
                //新建用户
                $user = new User();
                $user->username = $this->username;
                $user->email = $this->email;
                $user->realname = $this->realname;
                $user->mobile = $this->mobile;
                $user->role = $this->role;
                $user->setPassword($this->password);
                $user->generateAuthKey();
                if (!$user->save()) {
                    $transaction->rollBack();
                    return false;
                }
                
                //保存当前用户角色
                $now = time();
                $rows = [];
                foreach ($this->role as $v){
                    $row = [];
                    $row['item_name'] = $v;
                    $row['user_id'] = $user->id;
                    $row['created_at'] = $now;
                    $rows[] = $row;
                }
                
                $result = $connection->createCommand()
                   ->batchInsert('{{%auth_assignment}}', array_keys($rows[0]), $rows)
                   ->execute();
                if(!$result){
                    $transaction->rollBack();
                    return false;
                }
                $transaction->commit();
                //重新加载缓存
                Common::reloadRbacCache();
                return true;
            }catch (\Exception $e){
                $transaction->rollBack();
                $this->addError('role', '系统异常');
            }
        }

        return false;
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
                'username' => '用户名',
                'realname' => '真实姓名',
                'email' => '邮件',
                'status' => '状态',
                'created_at' => '创建时间',
                'updated_at' => '更改时间',
                'password' => '密码',
                'role' => '用户角色',
                'mobile' => '手机号',
        ];
    }
}
