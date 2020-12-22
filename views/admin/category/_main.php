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
        'options' => [
            'class' => 'alert-info',
        ],
        'body' => "Добавление в категорию: " . $parent->name,
    ]);
}
?>

<?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'slug')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'use_seo_parents')->checkbox() ?>

<?= $form->field($model, 'meta_title')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'meta_description')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'h1')->textInput(['maxlength' => 255]) ?>

<?= $form->field($model, 'image', [
    'parts' => [
        '{buttons}' => $model->getFileHtmlButton('image')
    ],
    'template' => '<div class="col-sm-4 col-lg-2">{label}</div>{beginWrapper}{input}{buttons}{error}{hint}{endWrapper}'
])->fileInput() ?>


<?= $form->field($model, 'description')->widget(TinyMce::class, ['options' => ['rows' => 6]]); ?>
