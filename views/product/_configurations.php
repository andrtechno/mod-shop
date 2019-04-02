<?php
use panix\engine\Html;
use yii\helpers\Json;
if (count($model->processVariants())) { ?>
    <div class="errors" id="productErrors"></div>

    <table class="table table-bordered configurations">
        <?php
        $jsVariantsData = array();

        foreach ($model->processVariants() as $variant) {
            $dropDownData = array();
            echo '<tr><td class="attr_name">';
            echo $variant['attribute']->title . ':';
            echo '</td><td>';

            foreach ($variant['options'] as $v) {
                $jsVariantsData[$v->id] = $v;
                $price = ($v->price > 0) ? ' (+'.$v->price.' '.Yii::$app->currency->active->symbol.')':'';
                $dropDownData[$v->id] = $v->option->value .$price;
            }
            echo Html::dropDownList('eav[' . $variant['attribute']->id . ']', null, $dropDownData, array('class' => 'variantData form-control', 'empty' => Yii::t('app','EMPTY_LIST')));
            echo '</td></tr>';
        }

        // Register variant prices script
        $this->registerJs("
            var jsVariantsData = " . Json::encode($jsVariantsData) . ";
        ",\yii\web\View::POS_END);
       // Yii::app()->clientScript->registerScript('jsVariantsData', '
		//	var jsVariantsData = ' . CJavaScript::jsonEncode($jsVariantsData) . ';
		//', CClientScript::POS_END);

        // Display product configurations
        if ($model->use_configurations) {
            // Get data
            $confData = $this->getConfigurableData();

            // Register configuration script

            $this->registerJs(strtr('var productPrices = {prices};', ['{prices}' => Json::encode($confData['prices'])]),\yii\web\View::POS_END);

           /* Yii::app()->clientScript->registerScript('productPrices', strtr('
							var productPrices = {prices};
						', array(
                '{prices}' => CJavaScript::encode($confData['prices'])
                    )), CClientScript::POS_END);*/
//echo CVarDumper::dump($confData,10,true);
            foreach ($confData['attributes'] as $attr) {
               // $attr->name .= $confData['prices'];
                if (isset($confData['data'][$attr->name])) {
                    echo '<tr><td class="attr_name">';

                    echo $attr->title . ':';
                    echo '</td><td>';
                    echo Html::dropDownList('configurations[' . $attr->name . ']', null, array_flip($confData['data'][$attr->name]), array('class' => 'eavData form-control'));
                    echo '</td></tr>';
                }
            }
        }
        ?>
    </table>

<?php } ?>