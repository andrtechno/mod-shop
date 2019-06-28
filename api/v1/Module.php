<?php

namespace panix\mod\shop\api\v1;

use Yii;
use yii\helpers\VarDumper;

/**
 * Class Module
 * @package panix\mod\shop\api\v1
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'panix\mod\shop\api\v1\controllers';

    public function init()
    {

        //VarDumper::dump(Yii::$app,10,true);die;
        parent::init();
    }
}
