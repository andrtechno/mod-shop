<?php

namespace panix\mod\shop\components\rules;

use yii\web\UrlRule;

/**
 * Class BrandUrlRule
 * @package panix\mod\shop\components
 */
class BaseUrlRule extends \yii\web\UrlRule
{

    public $index = 'brand';

    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        if ($this->mode === self::PARSING_ONLY) {
            $this->createStatus = self::CREATE_STATUS_PARSING_ONLY;
            return false;
        }

        if ($route === $this->route) {
            if (isset($params['slug'])) {
                $url = '/' . trim($params['slug'], '/');
                unset($params['slug']);
            } else {
                $url = '';
            }
            $parts = [];
            if ($params) {
                unset($params['_pjax']);
                foreach ($params as $key => $val) {
                    //if ($val)
                    $parts[] = $key . '/' . $val;
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

        if (!preg_match($this->pattern, $pathInfo, $matches)) {
            return false;
        }

        $matches = $this->substitutePlaceholderNames($matches);

        foreach ($this->defaults as $name => $value) {
            if (!isset($matches[$name]) || $matches[$name] === '') {
                $matches[$name] = $value;
            }
        }
        $params = $this->defaults;
        $tr = [];
        //original end
        //foreach editing
        foreach ($matches as $name => $value) {
            //if (isset($this->_routeParams[$name])) {
            //    $tr[$this->_routeParams[$name]] = $value;
            //    unset($params[$name]);
            if (isset($this->paramRules[$name])) {
                if ($name == 'params') {
                    $parts = explode('/', $value);
                    $list = array_chunk($parts, 2);
                    foreach ($list as $k => $p) {
                        if(!isset($p[1])){
                            return false;
                        }
                        if (isset($p[0], $p[1])) {
                            $_GET[$p[0]] = $p[1];
                            $params[$p[0]] = $p[1];
                        }
                    }
                } else {
                    $params[$name] = $value;
                    $_GET[$name] = $value;
                }
            }
        }
        if ($this->route !== null) {
            $route = strtr($this->route, $tr);
        } else {
            $route = $this->route;
        }

        if ($normalized) {
            // pathInfo was changed by normalizer - we need also normalize route
            return $this->getNormalizer($manager)->normalizeRoute([$route, $params]);
        }

        return [$route, $params];
    }


}
