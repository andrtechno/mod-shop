<?php

namespace panix\mod\shop\components\rules;

use panix\mod\shop\models\Brand;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;
use yii\web\UrlNormalizer;
use yii\web\UrlRule;

/**
 * Class BrandUrlRule
 * @package panix\mod\shop\components
 */
class Brand1111UrlRule extends UrlRule
{

    public $index = 'brand';
    private $_template;
    private $_paramRules = [];
    private $_routeParams = [];
    private $_routeRule;
    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        if ($this->mode === self::PARSING_ONLY) {
            $this->createStatus = self::CREATE_STATUS_PARSING_ONLY;
            return false;
        }

        $tr = [];

        // match the route part first
        if ($route !== $this->route) {
            if ($this->_routeRule !== null && preg_match($this->_routeRule, $route, $matches)) {
                $matches = $this->substitutePlaceholderNames($matches);
                foreach ($this->_routeParams as $name => $token) {
                    if (isset($this->defaults[$name]) && strcmp($this->defaults[$name], $matches[$name]) === 0) {
                        $tr[$token] = '';
                    } else {
                        $tr[$token] = $matches[$name];
                    }
                }
            } else {
                $this->createStatus = self::CREATE_STATUS_ROUTE_MISMATCH;
                return false;
            }
        }

        // match default params
        // if a default param is not in the route pattern, its value must also be matched
        foreach ($this->defaults as $name => $value) {
            if (isset($this->_routeParams[$name])) {
                continue;
            }
            if (!isset($params[$name])) {
                // allow omit empty optional params
                // @see https://github.com/yiisoft/yii2/issues/10970
                if (in_array($name, $this->placeholders) && strcmp($value, '') === 0) {
                    $params[$name] = '';
                } else {
                    $this->createStatus = self::CREATE_STATUS_PARAMS_MISMATCH;
                    return false;
                }
            }
            if (strcmp($params[$name], $value) === 0) { // strcmp will do string conversion automatically
                unset($params[$name]);
                if (isset($this->_paramRules[$name])) {
                    $tr["<$name>"] = '';
                }
            } elseif (!isset($this->_paramRules[$name])) {
                $this->createStatus = self::CREATE_STATUS_PARAMS_MISMATCH;
                return false;
            }
        }

        // match params in the pattern
        foreach ($this->_paramRules as $name => $rule) {
            if (isset($params[$name]) && !is_array($params[$name]) && ($rule === '' || preg_match($rule, $params[$name]))) {
                $tr["<$name>"] = $this->encodeParams ? urlencode($params[$name]) : $params[$name];
                unset($params[$name]);
            } elseif (!isset($this->defaults[$name]) || isset($params[$name])) {
                $this->createStatus = self::CREATE_STATUS_PARAMS_MISMATCH;
                return false;
            }
        }

       // $url=$this->index;
        $url = $this->trimSlashes(strtr($this->_template, $tr));

        if ($this->host !== null) {
            $pos = strpos($url, '/', 8);
            if ($pos !== false) {
                $url = substr($url, 0, $pos) . preg_replace('#/+#', '/', substr($url, $pos));
            }
        } elseif (strpos($url, '//') !== false) {
            $url = preg_replace('#/+#', '/', trim($url, '/'));
        }

        if ($url !== '') {
            $url .= ($this->suffix === null ? $manager->suffix : $this->suffix);
        }
//print_r($params);die;
if(false){
    $url .= '/' . 'dsa';
}else{
    if (!empty($params) && ($query = http_build_query($params)) !== '') {
        $url .= '?' . $query;
    }
}


        $this->createStatus = self::CREATE_STATUS_SUCCESS;
        return $url;



