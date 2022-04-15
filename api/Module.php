<?php

namespace panix\mod\shop\api;

use Yii;
use yii\base\BootstrapInterface;
use yii\helpers\VarDumper;
use yii\rest\UrlRule;

/**
 * Class Module
 * @package panix\mod\shop\api
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    public $controllerNamespace = 'panix\mod\shop\api\controllers';

    public function bootstrap($app)
    {
        $rules[] = [
            'class' => UrlRule::class,
            'controller' => 'shop/api/country',
            'pluralize' => false,
             'prefix'=>'api',
            //'extraPatterns' => [
            //    'GET new' => 'new',
           // ],
            // 'patterns' => [
            //     'GET,HEAD' => 'new'
            // ]
            //'tokens' => [
            //     '{id}' => '<id:\\w+>'
            // ]
        ];


        $app->urlManager->addRules(
            $rules,
            false
        );
    }

    public function init()
    {
        parent::init();
    }
}
