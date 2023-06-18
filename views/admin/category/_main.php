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
        'body' => Yii::t('shop/Category', 'ADD_TO_PARENT', $parent->name),
    ]);
}
?>

<?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'slug')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'image', [
    'parts' => [
        '{buttons}' => $model->getFileHtmlButton('image')
    ],
    'template' => '{label}{beginWrapper}{input}{buttons}{error}{hint}{endWrapper}'
])->fileInput() ?>
<?= $form->field($model, 'icon', [
    'parts' => [
        '{buttons}' => $model->getFileHtmlButton('icon')
    ],
    'template' => '{label}{beginWrapper}{input}{buttons}{error}{hint}{endWrapper}'
])->fileInput() ?>


<?= $form->field($model, 'description')->widget(TinyMce::class, ['options' => ['rows' => 6]]); ?>
