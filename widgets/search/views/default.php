<?php

use panix\engine\Html;
use yii\jui\AutoComplete;
use yii\web\JsExpression;
use yii\helpers\Url;
$id = $this->context->id;
?>

<div class="search-box m-auto w-100">
    <?= Html::beginForm(Yii::$app->urlManager->createUrl(['/shop/search/index', 'q' => $value]), 'post', ['id' => 'search-form-'.$id]) ?>
    <div class="input-group">

        <?php
        echo AutoComplete::widget([
            'id' => 'searchInput-'.$id,
            'name' => 'q',
            'value' => $value,
            //'model'=>$searchModel,
            //'attribute' => 'name',
            'options' => ['placeholder' => Yii::t('shop/default','SEARCH'), 'class' => 'form-control'],
            'clientOptions' => [
                'source' => new JsExpression('function (request, response) {
                    $.ajax({
                        url: "' . Url::to(['/shop/search/ajax']) . '",
                        data: { q: request.term },
                        dataType: "json",
                        success: response,
                        beforeSend: function(){
                            $("#searchInput-'.$id.'").addClass("loading");
                        },
                        complete: function(){
                            $("#searchInput-'.$id.'").removeClass("loading");
                        },
                        error: function () {
                            response([]);
                        },
                        success:function(data){
                        $("#search-autocomplete-result").html(data);
                        }
                    });
                }'),
                'minLength' => 0,
                'create' => new JsExpression('function( event, ui ) {
                    $("#searchInput-'.$id.'").autocomplete( "instance" )._renderItem = function( ul, item ) {
                        return $( "<li></li>" ).data( "item.autocomplete", item ).append(item.renderItem).appendTo( ul );
                    };
                }'),
                'select' => new JsExpression('function( event, ui ) {
                    window.location.href = ui.item.url;
                    return false;
                }'),
            ],
        ]);
        ?>

        <div class="input-group-append"><?= Html::submitButton('Найти', ['class' => 'btn btn-secondary']); ?></div>
    </div>

    <small class="text-muted">Например: <strong>Apple X</strong></small>
    <?= Html::endForm() ?>
</div>
<?php
$this->registerJs("
    $(function () {
        $('#searchQuery').keydown(function (event) {
            if (event.which == 13) {
                $('#search-form').submit();
            }
        });
    });
", \yii\web\View::POS_END);

?>
