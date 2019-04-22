<?php
use panix\ext\tinymce\TinyMce;
use panix\mod\shop\models\Category;
use panix\engine\bootstrap\Alert;

/**
 * @var $form \panix\engine\bootstrap\ActiveForm
 * @var $model \panix\mod\shop\models\Category
 */
if (Yii::$app->request->get('parent_id')) {
    $parent = Category::findOne(Yii::$app->request->get('parent_id'));
    echo Alert::widget([
        'closeButton' => false,
        'options' => [
            'class' => 'alert-info',
        ],
        'body' => "Добавление в категорию: " . $parent->name,
    ]);
}
?>

<?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'seo_alias')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'image', [
    'parts' => [
        '{buttons}' => $model->getFileHtmlButton('image')
    ],
    'template' => '{label}{beginWrapper}{input}{buttons}{error}{hint}{endWrapper}'
])->fileInput() ?>

<?= $form->field($model, 'description')->widget(TinyMce::class, ['options' => ['rows' => 6]]); ?>
<?= $form->field($model, 'seo_product_title')->textInput(['maxlength' => 255]); ?>
<?= $form->field($model, 'seo_product_description')->textarea(['options' => ['rows' => 6]]); ?>