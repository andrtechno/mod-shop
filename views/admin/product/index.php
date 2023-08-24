<?php

use panix\engine\grid\GridView;
use panix\engine\widgets\Pjax;
use panix\ext\fancybox\Fancybox;
use panix\mod\shop\bundles\admin\ProductIndex;
use panix\engine\CMS;

echo Fancybox::widget(['target' => '.image a']);

ProductIndex::register($this);
Pjax::begin(['id' => 'pjax-grid-product']);

?>
<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center">
            <h5 class="m-2"><?= $this->context->pageName; ?></h5>
            <div class="ml-auto mr-2">
                <?php
                echo \yii\helpers\Html::a(\panix\engine\Html::icon('filter'), '#collapse-grid-filter', [
                    'data-toggle' => "collapse",
                    'aria-expanded' => "false",
                    'aria-controls' => "collapse-grid-filter",
                    'class' => 'btn btn-sm btn-outline-secondary ml-auto'
                ]);
                ?>
            </div>
        </div>
    </div>


    <?php
    echo $this->render('_grid_filter', ['model' => $searchModel]);
    echo GridView::widget([
        'id' => 'grid-product',
        'tableOptions' => ['class' => 'table table-striped'],
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        //'enableLayout'=>false,
        'layoutPath' => "@shop/views/admin/product/_layout_grid",
        'layoutOptions' => [
            // 'title' => $this->context->pageName,
            // 'beforeContent' => $this->render('_grid_filter', ['model' => $searchModel]),
            // 'buttons' => $this->context->buttons
        ],
        'showFooter' => true,
        'pager' => ['options' => ['class' => 'pagination ml-auto mr-auto mr-lg-0 ml-lg-auto']]
    ]);
    ?>

</div>
<?php
Pjax::end();

?>

<div class="modal fade" id="ProductSetModal" tabindex="-1" role="dialog" aria-labelledby="product-modalLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="product-modalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary"><?= Yii::t('app/default', 'SAVE') ?></button>
            </div>
        </div>
    </div>
</div>

