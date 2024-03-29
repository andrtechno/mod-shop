<?php

namespace panix\mod\shop\components\rules;

use Yii;
use panix\engine\CMS;
use panix\mod\shop\models\Category;
use yii\web\UrlRule;

/**
 * Class CategoryUrlRule
 * @package panix\mod\shop\components
 */
class CategoryUrlRule2 extends UrlRule
{
    public $index = 'catalog';

    public function parseRequest($manager, $request)
    {

        if ($this->mode === self::CREATION_ONLY) {
            return false;
        }

        if (!empty($this->verb) && !in_array($request->getMethod(), $this->verb, true)) {
            return false;
        }

        $suffix = (string)($this->suffix === null ? $manager->suffix : $this->suffix);
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

        if ($this->host !== null) {
            $pathInfo = strtolower($request->getHostInfo()) . ($pathInfo === '' ? '' : '/' . $pathInfo);
        }

         //if (!preg_match($this->pattern, $pathInfo, $matches)) {

         //   return false;
       //  }



        $params = $this->defaults;

        if ($params['slug'] !== '' && strpos(str_replace($this->index . '/', '', $pathInfo), $params['slug']) === 0) {

            $parts_slug = explode('/', $this->index . '/' . $params['slug']);
            $parts = explode('/', $pathInfo);

            $a = array_slice($parts, 0, count($parts_slug));
            if (array_diff($parts_slug, $a)) {

                return false;
            }
            $_GET['slug'] = $params['slug'];
            $filterPathInfo = ltrim(substr($pathInfo, strlen($this->index . '/' . $params['slug'])), '/');
            if (!empty($filterPathInfo)) {
                $parts = explode('/', $filterPathInfo);
                $paramsList = array_chunk($parts, 2);

                foreach ($paramsList as $p) {
                    if (isset($p[1])) {
                        $params[$p[0]] = $p[1];
                        $_GET[$p[0]] = $p[1];
                    } else {
                        return false;
                    }
                }
            }


            $route = $this->route;
            Yii::debug("Request parsed with URL rule: {$this->name}", __METHOD__);

            if ($normalized) {
                // pathInfo was changed by normalizer - we need also normalize route
                return $this->getNormalizer($manager)->normalizeRoute([$route, $params]);
            }

            return [$route, $params];
        }


        return false;

    }

    public function createUrl($manager, $route, $params)
    {

        if ($route === $this->route) {
            if (isset($params['slug'])) {
                $url = '/' . trim($params['slug'], '/');
                unset($params['slug']);
            } else {
                $url = '';
            }
            //echo $url;die;
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
                if (!empty($url))
                    $url .= '/' . implode('/', $parts);
            }

            return $this->index . $url . $this->suffix;
            // return $url . $this->suffix;
        }
        return parent::createUrl($manager, $route, $params);
    }
}
