<?php

namespace common\modules\page\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class PageAsset extends AssetBundle
{
    public $sourcePath = '@backend/assets';
    public $css = [

    ];
    public $js = [
        'modules/page_module/page_module.js',
    ];
    public $depends = [
        'backend\assets\AppAsset'
    ];
}
