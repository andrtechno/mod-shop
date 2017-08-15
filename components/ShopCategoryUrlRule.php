<?php

class ShopCategoryUrlRule extends CBaseUrlRule {

    public $connectionID = 'db';
    public $urlSuffix = '';

    public function createUrl($manager, $route, $params, $ampersand) {
        if ($route === 'shop/category/view') {
            if(isset($params['seo_alias'])){
            $url = trim($params['seo_alias'], '/');
            unset($params['seo_alias']);
            }else{
               $url=''; 
            }
            $parts = array();
            if (!empty($params)) {
                foreach ($params as $key => $val)
                    $parts[] = $key . '/' . $val;
                
                // for ajax mode
                if (Yii::app()->request->isAjaxRequest && Yii::app()->settings->get('shop','ajax_mode')) {
                    if (!Yii::app()->request->getParam('ajax')) {
                        $url .= '/' . implode('/', $parts);
                    } else {
                       // $url .= implode('/', $parts);
                    }
                } else {
                    $url .= '/' . implode('/', $parts);
                }
            }

            return $url . $this->urlSuffix;
        }
        return false;
    }

    public function parseUrl($manager, $request, $pathInfo, $rawPathInfo) {
        if (empty($pathInfo))
            return false;

        if ($this->urlSuffix)
            $pathInfo = strtr($pathInfo, array($this->urlSuffix => ''));

        foreach ($this->getAllPaths() as $path) {
            if ($path !== '' && strpos($pathInfo, $path) === 0) {
                $_GET['seo_alias'] = $path;

                $params = ltrim(substr($pathInfo, strlen($path)), '/');
                Yii::app()->urlManager->parsePathInfo($params);

                return 'shop/category/view';
            }
        }

        return false;
    }

    protected function getAllPaths() {
        $allPaths = Yii::app()->cache->get('ShopCategoryUrlRule');

        if ($allPaths === false) {
            $allPaths = Yii::app()->db->createCommand()
                    ->from('{{shop_category}}')
                    ->select('full_path')
                    ->queryColumn();

            // Sort paths by length.
            usort($allPaths, function($a, $b) {
                        return strlen($b) - strlen($a);
                    });

            Yii::app()->cache->set('ShopCategoryUrlRule', $allPaths,Yii::app()->settings->get('app','cache_time'));
        }

        return $allPaths;
    }

}
