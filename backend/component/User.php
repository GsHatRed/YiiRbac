<?php
namespace backend\component;


use backend\module\rbac\models\Role;
class User extends \yii\web\User
{
    /**
     * @var array | string
     * 超级管理员
     */
    public $admin;
    
    public function can($permissionName, $params = [], $allowCaching = true)
    {
        if(!$this->isGuest && $this->isAdmin()){
            return true;
        }
        
        return parent::can($permissionName, $params, $allowCaching);
    }
    
    /**
     * 检验当前用户是否为超级管理员
     * @param User $user the current user
     * @return boolean
     */
    protected function isAdmin()
    {
        $roles  = Role::getRoleNameByUid($this->getId());
        if(is_array($this->admin)){
            $count  = count($roles);
            $errand = count(array_diff($roles, $this->admin));
            if($count!==0 && $count>$errand){
                return true;
            }
        }elseif(is_string($this->admin) && in_array($this->admin, $roles)){
            return true;
        }
    
        return false;
    }
}