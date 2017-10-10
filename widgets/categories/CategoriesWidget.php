<?php

namespace panix\mod\shop\widgets\categories;

use panix\mod\shop\models\Category;
use panix\mod\shop\models\CategoryNode;
use yii\helpers\Html;
use Yii;

/**
 * 
 * @package widgets.modules.shop
 * @uses \panix\engine\data\Widget
 */
class CategoriesWidget extends \panix\engine\data\Widget {

    public function init() {
        //$this->publishAssets();
    }

    public function run() {

        $model = Category::findOne(1);

        if (!$model) {
            die(__CLASS__.'err');
        } else {
            $result = $model->menuArray();
        }

        return $this->render($this->skin, ['result' => $result]);
    }

    public function recursive($data, $i = 0) {
        $html = '';

        if (isset($data)) {
            $html .= Html::beginTag('ul');
            foreach ($data as $obj) {

                $i++;
                if (isset($_GET['seo_alias']) && stripos($_GET['seo_alias'], $obj['url']['seo_alias']) !== false) {
                    $ariaExpanded = 'true';
                    $collapseClass = 'collapse in';
                } else {
                    $ariaExpanded = 'false';
                    $collapseClass = 'collapse';
                }
                if(Yii::$app->request->get('seo_alias')){
                        $activeClass = ($obj['url']['seo_alias'] === $_GET['seo_alias']) ? 'active' : '';
                }else{
                    $activeClass='';
                }
            

                $html .= Html::beginTag('li', array('class' => $activeClass));
                if (isset($obj['items'])) {
                    $html .= $obj['total_count'];
                    $html .= Html::a($obj['label'], '#collapse' . $obj['id'], array(
                                'data-toggle' => 'collapse',
                                'aria-expanded' => $ariaExpanded,
                                'aria-controls' => 'collapse' . $obj['id'],
                                'class' => 'collapsed plus-minus'
                    ));
                    $html .= Html::beginTag('div', array('class' => $collapseClass, 'id' => 'collapse' . $obj['id']));
                    $html .= $this->recursive($obj['items'], $i);

                    $html .= Html::endTag('div');
                } else {
 $html .= $obj['total_count'];
                   // $html .= Html::a($obj['label'], Yii::$app->urlManager->createUrl([$obj['url'][0], ['seo_alias' => $obj['url']['seo_alias']]]));
                    $html .= Html::a($obj['label'], Yii::$app->urlManager->createUrl($obj['url']));
                }
                $html .= Html::endTag('li');
            }
            $html .= Html::endTag('ul');
        } else {
            $parent[$obj['id']] = $obj['id'];
                   $html .= $data['total_count'];
            $html .= Html::a($data['label'], '');
        }
        return $html;
    }

}
