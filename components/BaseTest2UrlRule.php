<?php

namespace panix\mod\shop\components;

use panix\engine\CMS;
use Yii;
use yii\helpers\Url;
use yii\web\HttpException;
use yii\web\UrlRule;

class BaseTest2UrlRule extends UrlRule
{

    public $pattern = 'brand/<slug:[0-9a-zA-Z\-]+>';
    public $cacheDuration = 0;
    public $index = 'brand';
    public $alias = 'slug';
    public $query;

    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        if ($route === $this->route) {
            if (isset($params['slug'])) {
                $url = trim($params['slug'], '/');
                unset($params['slug']);
            } else {
                $url = '';
            }
            $parts = [];
            if (!empty($params)) {
                foreach ($params as $key => $val) {
                    if (!is_array($val)) {
                        $parts[] = $key . '/' . $val;
                    }
                }
                $url .= '/' . implode('/', $parts);
            }
            return $this->index  . $url . $this->suffix;
        }
        return false;
    }


    public function parseRequest($manager, $request)
    {

        $params = [];
        $pathInfo = $request->getPathInfo();

        $basePathInfo = $pathInfo;
        if (empty($pathInfo))
            return false;

        if ($this->suffix)
            $pathInfo = strtr($pathInfo, [$this->suffix => '']);


        $pathInfoParse = str_replace($this->index . '/', '', $pathInfo);
        $parts = explode('/', $pathInfoParse);
        if ($this->index == mb_substr($pathInfo, 0,strlen($this->index))) {
            $paramsList = array_chunk($parts, 2);
            //CMS::dump($paramsList);die;
            foreach ($paramsList as $k => $p) {
                if (isset($p[1]) && isset($p[0])) {
                    $_GET[$p[0]] = $p[1];
                    $params[$p[0]] = $p[1];
                }
            }

            /*if (isset($params[$this->index])) {
                $params['slug'] = $params[$this->index];
                $_GET['slug'] = $params['slug'];
                unset($params[$this->index]);
            } else {
                return false;
            }*/

          //  CMS::dump([$this->route, $params]);
          //  die;
            return [$this->route, $params];
        }
        return false;
    }

}
