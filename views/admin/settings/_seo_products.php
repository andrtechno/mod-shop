<?= $form->field($model, 'seo_products')->checkbox(); ?>
<?=

$form->field($model, 'seo_products_title')->hint($model::t('META_TPL', [
            'currency' => Yii::$app->currency->active->symbol
]));
?>

<?=

$form->field($model, 'seo_products_keywords')->hint($model::t('META_TPL', [
            'currency' => Yii::$app->currency->active->symbol
]));
?>
<?=

$form->field($model, 'seo_products_description')->hint($model::t('META_TPL', [
            'currency' => Yii::$app->currency->active->symbol
]));
?>

META_CAT_TPL