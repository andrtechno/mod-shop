<?php

namespace panix\mod\shop\api\v1;

use Yii;
use yii\base\BootstrapInterface;
use yii\helpers\VarDumper;
use yii\rest\UrlRule;

/**
 * Class Module
 * @package panix\mod\shop\api\v1
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
   // public $controllerNamespace = 'panix\mod\shop\api\v1\controllers';

    public function bootstrap($app)
    {

       // $rules[] = ['GET,POST shop/filter/index' => 'shop/filter/index'];
        /*$rules[] =  [
            'class' => yii\rest\UrlRule::class,
            'controller' => 'shop/country',
            // 'pluralize'=>false,
            //'prefix'=>'api',
            'extraPatterns' => [
                'GET /new' => 'new',
                'POST /login'=>'login',
            ],
            'tokens' => [
                '{id}' => '<id:\\w+>'
            ]

        ];*/
        $rules[] = [
            'class' => UrlRule::class,
            'controller' => 'shop/product',
            //'pluralize' => false,
            'extraPatterns' => [
                'GET /index' => 'index',
                'GET /view' => 'view',
            ],
            'tokens' => [
                '{id}' => '<id:\\w+>'
            ]

        ];
        $rules[] = [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'shop/filter',
            //'pluralize' => false,
            'extraPatterns' => [
                'GET,POST /' => 'index',
            ],

        ];
        $app->urlManager->addRules(
            $rules,
            false
        );


        /*$app->getUrlManager()->addRules([
            [
                'class' => 'yii\web\UrlRule',
                'route' => 'shop/<controller>/<action>',
                'pattern' => 'shop/<controller:[\w\-]+>/<action:[\w\-]+>',
                'normalizer' => false,
                'suffix' => false
            ]
        ], false);*/
    }

}
