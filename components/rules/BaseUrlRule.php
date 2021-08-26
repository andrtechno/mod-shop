<?php

namespace panix\mod\shop\components\rules;

use panix\engine\CMS;
use Yii;
use yii\helpers\Url;
use yii\web\HttpException;
use yii\web\UrlRule;

class BaseUrlRule extends UrlRule
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
                $url = '/'.trim($params['slug'], '/');
                unset($params['slug']);
            } else {
                $url = '';
            }
            $parts = [];
            if (!empty($params)) {
                if(Yii::$app->request->isPjax){
                    unset($params['_pjax']);
                }
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

        //original begin
        if ($this->mode === self::CREATION_ONLY) {
            return false;
        }

        if (!empty($this->verb) && !in_array($request->getMethod(), $this->verb, true)) {
            return false;
        }

        $suffix = (string) ($this->suffix === null ? $manager->suffix : $this->suffix);
        $pathInfo = $request->getPathInfo();
        $normalized = false;
        if ($this->hasNormalizer($manager)) {
            $pathInfo = $this->getNormalizer($manager)->normalizePathInfo($pathInfo, $suffix, $normalized);
        }
        if ($suffix !== '' && $pathInfo !== '') {
            $n = strlen($suffix);
            if (substr_compare($pathInfo, $suffix, -$n, $n) === 0) {
                $pathInfo = substr($pathInfo, 0, -$n);
                if ($pathInfo === '') {
                    // suffix alone is not allowed
                    return false;
                }
            } else {
                return false;
            }
        }
        //original end

        $params=[];

        $pathInfoParse = str_replace($this->index . '/', '', $pathInfo);
        $parts = explode('/', $pathInfoParse);
        if ($this->index == mb_substr($pathInfo, 0,strlen($this->index))) {
            $paramsList = array_chunk($parts, 2);



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

          // CMS::dump([$this->route, $params]);
           //die;
            return [$this->route, $params];
        }
        return false;
    }

}
