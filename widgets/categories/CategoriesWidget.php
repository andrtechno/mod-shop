<?php

namespace panix\mod\shop\widgets\categories;

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

    public function recursive($data, $i = 0)
    {
        $html = '';

        if (isset($data)) {

            foreach ($data as $obj) {


                $iconClass = (isset($obj['folder'])) ? 'icon-folder-open' : '';
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
                    $html .= Html::a($obj['title'], '#collapse' . $obj['key'], array(
                        'data-toggle' => 'collapse',
                        'aria-expanded' => $ariaExpanded,
                        'aria-controls' => 'collapse' . $obj['key'],
                        'class' => "nav-link collapsed {$activeClass} {$iconClass}"
                    ));
                    $html .= Html::beginTag('div', ['class' => $collapseClass, 'id' => 'collapse' . $obj['key']]);
                    $html .= $this->recursive($obj['children'], $i);
                    $html .= Html::endTag('div');
                } else {
                    $html .= Html::a($obj['title'], Yii::$app->urlManager->createUrl($obj['url']), ['class' => "nav-link {$activeClass} {$iconClass}"]);
                }
            }
            $i++;

        }
        return $html;
    }

}
