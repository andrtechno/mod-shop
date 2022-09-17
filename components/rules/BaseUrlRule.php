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
                $url = '/' . trim($params['slug'], '/');
                unset($params['slug']);
            } else {
                $url = '';
            }
            $parts = [];
            if (!empty($params)) {
                if (Yii::$app->request->isPjax) {
                    unset($params['_pjax']);
                }
                foreach ($params as $key => $val) {
                    if (!is_array($val)) {
                        $parts[] = $key . '/' . $val;
                    }
                }
                $url .= '/' . implode('/', $parts);
            }

            return $this->index . $url . $this->suffix;
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
        //original end

        $params = [];
        $pathInfoParse = str_replace($this->index . '/', '', $pathInfo);

        $index = $this->index;
        if (preg_match("/$index(\w+)/i", $pathInfo, $matches)) {
            return false;
        }

        if ($this->index == mb_substr($pathInfo, 0, mb_strlen($this->index))) {
            $pathInfoParse = str_replace($this->index, '', $pathInfoParse);

            if (!empty($pathInfoParse)) {
                $parts = explode('/', $pathInfoParse);
                $paramsList = array_chunk($parts, 2);

                foreach ($paramsList as $k => $p) {
                    if (!isset($p[1])) {
                        return false;
                    }
                    if (isset($p[1], $p[0])) {
                        $_GET[$p[0]] = $p[1];
                        $params[$p[0]] = $p[1];
                    }
                }

            }
            return [$this->route, $params];

        }
        return false;
    }

}
