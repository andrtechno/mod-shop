<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use panix\mod\shop\models\Currency;
use panix\mod\shop\models\search\SearchResultSearch;
use panix\engine\controllers\AdminController;

class SearchResultController extends AdminController
{


    public $icon = '';

    public function actionIndex()
    {
        $this->pageName = Yii::t('shop/admin', 'SearchResult');

        $this->view->params['breadcrumbs'][] = [
            'label' => Yii::t('shop/default', 'MODULE_NAME'),
            'url' => ['/admin/shop']
        ];
        $this->view->params['breadcrumbs'][] = $this->pageName;

        $searchModel = new SearchResultSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }


}
