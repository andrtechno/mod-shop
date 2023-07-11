<?php

use panix\engine\Html;
use panix\engine\bootstrap\ActiveForm;

/**
 * @var \panix\mod\shop\models\Category $model
 */
?>

<div class="row">
    <div class="col-sm-12 col-md-7 col-lg-8">
        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
        <div class="card">
            <div class="card-header">
                <h5><?= Html::encode($this->context->pageName) ?></h5>
            </div>

            <div class="card-body">
                <?php
                $tabs = [];

                $tabs[] = [
                    'label' => $model::t('TAB_MAIN'),
                    'content' => $this->render('_main', ['form' => $form, 'model' => $model]),
                    'active' => true,
                    'encode' => false,
                    'options' => ['class' => 'text-center nav-item'],
                ];
                $tabs[] = [
                    'label' => Yii::t('seo/default', 'TAB_SEO'),
                    'content' => $this->render('_seo', ['form' => $form, 'model' => $model]),
                    'options' => ['class' => 'text-center nav-item'],
                ];

                echo \panix\engine\bootstrap\Tabs::widget([
                    //'encodeLabels'=>true,
                    'options' => [
                        'class' => 'nav-pills'
                    ],
                    'items' => $tabs,
                ]);

                ?>


            </div>
            <div class="card-footer text-center">
                <?= $model->submitButton(); ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <div class="col-sm-12 col-md-5 col-lg-4">
        <?= $this->render('_category', ['model' => $model]); ?>
    </div>
</div>


<?php

$this->registerJs("
    /*$(document).on('beforeValidate', '#chatgpt-form', function (event, messages, deferreds) {
        //console.log('beforeValidate',messages);
        $(this).find('button[type=\"submit\"]').attr('disabled','disabled');
    }).on('afterValidate', '#chatgpt-form', function (event, messages, errorAttributes) {
        //console.log('afterValidate');
        var countErrors = 0;
        if (errorAttributes.length) {
            if(!countErrors){
                $(this).find('button[type=\"submit\"]').removeAttr('disabled');
            }else{
                //$(this).find('button[type=\"submit\"]').attr('disabled','disabled');
            }
        }else{
            //$(this).find('#cart-submit').removeAttr('disabled');
        }
    }).on('beforeSubmit', '#chatgpt-form', function (event) {
        //console.log('beforeSubmit');
        //$(this).find('button[type=\"submit\"]').removeAttr('disabled');
    });*/
    
    
    $('#chatgpt-form').on('beforeSubmit', function(e) {
        var form = $(this);
        var formData = form.serialize();
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: formData,
            dataType: 'json',
            beforeSend: function () {
                $('#exampleModal .modal-content').addClass('pjax-loading');
            },
            success: function (data) {
                $('#exampleModal .modal-content').removeClass('pjax-loading');
                if(data.success){
                    if(data.action == 'apply'){
                        tinymce.activeEditor.setContent(data.result);
                        //tinymce.get('#category-description').setContent(data.result);
                        $('#exampleModal').modal('hide');
                        $('#btn-next').html('Далее');
                        $('#btn-changes').addClass('d-none');
                        $('#dynamicmodel-result').attr('disabled',true).html('');
                    }else{
                        $('#dynamicmodel-result').html(data.result);
                        $('#dynamicmodel-result').attr('disabled',false);
                        $('#btn-changes').removeClass('d-none');
                        $('#btn-next').html('Применить');
                    }
                    
                }else{
                    common.notify(data.message,'error');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR, textStatus, errorThrown);
                $('#exampleModal .modal-content').removeClass('pjax-loading');
            }
        });

    }).on('submit', function(e){
        e.preventDefault();
    });
    
    
    
    $(document).on('click', '#btn-changes', function(e) {
        $('#dynamicmodel-result').attr('disabled',true).html('');
        var form = $('#chatgpt-form');
        var formData = form.serialize();

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: formData,
            dataType: 'json',
            beforeSend: function () {
                $('#exampleModal .modal-content').addClass('pjax-loading');

            },
            success: function (data) {
                $('#exampleModal .modal-content').removeClass('pjax-loading');
                if(data.success){

                        $('#dynamicmodel-result').html(data.result);
                        $('#dynamicmodel-result').attr('disabled',false);
                        $('#btn-changes').removeClass('d-none');
                        $('#btn-next').html('Применить');

                    
                }else{
                    common.notify(data.message,'error');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR, textStatus, errorThrown);
                $('#exampleModal .modal-content').removeClass('pjax-loading');
            }
        });

    }).on('submit', function(e){
        e.preventDefault();
    });
    
    
