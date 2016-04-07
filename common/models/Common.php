<?php
namespace common\models;

use Yii;
class Common
{
    /**
     * 打印函数
     * @param mixed $var
     * @param boolean $isDie
     */
    public static  function p($var, $isDie=true)
    {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
        
        if($isDie) Yii::$app->end();
    }
    
    /**
     * 重新缓存rbac模块缓存
     */
    public static  function reloadRbacCache()
    {
        $auth = Yii::$app->getAuthManager();
        $auth->invalidateCache();
        $auth->loadFromCache();
        $cache = Yii::$app->getCache();
        $cachekey = Yii::$app->params['cacheKey']['rbac']['rbacChange'];
        $letter = range('a', 'z');
        shuffle($letter);
        $value = implode('', $letter).time();
        $cache->set($cachekey, $value);
    }
    
    
    
    
    
    
    
}