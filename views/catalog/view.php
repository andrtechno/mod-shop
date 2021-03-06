<?php

use yii\helpers\Url;
use yii\helpers\Html;
use panix\mod\shop\widgets\categories\CategoriesWidget;
use panix\mod\shop\widgets\filtersnew\FiltersWidget;

?>


<div class="row">
    <div class="col-sm-3">
        <?= CategoriesWidget::widget([]) ?>


        <?php

        echo FiltersWidget::widget([
            'model' => $this->context->dataModel,
            'attributes' => $this->context->eavAttributes,
            'query' => $this->context->filterQuery,
            'tagCount' => false,
            'searchItem' => 0,
        ]);

        ?>




    </div>   
    <div class="col-sm-9">
        <?= Html::a('back', Url::previous(),['class'=>'btn btn-default']); ?>
        <h1><?= $this->context->dataModel->name ?></h1>
        <?php echo $this->render('_sorting', ['itemView' => $itemView]); ?>

        <div id="listview-ajax">
        <?php
        echo $this->render('@shop/views/catalog/listview',[
            'itemView' => $itemView,
            'provider' => $provider,
        ]);
        ?>
        </div>
    </div>
</div>