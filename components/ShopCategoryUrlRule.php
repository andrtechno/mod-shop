<?php

namespace panix\mod\shop\components;

use Yii;

class ShopCategoryUrlRule extends \yii\web\UrlRule {



    public function createUrl($manager, $route, $params) {
       /* if ($route === 'shop/category/view') {
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
                if (Yii::$app->request->isAjax) {
                    if (!Yii::$app->request->get('ajax')) {
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
        return false;*/
        return  ['pattern' => 'admin/auth', 'route' => 'admin/auth'];
    }

    public function parseRequest222($manager, $request) {


        foreach ($this->getAllPaths() as $path) {
            if ($path !== '' && strpos($pathInfo, $path) === 0) {
                $_GET['seo_alias'] = $path;

                $params = ltrim(substr($pathInfo, strlen($path)), '/');
                Yii::$app->urlManager->parsePathInfo($params);

                return 'shop/category/view';
            }
        }

        return false;
    }

    protected function getAllPaths() {
        $allPaths = Yii::$app->cache->get('ShopCategoryUrlRule');

        if ($allPaths === false) {
            
            
            $allPaths = (new \yii\db\Query())
    ->select(['full_path'])
    ->from('{{%shop_category}}')
    ->all();
            
            
           /* $allPaths = Yii::app()->db->createCommand()
                    ->from('{{shop_category}}')
                    ->select('full_path')
                    ->queryColumn();*/

            // Sort paths by length.
            usort($allPaths, function($a, $b) {
                        return strlen($b) - strlen($a);
                    });

            Yii::$app->cache->set('ShopCategoryUrlRule', $allPaths,Yii::$app->settings->get('app','cache_time'));
        }

        return $allPaths;
    }

}
