<?php
use panix\engine\Html;
use yii\helpers\Json;

if (count($model->processVariants())) { ?>
    <div class="errors" id="productErrors"></div>

    <div class="configurations">
        <?php
        $jsVariantsData = [];

        foreach ($model->processVariants() as $variant) {
            $dropDownData = [];
            echo '<div class="form-group row">';
            echo Html::label($variant['attribute']->title . ':', 'eav-' . $variant['attribute']->id, ['class' => 'col-sm-3 col-form-label attr_name']);

            foreach ($variant['options'] as $v) {
                $jsVariantsData[$v->id] = $v;
                if($v->price_type){
                   // $price = ($v->price > 0) ? ' (+' . $v->price . '%)' : '';
                    $price = ' (+' .Yii::$app->currency->number_format(($model->price / 100 * $v->price)).' ' . Yii::$app->currency->active['symbol'] . ')';
                }else{
                    $price = ($v->price > 0) ? ' (+' . $v->price . ' ' . Yii::$app->currency->active['symbol'] . ')' : '';
                }

                $dropDownData[$v->id] = $v->option->value . $price;
            }
            echo ' <div class="col-sm-9">';
            echo Html::dropDownList('eav[' . $variant['attribute']->id . ']', null, $dropDownData, [
                'id' => 'eav-' . $variant['attribute']->id,
                'class' => 'variantData custom-select w-auto',
                'prompt' => html_entity_decode(Yii::t('app/default', 'EMPTY_LIST'))
            ]);
            echo '</div></div>';
        }

        // Register variant prices script
        $this->registerJs("var jsVariantsData = " . Json::encode($jsVariantsData) . ";", \yii\web\View::POS_END);
        // Yii::app()->clientScript->registerScript('jsVariantsData', '
        //	var jsVariantsData = ' . CJavaScript::jsonEncode($jsVariantsData) . ';
        //', CClientScript::POS_END);

        // Display product configurations
        if ($model->use_configurations) {
            // Get data
            $confData = $this->getConfigurableData();

            // Register configuration script

            $this->registerJs(strtr('var productPrices = {prices};', ['{prices}' => Json::encode($confData['prices'])]), \yii\web\View::POS_END);

            /* Yii::app()->clientScript->registerScript('productPrices', strtr('
                             var productPrices = {prices};
                         ', array(
                 '{prices}' => CJavaScript::encode($confData['prices'])
                     )), CClientScript::POS_END);*/
//echo CVarDumper::dump($confData,10,true);
            foreach ($confData['attributes'] as $attr) {
                // $attr->name .= $confData['prices'];
                if (isset($confData['data'][$attr->name])) {
                    echo '<div class="form-group row">';
                    echo Html::label($attr->title . ':', 'conf-' . $attr->name, ['class' => 'col-sm-3 col-form-label attr_name']);
                    echo ' <div class="col-sm-9">';
                    echo Html::dropDownList('configurations[' . $attr->name . ']', null, array_flip($confData['data'][$attr->name]), [
                        'id' => 'conf-' . $attr->name,
                        'class' => 'eavData custom-select w-auto'
                    ]);
                    echo '</div></div>';
                }
            }
        }
        ?>
    </div>

<?php } ?>