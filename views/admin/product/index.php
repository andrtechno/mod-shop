<?php
use panix\engine\grid\GridView;
use panix\engine\widgets\Pjax;
use panix\ext\fancybox\Fancybox;
use panix\mod\shop\bundles\admin\ProductIndex;
use panix\engine\CMS;

echo Fancybox::widget(['target' => '.image a']);
//$filterCount = 0;
/*array_push($this->context->buttons,[
    'label' => \panix\engine\Html::icon('filter') . (($filterCount)?'<span class="badge badge-danger" style="font-size:75%">' . $filterCount . '</span>':''),
    'url' => '#collapse-grid-filter',
    'options' => [
        'data-toggle' => "collapse",
        'aria-expanded' => "false",
        'aria-controls' => "collapse-grid-filter",
        'class' => 'btn btn-sm btn-outline-secondary'
    ]
]);*/

/*
$pattern = '#^catalog/(?P<slug>[0-9a-zA-Z_\/\-]+)/(?P<filter>\/[\w,\/]+)$#u';

$pathInfo = 'catalog/ukhod-dla-volos/kondicioner-dla-volos/filter/size/13,5/brand/1';
if (!preg_match($pattern, $pathInfo, $matches)) {
  //  return false;
}
CMS::dump($matches);die;
*/
?>
<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center">
            <h5 class="m-2"><?= $this->context->pageName; ?></h5>
            <div class="ml-auto mr-2">


                <?php
                echo \yii\helpers\Html::a(\panix\engine\Html::icon('filter'),'#collapse-grid-filter',[
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
ProductIndex::register($this);
Pjax::begin(['id'=>'pjax-grid-product']);

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
    'pager'=>['options'=>['class'=>'pagination ml-auto mr-auto mr-lg-0 ml-lg-auto']]
]);
Pjax::end();
?>

</div>




