<?php
echo $form->field($model, 'seo_products')->checkbox();
echo $form->field($model, 'seo_products_title')->hint($model::t('META_TPL', [
            'currency' => Yii::$app->currency->active->symbol
]));
echo $form->field($model, 'seo_products_keywords')->hint($model::t('META_TPL', [
            'currency' => Yii::$app->currency->active->symbol
]));
echo $form->field($model, 'seo_products_description')->hint($model::t('META_TPL', [
            'currency' => Yii::$app->currency->active->symbol
]));