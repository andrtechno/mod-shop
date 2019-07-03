<?php

namespace panix\mod\shop\widgets\categories;

use panix\engine\CMS;
use panix\mod\shop\models\Category;
use panix\engine\data\Widget;
use yii\helpers\Html;
use Yii;

/**
 *
 * @package widgets.modules.shop
 * @uses \panix\engine\data\Widget
 */
class CategoriesWidget extends Widget
{

    public function run()
    {

        // $model = Category::findOne(1);
        $model = Category::find()->dataFancytree(1);

        return $this->render($this->skin, ['result' => $model]);
    }

    public function recursive($data, $i = 0, $ulOptions = array())
    {
        $html = '';
        if (isset($data)) {
            $html .= Html::beginTag('ul', $ulOptions);
            foreach ($data as $obj) {

                $i++;
                if (Yii::$app->request->get('slug') && stripos(Yii::$app->request->get('slug'), $obj['url']) !== false) {
                    $ariaExpanded = 'true';
                    $collapseClass = 'collapse in ';
                } else {
                    $ariaExpanded = 'false';
                    $collapseClass = 'collapse ';
                }

                if (Yii::$app->request->get('slug')) {
                    $activeClass = ($obj['url'] === '/' . Yii::$app->request->get('slug')) ? 'active' : '';
                } else {
                    $activeClass = '';
                }
                if (isset($obj['children'])) {
                    $toggleBtn = Html::button('+', [
                       // 'class' => 'btn btn-sm btn-link',
                        'data-toggle' => 'collapse',
                        'aria-expanded' => $ariaExpanded,
                        'data-target'=>'#' . $this->hash($obj['key']),
                        'aria-controls' => '#' . $this->hash($obj['key']),
                        'class' => "collapsed {$activeClass}"
                    ]);
                } else {
                    $toggleBtn = '';

                }
                $html .= Html::beginTag('li', []);
                // $iconClass = (isset($obj['folder'])) ? 'icon-folder-open' : '';


                if (isset($obj['children'])) {
                    $html .= Html::a($obj['title'], '#' . $this->hash($obj['key']));
                    $html .= $toggleBtn;
                    $ulOptions = ['class' => $collapseClass, 'id' => $this->hash($obj['key'])];
                    $html .= $this->recursive($obj['children'], $i, $ulOptions);
                } else {
                    $html .= Html::a($obj['title'], Yii::$app->urlManager->createUrl($obj['url']), ['class' => "nav-link1 {$activeClass}"]);
                }
                $html .= Html::endTag('li');
            }
            $html .= Html::endTag('ul');

        }
        return $html;
    }

    private function hash($key)
    {
        return 'collapse-' . CMS::hash('catalog-' . $key);
    }
}
