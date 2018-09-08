<?php

namespace panix\mod\shop;


use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{

    public function bootstrap($app)
    {
        $app->getUrlManager()->addRules([
            // ['class' => 'yii\web\UrlRule', 'pattern' => $this->id, 'route' => $this->id . '/default/index'],
            // ['class' => 'yii\web\UrlRule', 'pattern' => $this->id . '/<id:\w+>', 'route' => $this->id . '/default/view'],
            // [
            // 'class' => 'yii\web\UrlRule',
            // 'pattern' => $this->id . '/<controller:(ajax)>/<action:\w+>',
            // 'route' => $this->id . '/<controller>/<action>'
            // ],
            [
                // 'class' => 'yii\web\UrlRule',
                'pattern' => 'shop/<controller:\w+>/<action:\w+>/<id:\d+>',
                'route' => 'shop/<controller>/<action>'
            ],
        ], false);
    }
}