        if ($route === $this->route) {
            if (isset($params['slug'])) {
                $url = trim($params['slug'], '/');
                unset($params['slug']);
            } else {
                $url = '';
            }
            $parts = [];
            if (!empty($params)) {
                unset($params['_pjax']);

                foreach ($params as $key => $val) {
                    //if(is_array($val)){
                    //     $val = implode(',',$val);
                    // }
                    if ($val)
                        $parts[] = $key . '/' . $val;
                }
                $url .= '/' . implode('/', $parts);
                //$url .= implode('/', $parts);
            }
           // print_r($this->index . '/' . $url . $this->suffix);die;
            return $this->index . '/' . $url . $this->suffix;
            //return $this->index . $url . $this->suffix;
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
            if (isset($this->_routeParams[$name])) {
                $tr[$this->_routeParams[$name]] = $value;
                unset($params[$name]);
            } elseif (isset($this->_paramRules[$name])) {
                if($name == 'params'){
                    $parts = explode('/', $value);

                    $list = array_chunk($parts, 2);
                    foreach ($list as $k => $p) {
                        if (isset($p[0],$p[1])) {
                            $_GET[$p[0]] = $p[1];
                            $params[$p[0]] = $p[1];
                        }
                    }
                }else{
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

        if ($this->_routeRule !== null) {
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


    private function trimSlashes($string)
    {
        if (strncmp($string, '//', 2) === 0) {
            return '//' . trim($string, '/');
        }

        return trim($string, '/');
    }

    private function preparePattern()
    {
        $this->pattern = $this->trimSlashes($this->pattern);
        $this->route = trim($this->route, '/');

        if ($this->host !== null) {
            $this->host = rtrim($this->host, '/');
            $this->pattern = rtrim($this->host . '/' . $this->pattern, '/');
        } elseif ($this->pattern === '') {
            $this->_template = '';
            $this->pattern = '#^$#u';

            return;
        } elseif (($pos = strpos($this->pattern, '://')) !== false) {
            if (($pos2 = strpos($this->pattern, '/', $pos + 3)) !== false) {
                $this->host = substr($this->pattern, 0, $pos2);
            } else {
                $this->host = $this->pattern;
            }
        } elseif (strncmp($this->pattern, '//', 2) === 0) {
            if (($pos2 = strpos($this->pattern, '/', 2)) !== false) {
                $this->host = substr($this->pattern, 0, $pos2);
            } else {
                $this->host = $this->pattern;
            }
        } else {
            $this->pattern = '/' . $this->pattern . '/';
        }

        if (strpos($this->route, '<') !== false && preg_match_all('/<([\w._-]+)>/', $this->route, $matches)) {
            foreach ($matches[1] as $name) {
                $this->_routeParams[$name] = "<$name>";
            }
        }

        $this->translatePattern(true);
    }
    public function init()
    {
        if ($this->pattern === null) {
            throw new InvalidConfigException('UrlRule::pattern must be set.');
        }
        if ($this->route === null) {
            throw new InvalidConfigException('UrlRule::route must be set.');
        }
        if (is_array($this->normalizer)) {
            $normalizerConfig = array_merge(['class' => UrlNormalizer::className()], $this->normalizer);
            $this->normalizer = Yii::createObject($normalizerConfig);
        }
        if ($this->normalizer !== null && $this->normalizer !== false && !$this->normalizer instanceof UrlNormalizer) {
            throw new InvalidConfigException('Invalid config for UrlRule::normalizer.');
        }
        if ($this->verb !== null) {
            if (is_array($this->verb)) {
                foreach ($this->verb as $i => $verb) {
                    $this->verb[$i] = strtoupper($verb);
                }
            } else {
                $this->verb = [strtoupper($this->verb)];
            }
        }
        if ($this->name === null) {
            $this->name = $this->pattern;
        }

        $this->preparePattern();
    }
    private function translatePattern($allowAppendSlash)
    {
        $tr = [
            '.' => '\\.',
            '*' => '\\*',
            '$' => '\\$',
            '[' => '\\[',
            ']' => '\\]',
            '(' => '\\(',
            ')' => '\\)',
        ];

        $tr2 = [];
        $requiredPatternPart = $this->pattern;
        $oldOffset = 0;
        if (preg_match_all('/<([\w._-]+):?([^>]+)?>/', $this->pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            $appendSlash = false;
            foreach ($matches as $match) {
                $name = $match[1][0];
                $pattern = isset($match[2][0]) ? $match[2][0] : '[^\/]+';
                $placeholder = 'a' . hash('crc32b', $name); // placeholder must begin with a letter
                $this->placeholders[$placeholder] = $name;
                if (array_key_exists($name, $this->defaults)) {
                    $length = strlen($match[0][0]);
                    $offset = $match[0][1];
                    $requiredPatternPart = str_replace("/{$match[0][0]}/", '//', $requiredPatternPart);
                    if (
                        $allowAppendSlash
                        && ($appendSlash || $offset === 1)
                        && (($offset - $oldOffset) === 1)
                        && isset($this->pattern[$offset + $length])
                        && $this->pattern[$offset + $length] === '/'
                        && isset($this->pattern[$offset + $length + 1])
                    ) {
                        // if pattern starts from optional params, put slash at the end of param pattern
                        // @see https://github.com/yiisoft/yii2/issues/13086
                        $appendSlash = true;
                        $tr["<$name>/"] = "((?P<$placeholder>$pattern)/)?";
                    } elseif (
                        $offset > 1
                        && $this->pattern[$offset - 1] === '/'
                        && (!isset($this->pattern[$offset + $length]) || $this->pattern[$offset + $length] === '/')
                    ) {
                        $appendSlash = false;
                        $tr["/<$name>"] = "(/(?P<$placeholder>$pattern))?";
                    }
                    $tr["<$name>"] = "(?P<$placeholder>$pattern)?";
                    $oldOffset = $offset + $length;
                } else {
                    $appendSlash = false;
                    $tr["<$name>"] = "(?P<$placeholder>$pattern)";
                }

                if (isset($this->_routeParams[$name])) {
                    $tr2["<$name>"] = "(?P<$placeholder>$pattern)";
                } else {
                    $this->_paramRules[$name] = $pattern === '[^\/]+' ? '' : "#^$pattern$#u";
                }
            }
        }

        // we have only optional params in route - ensure slash position on param patterns
        if ($allowAppendSlash && trim($requiredPatternPart, '/') === '') {
            $this->translatePattern(false);
            return;
        }

        $this->_template = preg_replace('/<([\w._-]+):?([^>]+)?>/', '<$1>', $this->pattern);
        $this->pattern = '#^' . trim(strtr($this->_template, $tr), '/') . '$#u';

        // if host starts with relative scheme, then insert pattern to match any
        if ($this->host !== null && strncmp($this->host, '//', 2) === 0) {
            $this->pattern = substr_replace($this->pattern, '[\w]+://', 2, 0);
        }

        if (!empty($this->_routeParams)) {
            $this->_routeRule = '#^' . strtr($this->route, $tr2) . '$#u';
        }
    }

}
