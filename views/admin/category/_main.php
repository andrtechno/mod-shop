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

<?php

$test = Yii::$app->chatgpt->completion([
    'model' => 'text-davinci-003',
   // 'prompt'=>'<email_subject>\nFrom:<customer_name>\nDate:<date>\nContent:<email_body>\n\n###\n\n',
    //'prompt'=>"Item=handbag, Color=army_green, price=$99, size=S->",
    //'prompt'=>"Item=Телефон samsung, Color=Синий, price=100грн, size=Боль->",

   // 'prompt' => 'Опиши товар в 1000 символов, по ключевым словам "Туфли, лето, женщины, черный, искусственная кожа, Высота платформы 3 см". только не добавляй лишнего', //

'prompt'=>"Опиши товар, по ключевым словам \"Туфли, лето, женщины, черный, искусственная кожа, Высота платформы 3 см\". только не добавляй лишнего",
'suffix'=>'Pixelion',
    'temperature' => 0.9, //Какую температуру выборки использовать, от 0 до 2. Более высокие значения, такие как 0,8, сделают вывод более случайным, а более низкие значения, такие как 0,2, сделают его более сфокусированным и детерминированным.
    'max_tokens' => 3000,
    'user'=>'Panix',
    'n'=>2,
    'top_p'=>0.2,
    'frequency_penalty' => 0,
    'stop'=>'!!!!'
    //'presence_penalty' => 0.6,
    //"completion"=>"<numerical_category>"
    //"completion"=>" This stylish small green handbag will add a unique touch to your look, without costing you a fortune."
]);
foreach ($test->choices as $choice){
    echo $choice->text;
    echo '<br>';
    echo '<br>';
}
//\panix\engine\CMS::dump($test);

/*
$img = Yii::$app->chatgpt->image([
    'prompt' => 'car on track',
    'n' => 1,
    'size' => '512x512',
    'response_format' => 'url',
]);
if(!isset($img->error)){
    echo \yii\helpers\Html::img($img->data[0]->url);
}else{
    echo $img->error->message;
}*/



/*
$chat = Yii::$app->chatgpt->chat([
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        [
            "role" => "system",
            "content" => "Опиши товар, по ключевым словам \"Туфли, лето, женщины, черный, искусственная кожа, Высота платформы 3 см\". только не добавляй лишнего"
        ],
    ],
    'temperature' => 1.0,
    'max_tokens' => 4000,
    'frequency_penalty' => 0,
    'presence_penalty' => 0,
]);
\panix\engine\CMS::dump($chat);
*/
// decode response
//$d = json_decode($chat);
// Get Content
//print_r($d->choices[0]->message->content);

