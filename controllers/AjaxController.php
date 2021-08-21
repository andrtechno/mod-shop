<?php

namespace panix\mod\shop\controllers;

use panix\mod\shop\components\Filter;
use panix\mod\shop\models\Category;
use Yii;
use panix\engine\controllers\WebController;
use yii\helpers\ArrayHelper;

class AjaxController extends WebController
{
    /**
     * Set store currency
     *
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionCurrency($id)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->currency->setActive($id);
        } else {
            return $this->goHome();
        }
    }


    public function actionFilter()
    {
        $productModel = Yii::$app->getModule('shop')->model('Product');
        $query = $productModel::find();
        $query->published();
        $category_id=9;
        if ($category_id) {
            $category = Category::findOne($category_id);
            if (!$category)
                $this->error404();
            $query->applyCategories($category);
        }
        $filterPost = Yii::$app->request->post('filter');
        if ($filterPost){
            if(isset($filterPost['brand'])){
                $query->applyBrands($filterPost['brand']);
            }
            //unset(Yii::$app->request->post('filter')['brand']);
           // $query->getFindByEavAttributes3($filterPost);
        }
//echo $query->createCommand()->rawSql;die;

        $filter = new Filter($query, $category);


        //print_r($f->getPostActiveAttributes());die;
        $attributes = $filter->getCategoryAttributesCallback();
        $brands = $filter->getCategoryBrandsCallback();
        $total = 0;
        $results = ArrayHelper::merge($attributes, ['brand' => $brands]);



     //   $total = $filter->count();
        return $this->asJson([
            'textTotal'=>"Показать ".Yii::t('shop/default','PRODUCTS_COUNTER',$total),
            'totalCount' => $total,
            'filters' => $results
        ]);
    }
}
