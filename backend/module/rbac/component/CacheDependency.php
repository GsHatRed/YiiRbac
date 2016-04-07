<?php
namespace backend\module\rbac\component;

use yii\caching\Dependency;
use Yii;

/**
 * rbac缓存依赖类 。
 * 当rbac权限发生变化的时候，会向redis缓存一个rbac修改时间，如果在
 * rbac权限模块用到缓存，可以实例化此类，然后传入set方法作为依赖参数，
 * 那么当rbac权限模块权限修改，你所写方法用的缓存也会重新加载
 * @author Yong
 */
class CacheDependency extends Dependency
{
    public $cachekey;
    
    public function init()
    {
        parent::init();
        $this->cachekey = Yii::$app->params['cacheKey']['rbac']['rbacChange'];
    }
    
    protected function generateDependencyData($cache)
    {
        return $cache->get($this->cachekey);
    }
}