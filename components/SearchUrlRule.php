<?php

namespace panix\mod\shop\components;

use Yii;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\UrlRule;

/**
 * Class SearchUrlRule
 * @package panix\mod\shop\components
 */
class SearchUrlRule extends UrlRule
{

    public $pattern = 'search/q/<q:\w+>';
    public $route = 'shop/search/index';

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
            $url = 'search';
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

        if (strpos($pathInfo, 'search') !== 0) {
            return false;
        }

        if ($this->suffix)
            $pathInfo = strtr($pathInfo, [$this->suffix => '']);

        $pathInfo = str_replace('search', '', $pathInfo);

        $parts = explode('/', $pathInfo);

        unset($parts[0]);

        $partsList = array_chunk($parts, 2);

        foreach ($partsList as $k => $p) {
            if (isset($p[1])) {
                $_GET[$p[0]] = $p[1];
                $params[$p[0]] = $p[1];
            } else {
                throw new NotFoundHttpException(Yii::t('app/error', '404'));
            }
        }


        return [$this->route, $params];
    }

}
