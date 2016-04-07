<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * @author Yong
 */
class ApplicationAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $jsOptions = ['position' => View::POS_HEAD];
    public $css = [
        'css/bootstrap-responsive.css',
        'css/style.css',
        'select2/css/select2.min.css',
        'date/css/bootstrap-datetimepicker.min.css',
    ];
    public $js = [
        //'js/jquery.sorted.js',
        //'js/bootstrap.js',
        //'js/ckform.js',
        //'js/common.js',
        'select2/js/select2.full.min.js',
        'select2/js/select2.min.js',
        'js/bootstrap-treeview.min.js',
        'date/js/bootstrap-datetimepicker.min.js',
        'js/customer.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
