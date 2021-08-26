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

    public function createUrl($manager, $route, $params)
    {
        if ($this->mode === self::PARSING_ONLY) {
            $this->createStatus = self::CREATE_STATUS_PARSING_ONLY;
            return false;
        }
        $url=$this->index;
        $tr = [];
        if (isset($params['slug'])) {
            $url .= '/'.trim($params['slug'], '/');
            $this->defaults['slug']=$params['slug'];
            unset($params['slug']);
        } else {
            $url .= '';
        }
        // match the route part first


        // match default params
        // if a default param is not in the route pattern, its value must also be matched


        // match params in the pattern

        /*if ($this->host !== null) {
            $pos = strpos($url, '/', 8);
            if ($pos !== false) {
                $url = substr($url, 0, $pos) . preg_replace('#/+#', '/', substr($url, $pos));
            }
        } elseif (strpos($url, '//') !== false) {
            $url = preg_replace('#/+#', '/', trim($url, '/'));
        }*/


        $parts = [];
        if (!empty($params) && ($query = http_build_query($params)) !== '') {

            foreach ($params as $key => $val) {
                if (!is_array($val)) {
                    $parts[] = $key . '/' . $val;
                }
            }
            $url .= '/' . implode('/', $parts);
           // $url .= '/' . $query;
        }
        if ($url !== '') {
            $url .= ($this->suffix === null ? $manager->suffix : $this->suffix);
        }
        $this->createStatus = self::CREATE_STATUS_SUCCESS;
        return $url;
    }



    /**
     * @inheritdoc
     */
    public function createUrl2($manager, $route, $params)
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

        return parent::createUrl($manager, $route, $params);
    }
    public function createUrl22($manager, $route, $params)
    {
       // if ($this->mode === self::PARSING_ONLY) {
         //   $this->createStatus = self::CREATE_STATUS_PARSING_ONLY;
          //  return false;
       // }
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
            //if($params){
                //print_r($parts);die;
            //}
            return $this->index . $url . $this->suffix;
            // return $url . $this->suffix;
        }
        return parent::createUrl($manager, $route, $params);
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

       // if (!preg_match($this->pattern, $pathInfo, $matches)) {
           // echo $pathInfo;die;
        //    return false;
       // }

        //original end

       // $params=[];
        $params = $this->defaults;



        $pathInfoParse = str_replace($this->index . '/', '', $pathInfo);
        $parts = explode('/', $pathInfoParse);
        if ($this->index == mb_substr($pathInfo, 0,strlen($this->index))) {
            $paramsList = array_chunk($parts, 2);


            //print_r($paramsList);die;
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
            $route = $this->route;
            if ($normalized) {
                // pathInfo was changed by normalizer - we need also normalize route
                return $this->getNormalizer($manager)->normalizeRoute([$route, $params]);
            }
          //      if(count($params))
               // CMS::dump([$route, $params]);
          // die;
            return [$route, $params];
        }

        return false;
    }
    private function trimSlashes($string)
    {
        if (strncmp($string, '//', 2) === 0) {
            return '//' . trim($string, '/');
        }

        return trim($string, '/');
    }
}
