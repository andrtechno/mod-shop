<?php

namespace panix\mod\shop\components;

use yii\helpers\VarDumper;
use yii\web\UrlRule;
use panix\mod\shop\models\Manufacturer;

/**
 * Class ManufacturerUrlRule
 * @package panix\mod\shop\components
 */
class ManufacturerUrlRule extends UrlRule
{

    public $route = 'shop/manufacturer/view';
    public $pattern = 'manufacturer/<slug:[0-9a-zA-Z\-]+>';
    public $cacheDuration = 0;

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
                    $parts[] = $key . '/' . $val;
                }
                $url .= '/' . implode('/', $parts);
            }

            return 'manufacturer/' . $url . $this->suffix;
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

//echo $pathInfo;die;
        foreach ($this->getAllPaths() as $path) {

            if ($path['slug'] !== '' && strpos(str_replace('manufacturer/', '', $pathInfo), $path['slug']) === 0) {
                $_GET['slug'] = $path['slug'];

                $parts = explode('/', $pathInfo);
                $paramsList = array_chunk($parts, 2);
                unset($paramsList[0]);
                foreach ($paramsList as $k => $p) {
                    if (isset($p[0])) {
                        $_GET[$p[0]] = $p[1];
                       // $params[$p[0]] = strpos( $p[1],',') ? explode(',', $p[1]) : $p[1];
                        $params[$p[0]] = $p[1];
                    }
                }

                $params['slug'] = ltrim($path['slug']);

                //echo VarDumper::dump($params, 10, true);
                //die;
                return [$this->route, $params];
            }
        }

        return false;
    }

    protected function getAllPaths()
    {
        $allPaths = \Yii::$app->cache->get(__CLASS__);
        if ($allPaths === false) {
            $allPaths = (new \yii\db\Query())
                ->select(['slug'])
                ->from(Manufacturer::tableName())
                ->all();


            // Sort paths by length.
            usort($allPaths, function ($a, $b) {
                return strlen($b['slug']) - strlen($a['slug']);
            });

            \Yii::$app->cache->set(__CLASS__, $allPaths, $this->cacheDuration);
        }

        return $allPaths;
    }

}
