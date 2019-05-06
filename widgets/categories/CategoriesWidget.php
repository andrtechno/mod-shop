<?php

namespace panix\mod\shop\widgets\categories;

use panix\mod\shop\models\Category;
use panix\mod\shop\models\CategoryNode;
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

        $model = Category::findOne(1);

        if (!$model) {
            die(__CLASS__ . 'err');
        } else {
            $result = $model->menuArray();
        }

        return $this->render($this->skin, ['result' => $result]);
    }

    public function recursive($data, $i = 0)
    {
        $html = '';

        if (isset($data)) {
            $html .= Html::beginTag('ul');
            foreach ($data as $obj) {

                $i++;
                if (isset($_GET['slug']) && stripos($_GET['slug'], $obj['url']['slug']) !== false) {
                    $ariaExpanded = 'true';
                    $collapseClass = 'collapse in';
                } else {
                    $ariaExpanded = 'false';
                    $collapseClass = 'collapse';
                }
                if (Yii::$app->request->get('slug')) {
                    $activeClass = ($obj['url']['slug'] === $_GET['slug']) ? 'active' : '';
                } else {
                    $activeClass = '';
                }


                $html .= Html::beginTag('li', array('class' => $activeClass));
                if (isset($obj['items'])) {
                    $html .= Html::a($obj['label'], '#collapse' . $obj['id'], array(
                        'data-toggle' => 'collapse',
                        'aria-expanded' => $ariaExpanded,
                        'aria-controls' => 'collapse' . $obj['id'],
                        'class' => 'collapsed plus-minus'
                    ));
                    $html .= Html::tag('sup', $obj['total_count'], []);
                    $html .= Html::beginTag('div', array('class' => $collapseClass, 'id' => 'collapse' . $obj['id']));
                    $html .= $this->recursive($obj['items'], $i);

                    $html .= Html::endTag('div');
                } else {

                    // $html .= Html::a($obj['label'], Yii::$app->urlManager->createUrl([$obj['url'][0], ['slug' => $obj['url']['slug']]]));
                    $html .= Html::a($obj['label'], Yii::$app->urlManager->createUrl($obj['url']));
                    $html .= Html::tag('sup', $obj['total_count'], []);
                }
                $html .= Html::endTag('li');
            }
            $html .= Html::endTag('ul');
        } else {
            //$parent[$obj['id']] = $obj['id'];
            $html .= Html::a($data['label'], '');
            $html .= Html::tag('sup', $data['total_count'], []);
        }
        return $html;
    }

}