");
$this->registerCss('
[data-notify="container"]{
z-index:2000 !important;
}
');
?>

<?php if (!$model->isNewRecord) { ?>
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width: 800px !important;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">ChatGPT</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php $form2 = ActiveForm::begin(['layout' => 'default', 'id' => 'chatgpt-form', 'action' => ['/admin/shop/category/gpt', 'id' => $model->id]]); ?>
            <div class="modal-body p-0 m-0">

                <?php
                $modelGRP = new \yii\base\DynamicModel(['prompt', 'temperature']);
                $modelGRP->addRule(['prompt', 'max_tokens', 'frequency_penalty', 'presence_penalty', 'temperature', 'n'], 'required');
                $modelGRP->addRule(['result'], 'string');
                $modelGRP->setAttributeLabels(
                    [
                        'prompt' => 'Запрос',
                        'n' => 'Количество вариантов ответа',
                        'max_tokens' => 'Макс. количество токенов',
                    ]
                );
                ?>
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab"
                           aria-controls="home" aria-selected="true">Запрос</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab"
                           aria-controls="profile" aria-selected="false">Настройки</a>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active pl-3 pr-3" id="home" role="tabpanel"
                         aria-labelledby="home-tab">

                            <?php
                            $parent = $model->parent()->one();
                            $var = 'Напиши текст про преимущества покупки {parent_category} {current_category} оптом в интернет-магазине. До 2500 символов. Упомяни что наш магазин предлагает доступные цены и большой выбор. не упоминай год.';
                            ?>
                            <?= $form2->field($modelGRP, 'prompt')->textarea(['rows' => 7, 'value' => $var]) ?>
                            <?= $form2->field($modelGRP, 'result')->textarea(['rows' => 7, 'value' => $modelGRP->result,'disabled'=>true]); ?>

                        <div><code>{current_category}</code> - <?= $model->name; ?></div>
                        <div><code>{parent_category}</code> - <?= $parent->name; ?></div>
                    </div>
                    <div class="tab-pane fade pl-3 pr-3" id="profile" role="tabpanel" aria-labelledby="profile-tab">


                        <?= $form2->field($modelGRP, 'temperature')->textInput(['value' => '0.9'])->hint('Какую температуру выборки использовать, от 0 до 2. Более высокие значения, такие как 0,8, сделают вывод более случайным, а более низкие значения, такие как 0,2, сделают его более сфокусированным и детерминированным.') ?>
                        <?= $form2->field($modelGRP, 'max_tokens')->textInput(['value' => '3000']) ?>
                        <?php //echo $form2->field($modelGRP, 'n')->textInput(['value' => '1'])->hint('чем больше вариантов тем больше будет токенов') ?>
                        <?= $form2->field($modelGRP, 'frequency_penalty')->textInput(['value' => '0'])->hint('Число от -2,0 до 2,0. Положительные значения штрафуют новые токены в зависимости от их текущей частоты в тексте, уменьшая вероятность того, что модель дословно повторит одну и ту же строку.') ?>
                        <?= $form2->field($modelGRP, 'presence_penalty')->textInput(['value' => '0.6'])->hint('Число от -2,0 до 2,0. Положительные значения штрафуют новые токены в зависимости от того, появляются ли они в тексте до сих пор, что увеличивает вероятность того, что модель будет говорить о новых темах.'); ?>
                    </div>

                </div>

            </div>
            <div class="modal-footer">
                <?php echo Html::button('Другой вариант', ['class' => 'btn btn-primary d-none','name'=>'change','id'=>'btn-changes','value'=>1]); ?>
                <?php echo Html::submitButton('Далее', ['class' => 'btn btn-success','id'=>'btn-next','name'=>'next']); ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php } ?>

