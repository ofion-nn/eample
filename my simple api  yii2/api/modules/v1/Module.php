<?php

namespace api\modules\v1;

/**
 * api_v1 module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'api\modules\v1\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->controllerNamespace = 'api\modules\v1\controllers';
        // custom initialization code goes here
    }
}
