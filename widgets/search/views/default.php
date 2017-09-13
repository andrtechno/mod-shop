<?php

use panix\engine\Html;
use yii\jui\AutoComplete;
use yii\web\JsExpression;
use yii\helpers\Url;
?>

<div id="search-box">
    <?= Html::beginForm(Yii::$app->urlManager->createUrl(['/shop/category/search', 'q' => $value]), 'post', ['id' => 'search-form']) ?>
    <div class="input-group">

        <?php
        echo AutoComplete::widget([
            'id' => 'searchInput',
            'name' => 'q',
            'value' => $value,
            //'model'=>$searchModel,
            //'attribute' => 'name',
            'options' => ['placeholder' => 'Поиск...', 'class' => 'form-control'],
            'clientOptions' => [
                'source' => new JsExpression('function (request, response) {
                    $.ajax({
                        url: "' . Url::to(['/shop/category/search']) . '",
                        data: { q: request.term },
                        dataType: "json",
                        success: response,
                        beforeSend: function(){
                            $("#searchInput").addClass("loading");
                        },
                        complete: function(){
                            $("#searchInput").removeClass("loading");
                        },
                        error: function () {
                            response([]);
                        }
                    });
                }'),
                'minLength' => 0,
                'create' => new JsExpression('function( event, ui ) {
                    $("#searchInput").autocomplete( "instance" )._renderItem = function( ul, item ) {
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

        <div class="input-group-btn"><?= Html::submitButton('Найти', ['class' => 'btn btn-default']); ?></div>
    </div>



    <?= Html::endForm() ?>
    <?= Html::a('GO', 'javascript:void(0);', array('onClick' => '$("#search-form").submit();', 'class' => 'search-button')); ?>
</div>
<script>
    $(function () {
        $('#searchQuery').keydown(function (event) {
            if (event.which == 13) {
                $('#search-form').submit();
            }
        });
    });
</script>