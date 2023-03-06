<?php

namespace panix\mod\shop\api;

use Yii;
use yii\base\BootstrapInterface;
use yii\rest\UrlRule;
use panix\mod\shop\models\Category;

/**
 * Class Module
 * @package panix\mod\shop\api
 */
class Bootstrap implements BootstrapInterface
{

    public function bootstrap($app)
    {
        $rules[] = [
            'class' => UrlRule::class,
            'controller' => 'shop/product',
            'pluralize' => false,
            'extraPatterns' => [
                'GET /' => 'index',
                'GET,HEAD <id>' => 'view',
            ],
            'tokens' => ['{id}' => '<id:\\w+>']
        ];
        $rules[] = [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'shop/filter',
            'pluralize' => false,
            'extraPatterns' => [
                'GET,POST /' => 'index',
                'GET,HEAD show' => 'show',
            ],
        ];
        $rules[] = [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'shop/elastic',
            'pluralize' => false,
            'extraPatterns' => [
                'GET,POST /' => 'index',
                'GET,HEAD show' => 'show',
            ],
        ];
        $rules['shop/search'] = 'shop/default/search';

        $rules[] = [
            'class' => 'panix\mod\shop\components\rules\BaseUrlRule',
            'route' => 'shop/brand/view',
            'index' => 'brand',
            'pattern' => 'brand/<slug:[0-9a-zA-Z_\-]+>/<params:.*>'
        ];
        $rules[] = [
            'class' => 'panix\mod\shop\components\rules\BaseUrlRule',
            'route' => 'shop/brand/view',
            'index' => 'brand',
            'pattern' => 'brand/<slug:[0-9a-zA-Z_\-]+>'
        ];

        foreach ($this->getAllPaths() as $path) {

            $pattern = [];
            $pathNew = explode('/', $path);

            foreach ($pathNew as $pat) {
                $pattern[] = '[0-9a-zA-Z_\-]+';
            }
            $pattern = implode('\/', $pattern);

            $rules[] = [
                'class' => 'panix\mod\shop\components\rules\BaseUrlRule',
                'route' => 'shop/catalog/view',
                'index' => 'catalog',
                'pattern' => 'catalog/<slug:' . $path . '>/<params:.*>'
            ];
            $rules[] = [
                'class' => 'panix\mod\shop\components\rules\BaseUrlRule',
                'route' => 'shop/catalog/view',
                'index' => 'catalog',
                'pattern' => 'catalog/<slug:' . $path . '>'
            ];
        }



            $rules[] = [
                'class' => 'panix\mod\shop\components\rules\SearchUrlRule',
                //'pattern'=>'products/search',
                'route' => 'shop/search/index',
                'defaults' => ['q' => Yii::$app->request->get('q')]
            ];

            $rules[] = [
                'class' => 'panix\mod\shop\components\rules\BaseUrlRule',
                'route' => 'shop/catalog/sales',
                'index' => 'sales',
                'pattern' => 'sales/<params:.*>'
            ];
            $rules[] = [
                'class' => 'panix\mod\shop\components\rules\BaseUrlRule',
                'route' => 'shop/catalog/sales',
                'index' => 'sales',
                'pattern' => 'sales'
            ];

            $rules[] = [
                'class' => 'panix\mod\shop\components\rules\BaseUrlRule',
                'route' => 'shop/catalog/new',
                'index' => 'new',
                'pattern' => 'new/<params:.*>'
            ];
            $rules[] = [
                'class' => 'panix\mod\shop\components\rules\BaseUrlRule',
                'route' => 'shop/catalog/new',
                'index' => 'new',
                'pattern' => 'new'
            ];



        $app->urlManager->addRules(
            $rules,
            false
        );
        $app->setComponents([
            'currency' => ['class' => 'panix\mod\shop\components\CurrencyManager'],
        ]);
    }

    public function getAllPaths()
    {

        $tableName = Category::tableName();
        $dependency = new \yii\caching\DbDependency(['sql' => "SELECT MAX(updated_at) FROM {$tableName}"]);
        $allPaths = \Yii::$app->cache->get('CategoryUrlRule');
        if ($allPaths === false) {
            $items = (new \yii\db\Query())
                ->select(['id', 'full_path'])
                ->andWhere('id!=:id', [':id' => 1])
                ->from($tableName)
                ->all();

            $allPaths = [];
            foreach ($items as $item) {
                $allPaths[$item['id']] = $item['full_path'];
            }
            // Sort paths by length.
            uasort($allPaths, function ($a, $b) {
                return strlen($b) - strlen($a);
            });

            \Yii::$app->cache->set('CategoryUrlRule', $allPaths, Yii::$app->db->queryCacheDuration, $dependency);
        }

        return $allPaths;
    }

}
