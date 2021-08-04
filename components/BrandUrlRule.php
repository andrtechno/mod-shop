<?php

namespace panix\mod\shop\components;

use panix\mod\shop\models\Brand;
use yii\web\NotFoundHttpException;
use yii\web\UrlRule;

/**
 * Class BrandUrlRule
 * @package panix\mod\shop\components
 */
class BrandUrlRule extends UrlRule
{

    public $pattern = 'brand/<slug:[0-9a-zA-Z\-]+>';
    public $cacheDuration = 0;
    public $index = 'brand';
    public $alias = 'slug';
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
                    if($val)
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

        $basePathInfo= $pathInfo;
        if (empty($pathInfo))
            return false;

        if ($this->suffix)
            $pathInfo = strtr($pathInfo, [$this->suffix => '']);


        foreach ($this->getAllPaths() as $path) {
            $pathInfo = str_replace($this->index . '/', '', $pathInfo);
            if ($path[$this->alias] !== '' && strpos($pathInfo, $path[$this->alias]) === 0) {

                $params['slug'] = ltrim($path[$this->alias]);
                $_GET['slug'] = $params['slug'];

                $pathInfo = ltrim(substr($basePathInfo, strlen($this->index.'/'.$path[$this->alias])), '/');

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

        return false;
    }
    public function getAllPaths()
    {
        $allPaths = \Yii::$app->cache->get('BrandUrlRule');
        if ($allPaths === false) {
            $allPaths = (new \yii\db\Query())
                ->select([$this->alias])
                ->from(Brand::tableName())
                ->all();

            // Sort paths by length.
            usort($allPaths, function ($a, $b) {
                return strlen($b[$this->alias]) - strlen($a[$this->alias]);
            });

            \Yii::$app->cache->set('BrandUrlRule', $allPaths, 1);
        }

        return $allPaths;
    }

}
