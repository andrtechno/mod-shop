<?php

namespace panix\mod\shop\components;

use Yii;
use panix\engine\CMS;
use yii\web\NotFoundHttpException;
use panix\mod\shop\models\Category;

/**
 * Class CategoryUrlRule
 * @package panix\mod\shop\components
 */
class CategoryUrlRule extends BaseUrlRule
{
    private $all_paths;
    // public $pattern = '';
    /*public $route = 'shop/category/view';
    public $pattern = '';
    public $cacheDuration = 0;
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
                    $parts[] = $key . '/' . $val;
                }
                $url .= '/' . implode('/', $parts);
            }

            return $url . $this->suffix;
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

            if ($path['full_path'] !== '' && strpos($pathInfo, $path['full_path']) === 0) {
                $_GET['slug'] = $path['full_path'];
                $uri = str_replace($path['full_path'], '', $pathInfo);
                $parts = explode('/', $uri);
                unset($parts[0]);
                //$pathInfo = implode($parts, '/');
                //   print_r(array_chunk($parts, 2));
                $ss = array_chunk($parts, 2);

                foreach ($ss as $k => $p) {
                    // print_r($p);
                    if (isset($p[1])) {
                        $_GET[$p[0]] = $p[1];
                        $params[$p[0]] = $p[1];
                    }
                }

                $params['slug'] = ltrim($path['full_path']);


                return [$this->route, $params];
            }
        }

        return false;
    }*/

    protected function getAllPaths()
    {
        $allPaths = \Yii::$app->cache->get('CategoryUrlRule');
        if ($allPaths === false) {
            $items = (new \yii\db\Query())
                ->select(['full_path'])
                ->andWhere('id!=:id', [':id' => 1])
                ->from(Category::tableName())
                ->all();

            $allPaths = [];
            foreach ($items as $item) {
                $allPaths[] = $item['full_path'];
            }

            // Sort paths by length.
            usort($allPaths, function ($a, $b) {
                return strlen($b) - strlen($a);
                // return strlen($b['full_path']) - strlen($a['full_path']);
            });


            \Yii::$app->cache->set('CategoryUrlRule', $allPaths, 1);
        }

        return $allPaths;
    }


    public function parseRequest($manager, $request)
    {


        $params = [];
        $pathInfo = $request->getPathInfo();


        $isAdmin = strpos($pathInfo, 'admin');
        if ($isAdmin === false) {


            $basePathInfo = $pathInfo;
            if (empty($pathInfo))
                return false;

            if ($this->suffix)
                $pathInfo = strtr($pathInfo, [$this->suffix => '']);

            //if ($this->host !== null) {
            //    $pathInfo = strtolower($request->getHostInfo()) . ($pathInfo === '' ? '' : '/' . $pathInfo);
            //}

            foreach ($this->getAllPaths() as $path) {
                $pathInfo = str_replace($this->index . '/', '', $pathInfo);
                if ($path !== '' && strpos($pathInfo, $path) === 0) {

                    $params['slug'] = ltrim($path);
                    $_GET['slug'] = $params['slug'];

                    $pathInfo = ltrim(substr($basePathInfo, strlen($this->index . '/' . $path)), '/');
                   // $pathInfo = trim(substr($basePathInfo, strlen($this->index . '/' . $path)));

                    if (!empty($pathInfo)) {
                        $parts = explode('/', $pathInfo);
                        $paramsList = array_chunk($parts, 2);
                        foreach ($paramsList as $k => $p) {

                            if (!isset($p[1])) {
                                return false;
                            }
                            if (isset($p[1]) && isset($p[0])) {
                                $_GET[$p[0]] = $p[1];
                                $params[$p[0]] = $p[1];
                            }
                        }
                    }
                    return [$this->route, $params];
                }

            }
        }
        return false;
    }

}
