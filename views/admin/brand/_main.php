<?php
use panix\ext\tinymce\TinyMce;

/**
 * @var panix\engine\bootstrap\ActiveForm $form
 * @var \panix\mod\shop\models\Brand $model
 */
?>
<?php
//echo $form->field($model, 'name')->widget(\panix\engine\bootstrap\TinyMceLang::class, []);

//Yii::$app->db->schema->refresh()
?>

<?= $form->field($model, 'name')->textInput(['maxlength' => 255]); ?>
<?= $form->field($model, 'slug')->textInput(['maxlength' => 255]); ?>
<?= $form->field($model, 'image', [
    'parts' => [
        '{buttons}' => $model->getFileHtmlButton('image')
    ],
    'template' => '<div class="col-sm-4 col-lg-2">{label}</div>{beginWrapper}{input}{buttons}{error}{hint}{endWrapper}'
])->fileInput() ?>

<?php
echo $form->field($model, 'description')->widget(TinyMce::class, [
    'options' => ['rows' => 6],
]);

?>
