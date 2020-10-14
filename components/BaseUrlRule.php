<?php

namespace panix\mod\shop\components;

use panix\engine\CMS;
use Yii;
use yii\web\HttpException;
use yii\web\UrlRule;

class BaseUrlRule extends UrlRule
{

    public $pattern = 'manufacturer/<slug:[0-9a-zA-Z\-]+>';
    public $cacheDuration = 0;
    public $index = 'manufacturer';
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
                if (!empty($url))
                    $url .= '/' . implode('/', $parts);
            }
            return $this->index . '/' . $url . $this->suffix;
            // return $url . $this->suffix;
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

        if (method_exists($this, 'getAllPaths')) {
            foreach ($this->getAllPaths() as $path) {
                $pathInfo = str_replace($this->index . '/', '', $pathInfo);
                //$pathInfo = $pathInfo;
                if ($path[$this->alias] !== '' && strpos($pathInfo, $path[$this->alias]) === 0) {

                    $params['slug'] = ltrim($path[$this->alias]);
                    $_GET['slug'] = $params['slug'];

                    $pathInfo = ltrim(substr($basePathInfo, strlen($this->index . '/' . $path[$this->alias])), '/');
                    //$pathInfo = ltrim(substr($basePathInfo, strlen($path[$this->alias])), '/');

                    $parts = explode('/', $pathInfo);
                    $paramsList = array_chunk($parts, 2);

                    foreach ($paramsList as $k => $p) {
                        if (isset($p[1]) && isset($p[0])) {
                            $_GET[$p[0]] = $p[1];
                            $params[$p[0]] = $p[1];
                        }
                    }

                    return [$this->route, $params];
                }
            }
        } else {
            return [$this->route, $params];
        }
        return false;
    }

}