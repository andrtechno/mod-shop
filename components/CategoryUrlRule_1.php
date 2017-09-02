<?php

namespace panix\mod\shop\components;

use yii\web\UrlRuleInterface;
use yii\base\Object;

class CategoryUrlRule extends Object implements UrlRuleInterface {

    public function createUrl($manager, $route, $params) {

        if ($route === 'shop/category/view') {
            if (isset($params['seo_alias'])) {
                $url = trim($params['seo_alias'], '/');
                unset($params['seo_alias']);
            } else {
                $url = '';
            }
            $parts = [];
            if (!empty($params)) {
                foreach ($params as $key => $val){
           
                $parts[] = $key . '/' . $val;

                }
                $url .= '/' . implode('/', $parts);
            }

            return $url . \Yii::$app->urlManager->suffix;
        }
        return false;
    }

    public function parseRequest($manager, $request) {

        $params = [];
        $pathInfo = $request->getPathInfo();
        if (empty($pathInfo))
            return false;

        if (\Yii::$app->urlManager->suffix)
            $pathInfo = strtr($pathInfo, array(\Yii::$app->urlManager->suffix => ''));
        //if (preg_match('%^(\w+)(/(\w+))?$%', $pathInfo, $matches)) {
        foreach ($this->getAllPaths() as $path) {
            if ($path['full_path'] !== '' && strpos($pathInfo, $path['full_path']) === 0) {
                $_GET['seo_alias'] = $path['full_path'];

                $params['seo_alias'] = ltrim($path['full_path']);
              //  var_dump($params);
                  // die;
                //// \Yii::$app->urlManager->parsePathInfo($params);
                // \Yii::$app->urlManager->parseRequest($params);
                return ['shop/category/view', $params];
            }
        }
        // check $matches[1] and $matches[3] to see
        // if they match a manufacturer and a model in the database.
        // If so, set $params['manufacturer'] and/or $params['model']
        // and return ['car/index', $params]
        // }
        return false; // this rule does not apply
    }

    protected function getAllPaths() {
        $allPaths = \Yii::$app->cache->get(__CLASS__);
        if ($allPaths === false) {
            $allPaths = (new \yii\db\Query())
                    ->select(['full_path'])
                    ->andWhere('id!=:id', [':id' => 1])
                    ->from('{{%shop_category}}')
                    ->all();


            // Sort paths by length.
            usort($allPaths, function($a, $b) {
                return strlen($b['full_path']) - strlen($a['full_path']);
             });

            \Yii::$app->cache->set(__CLASS__, $allPaths);
        }

        return $allPaths;
    }

}
