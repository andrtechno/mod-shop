<?php

namespace panix\mod\shop\components;

use yii\web\UrlRule;
use yii\web\UrlRuleInterface;

class SearchUrlRule extends UrlRule implements UrlRuleInterface
{

    public $pattern = 'products/search/q/<q:\w+>';
    public $route = 'shop/category/search';

    public function createUrl($manager, $route, $params)
    {

        if ($route === 'shop/category/search') {

            /*if (isset($params['q'])) {
                $url = trim($params['q'], '/');
                unset($params['q']);
            } else {
                $url = '';
            }*/
            $url = 'products/search';
            //$url='';
            $parts = [];
            if (!empty($params)) {
                foreach ($params as $key => $val) {
                    $parts[] = $key . '/' . $val;
                }
                $url .= '/' . implode('/', $parts);
            }


            return $url . \Yii::$app->urlManager->suffix;
        }

        return false;
    }



    public function parseRequest($manager, $request)
    {
        $params = [];
        $pathInfo = $request->getPathInfo();

        if (empty($pathInfo))
            return false;


        if (\Yii::$app->urlManager->suffix)
            $pathInfo = strtr($pathInfo, array(\Yii::$app->urlManager->suffix => ''));

        $pathInfo = str_replace('products/search', '', $pathInfo);

        $parts = explode('/', $pathInfo);

        unset($parts[0]);
        // print_r($parts);die;
        $ss = array_chunk($parts, 2);
        foreach ($ss as $k => $p) {
            $_GET[$p[0]] = $p[1];
            $params[$p[0]] = $p[1];
        }



        return ['shop/category/search', $params];
    }

}
