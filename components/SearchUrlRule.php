<?php

namespace panix\mod\shop\components;

use yii\web\UrlRule;

/**
 * Class SearchUrlRule
 * @package panix\mod\shop\components
 */
class SearchUrlRule extends UrlRule
{

    public $pattern = 'products/search/q/<q:\w+>';
    public $route = 'shop/category/search';

    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {

        if ($route === $this->route) {

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
            return $url . $this->suffix;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function parseRequest($manager, $request)
    {
        $params = [];
        $pathInfo = $request->getPathInfo();

        if (empty($pathInfo))
            return false;

        if (strpos($pathInfo, 'products/search') !== 0) {
            return false;
        }

        if ($this->suffix)
            $pathInfo = strtr($pathInfo, [$this->suffix => '']);

        $pathInfo = str_replace('products/search', '', $pathInfo);

        $parts = explode('/', $pathInfo);

        unset($parts[0]);

        $ss = array_chunk($parts, 2);

        foreach ($ss as $k => $p) {
            $_GET[$p[0]] = $p[1];
            $params[$p[0]] = $p[1];
        }


        return [$this->route, $params];
    }

}
