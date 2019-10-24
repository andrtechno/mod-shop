<?php

namespace panix\mod\shop\components;

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
                    //if(is_array($val)){
                    //     $val = implode(',',$val);
                    // }
                    $parts[] = $key . '/' . $val;
                }
                $url .= '/' . implode('/', $parts);
            }
            return $this->index . '/' . $url . $this->suffix;
        }
        return false;
    }


    public function parseRequest($manager, $request)
    {

        $params = [];
        $pathInfo = $request->getPathInfo();

        if (empty($pathInfo))
            return false;

        if ($this->suffix)
            $pathInfo = strtr($pathInfo, [$this->suffix => '']);


        foreach ($this->getAllPaths() as $path) {

            if ($path[$this->alias] !== '' && strpos(str_replace($this->index . '/', '', $pathInfo), $path[$this->alias]) === 0) {
                $_GET['slug'] = $path[$this->alias];

                $parts = explode('/', $pathInfo);
                $paramsList = array_chunk($parts, 2);
                unset($paramsList[0]);
                foreach ($paramsList as $k => $p) {

                    if (isset($p[0])) {

                        //if(strpos($p[1],',')){
                        //    $_GET[$p[0]] = $p[1];
                        //    $params[$p[0]] =  $p[1];
                       // }else{
                            $_GET[$p[0]] = $p[0];
                            $params[$p[0]] = $p[0];
                       // }
                        // $params[$p[0]] = strpos( $p[1],',') ? explode(',', $p[1]) : $p[1];

                    }
                }

                $params['slug'] = ltrim($path[$this->alias]);
                return [$this->route, $params];
            }
        }

        return false;
    }

}