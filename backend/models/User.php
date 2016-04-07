<?php
namespace backend\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use backend\module\rbac\models\Role;
use common\models\Common;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 * @property string $realname
 * @property string $mobile
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_STOP = 1;
    const STATUS_ACTIVE = 10;
    
    public $role, $password, $oldPassword;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $attributes = array_keys($this->attributes);
        array_push($attributes, 'password', 'role', 'oldPassword');
        $integers = ['id', 'status', 'created_at', 'updated_at'];
        $rules = [
            [ $integers, 'integer'],
            [ array_diff($attributes, $integers), 'trim'],
            [['username', 'email', 'realname', 'password', 'oldPassword'], 'string', 'min' => 6, 'max' => 64],
            ['email', 'email'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED, self::STATUS_STOP]],
            [['username', 'email', 'mobile'], 'unique', 'targetClass' => '\backend\models\User'],
            [['id', 'role', 'status'], 'required', 'on'=>'Supdate'],
            [['password', 'oldPassword'], 'required', 'on'=>'resetPassword'],
        ];
        
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
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
                'oldPassword' => '旧密码',
        ];
    }
    
    /**
     * 配合事务添加用户同时保存用户所属角色
     * @inheritdoc
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if(!$this->isNewRecord){
            $connection = Yii::$app->getDb();
            $transaction = $connection->beginTransaction();
            try{
                $oldRole = Role::getRoleNameByUid($this->id);
                $auth = Yii::$app->getAuthManager();
                //处理用户角色
                if(array_diff($this->role, $oldRole) || array_diff($oldRole, $this->role) ){
                    //删除用户角色
                    if(!$auth->revokeAll($this->id)){
                        $transaction->rollBack();
                        $this->addError('role', '系统异常1');
                        return false;
                    }
                    //保存当前用户角色
                    $now = time();
                    $rows = [];
                    foreach ($this->role as $v){
                        $row = [];
                        $row['item_name'] = $v;
                        $row['user_id'] = $this->id;
                        $row['created_at'] = $now;
                        $rows[] = $row;
                    }
                    
                    $result = $connection->createCommand()
                        ->batchInsert('{{%auth_assignment}}', array_keys($rows[0]), $rows)
                        ->execute();
                    if(!$result){
                        $transaction->rollBack();
                        $this->addError('role', '系统异常');
                        return false;
                    }
                }
                //修改密码
                if(!empty($this->password) || $this->status!=$this->oldAttributes['status']){
                    if(!empty($this->password)){
                        $this->setPassword($this->password);
                    }
                    if(!parent::save($runValidation, $attributeNames) ){
                        $transaction->rollBack();
                        return false;
                    }
                }
                $transaction->commit();
                Common::reloadRbacCache();
                return true;
            }catch (\Exception $e){
                $transaction->rollBack();
                $this->addError('role', '系统异常3');
                return false;
            }
        }
        
        return parent::save($runValidation, $attributeNames);
    }
    
    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['Supdate'] = ['role', 'id', 'updated_at', 'password', 'status', 'oldPassword'];
        $scenarios['resetPassword'] = ['password', 'oldPassword'];
      
        return $scenarios;
    }
    
    /**
     * 重置密码
     * @param string $runValidation
     * @param string $attributeNames
     * @return boolean
     */
    public function resetPassword($runValidation = true, $attributeNames = null)
    {
        $this->setPassword($this->password);
        return parent::save($runValidation, $attributeNames);
    }
    
}
