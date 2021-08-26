<?php
echo $form->field($model, 'seo_categories')->checkbox();
echo $form->field($model, 'seo_categories_title')->hint($model::t('META_CAT_TPL', [
            'currency' => Yii::$app->currency->active['symbol']
]));
echo $form->field($model, 'seo_categories_description')->hint($model::t('META_CAT_TPL', [
            'currency' => Yii::$app->currency->active['symbol']
]));
