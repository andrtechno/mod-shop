<?php
use panix\ext\tinymce\TinyMce;

/**
 * @var panix\engine\bootstrap\ActiveForm $form
 * @var \panix\mod\shop\models\Manufacturer $model
 */
?>

<?= $form->field($model, 'name')->textInput(['maxlength' => 255]); ?>
<?= $form->field($model, 'slug')->textInput(['maxlength' => 255]); ?>
<?= $form->field($model, 'image', [
    'parts' => [
        '{buttons}' => $model->getFileHtmlButton('image')
    ],
    'template' => '{label}{beginWrapper}{input}{buttons}{error}{hint}{endWrapper}'
])->fileInput() ?>

<?php
echo $form->field($model, 'description')->widget(TinyMce::class, [
    'options' => ['rows' => 6],
]);

?>