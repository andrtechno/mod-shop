<?php

use yii\helpers\Html;
//use app\cms\grid\AdminGridView;
use panix\engine\grid\sortable\SortableGridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\pages\models\PagesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>


<?= \panix\ext\fancybox\Fancybox::widget(['target' => '.image a']); ?>

<?php // echo $this->render('_search', ['model' => $searchModel]);  ?>


<?php
Pjax::begin([
    'id' => 'pjax-container', 'enablePushState' => false,
]);
?>
<?=
SortableGridView::widget([
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layout' => $this->render('@app/web/themes/admin/views/layouts/_grid_layout', ['title' => $this->context->pageName]), //'{items}{pager}{summary}'
    'columns' => [
        [
            'class' => 'yii\grid\SerialColumn',
            'contentOptions' => ['class' => 'text-center']
        ],
        [
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center image'],
            'value' => function($model) {
        //  return $model->getMainImageUrl();
        return ($model->getImage())?Html::a(Html::img($model->getImage()->getUrl('50x50')), $model->getImage()->getUrl()):'no image';
        //return $model->getImage()->getPath('50x50');
    },
        ],
        'name',
        [
            'attribute' => 'price',
            'format' => 'html',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function($model) {
        return $model::formatPrice($model->price) . ' ' . Yii::$app->currency->main->symbol;
    }
        ],
        [
            'class' => 'panix\engine\grid\columns\ActionColumn',
            'template' => '{view} {update} {switch} {delete}',
            'buttons' => [
                'view' => function ($url, $model, $key) {
                    return Html::a('<i class="icon-search"></i>', $model->getUrl(), [
                                'title' => Yii::t('yii', 'Delete'),
                                'target' => '_blank'
                    ]);
                },
                    ],
                /* 'urlCreator' => function ($action, $model, $key, $index) {
                  if ($action === 'view') {
                  $url = $model->getUrl(); // your own url generation logic
                  return $url;
                  }
                  } */
                ]
            ]
        ]);
        ?>
        <?php Pjax::end(); ?>


        <?php
        /*
          \yii\jui\Dialog::begin([
          'clientOptions' => [
          'modal' => true,
          ],
          ]);

          echo 'Dialog contents here...';

          \yii\jui\Dialog::end();
         * */


        /* \yii\bootstrap\Modal::begin([
          'id'=>'modal',
          'header' => '<h2>Hello world</h2>',
          'options'=>['style'=>'width:100%'],
          'toggleButton' => ['label' => 'click me'],
          ]);
         */

        use yii\widgets\ActiveForm;
        ?>

        <?php
        $form = ActiveForm::begin([
                    'id' => 'form-test',
                    'action' => '/images/crop',
                    'enableAjaxValidation' => true,
        ]);
        ?>

        <?php
        $model = new panix\ext\cropper\CropperForm();
        ?>




        <!-- Content -->
        <div class="container">
            <div class="row">
                <div class="col-md-9">
                    <!-- <h3>Demo:</h3> -->
                    <div class="img-container">
                        <img id="image" src="/uploads/l1kUWA3UOT_20482603_810200375834266_8994343786263871488_n.jpg" alt="Picture">

                    </div>
                </div>
                <div class="col-md-3">
                    <!-- <h3>Preview:</h3> -->
                    <div class="docs-preview clearfix">
                        <div class="img-preview preview-lg"></div>
                        <div class="img-preview preview-md"></div>
                        <div class="img-preview preview-sm"></div>
                        <div class="img-preview preview-xs"></div>
                    </div>

                    <!-- <h3>Data:</h3> -->
                    <div class="docs-data">
                        <div class="input-group input-group-sm">
                            <?= $form->field($model, 'coord_x') ?>
                        </div>
                        <div class="input-group input-group-sm">
                            <?= $form->field($model, 'coord_y') ?>
                        </div>
                        <div class="input-group input-group-sm">
                            <?= $form->field($model, 'width') ?>
                        </div>
                        <div class="input-group input-group-sm">
                            <?= $form->field($model, 'height') ?>
                        </div>
                        <div class="input-group input-group-sm">
                            <?= $form->field($model, 'rotate') ?>
                        </div>
                        <div class="input-group input-group-sm">
                            <?= $form->field($model, 'scaleX') ?>
                        </div>
                        <div class="input-group input-group-sm">
                            <?= $form->field($model, 'scaleY') ?>
                        </div>







                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-9 docs-buttons">
                    <?php echo Html::button('das', ['class' => 'btn btn-primary']); ?>
                    <!-- <h3>Toolbar:</h3> -->
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="setDragMode" data-option="move" title="Move">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;setDragMode&quot;, &quot;move&quot;)">
                                <span class="icon-move"></span>
                            </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="setDragMode" data-option="crop" title="Crop">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;setDragMode&quot;, &quot;crop&quot;)">
                                <span class="icon-resize"></span>
                            </span>
                        </button>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="zoom" data-option="0.1" title="Zoom In">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;zoom&quot;, 0.1)">
                                +
                            </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="zoom" data-option="-0.1" title="Zoom Out">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;zoom&quot;, -0.1)">
                                -
                            </span>
                        </button>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="move" data-option="-10" data-second-option="0" title="Move Left">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;move&quot;, -10, 0)">
                                <span class="icon-arrow-left"></span>
                            </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="move" data-option="10" data-second-option="0" title="Move Right">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;move&quot;, 10, 0)">
                                <span class="icon-arrow-right"></span>
                            </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="move" data-option="0" data-second-option="-10" title="Move Up">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;move&quot;, 0, -10)">
                                <span class="icon-arrow-up"></span>
                            </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="move" data-option="0" data-second-option="10" title="Move Down">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;move&quot;, 0, 10)">
                                <span class="icon-arrow-down"></span>
                            </span>
                        </button>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="rotate" data-option="-45" title="Rotate Left">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;rotate&quot;, -45)">
                                <span class="fa fa-rotate-left"></span>
                            </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="rotate" data-option="45" title="Rotate Right">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;rotate&quot;, 45)">
                                <span class="fa fa-rotate-right"></span>
                            </span>
                        </button>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="scaleX" data-option="-1" title="Flip Horizontal">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;scaleX&quot;, -1)">
                                <span class="fa fa-arrows-h"></span>
                            </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="scaleY" data-option="-1" title="Flip Vertical">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;scaleY&quot;, -1)">
                                <span class="fa fa-arrows-v"></span>
                            </span>
                        </button>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="crop" title="Crop">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;crop&quot;)">
                                <span class="fa fa-check"></span>
                            </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="clear" title="Clear">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;clear&quot;)">
                                <span class="fa fa-remove"></span>
                            </span>
                        </button>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="disable" title="Disable">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;disable&quot;)">
                                <span class="fa fa-lock"></span>
                            </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="enable" title="Enable">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;enable&quot;)">
                                <span class="fa fa-unlock"></span>
                            </span>
                        </button>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="reset" title="Reset">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;reset&quot;)">
                                <span class="icon-refresh"></span>
                            </span>
                        </button>
                        <label class="btn btn-primary btn-upload" for="inputImage" title="Upload image file">
                            <input type="file" class="sr-only" id="inputImage" name="file" accept=".jpg,.jpeg,.png,.gif,.bmp,.tiff">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="Import image with Blob URLs">
                                <span class="fa fa-upload"></span>
                            </span>
                        </label>
                        <button type="button" class="btn btn-primary" data-method="destroy" title="Destroy">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;destroy&quot;)">
                                <span class="fa fa-power-off"></span>
                            </span>
                        </button>
                    </div>

                    <div class="btn-group btn-group-crop">
                        <button type="button" class="btn btn-primary" data-method="getCroppedCanvas">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;getCroppedCanvas&quot;)">
                                Get Cropped Canvas
                            </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="getCroppedCanvas" data-option="{ &quot;width&quot;: 160, &quot;height&quot;: 90 }">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;getCroppedCanvas&quot;, { width: 160, height: 90 })">
                                160&times;90
                            </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="getCroppedCanvas" data-option="{ &quot;width&quot;: 320, &quot;height&quot;: 180 }">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;getCroppedCanvas&quot;, { width: 320, height: 180 })">
                                320&times;180
                            </span>
                        </button>
                    </div>

                    <!-- Show the cropped image in modal -->
                    <div class="modal fade docs-cropped" id="getCroppedCanvasModal" aria-hidden="true" aria-labelledby="getCroppedCanvasTitle" role="dialog" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="getCroppedCanvasTitle">Cropped</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body"></div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <a class="btn btn-primary" id="download" href="javascript:void(0);" download="cropped.jpg">Download</a>
                                </div>
                            </div>
                        </div>
                    </div><!-- /.modal -->

                    <button type="button" class="btn btn-primary" data-method="getData" data-option data-target="#putData">
                        <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;getData&quot;)">
                            Get Data
                        </span>
                    </button>
                    <button type="button" class="btn btn-primary" data-method="setData" data-target="#putData">
                        <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;setData&quot;, data)">
                            Set Data
                        </span>
                    </button>
                    <button type="button" class="btn btn-primary" data-method="getContainerData" data-option data-target="#putData">
                        <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;getContainerData&quot;)">
                            Get Container Data
                        </span>
                    </button>
                    <button type="button" class="btn btn-primary" data-method="getImageData" data-option data-target="#putData">
                        <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;getImageData&quot;)">
                            Get Image Data
                        </span>
                    </button>
                    <button type="button" class="btn btn-primary" data-method="getCanvasData" data-option data-target="#putData">
                        <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;getCanvasData&quot;)">
                            Get Canvas Data
                        </span>
                    </button>
                    <button type="button" class="btn btn-primary" data-method="setCanvasData" data-target="#putData">
                        <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;setCanvasData&quot;, data)">
                            Set Canvas Data
                        </span>
                    </button>
                    <button type="button" class="btn btn-primary" data-method="getCropBoxData" data-option data-target="#putData">
                        <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;getCropBoxData&quot;)">
                            Get Crop Box Data
                        </span>
                    </button>
                    <button type="button" class="btn btn-primary" data-method="setCropBoxData" data-target="#putData">
                        <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="$().cropper(&quot;setCropBoxData&quot;, data)">
                            Set Crop Box Data
                        </span>
                    </button>
                    <button type="button" class="btn btn-primary" data-method="moveTo" data-option="0">
                        <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="cropper.moveTo(0)">
                            Move to [0,0]
                        </span>
                    </button>
                    <button type="button" class="btn btn-primary" data-method="zoomTo" data-option="1">
                        <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="cropper.zoomTo(1)">
                            Zoom to 100%
                        </span>
                    </button>
                    <button type="button" class="btn btn-primary" data-method="rotateTo" data-option="180">
                        <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="cropper.rotateTo(180)">
                            Rotate 180°
                        </span>
                    </button>
                    <input type="text" class="form-control" id="putData" placeholder="Get data to here or set data with this value">
                </div><!-- /.docs-buttons -->

                <div class="col-md-3 docs-toggles">
                    <!-- <h3>Toggles:</h3> -->
                    <div class="btn-group d-flex flex-nowrap" data-toggle="buttons">
                        <label class="btn btn-primary active">
                            <input type="radio" class="sr-only" id="aspectRatio0" name="aspectRatio" value="1.7777777777777777">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="aspectRatio: 16 / 9">
                                16:9
                            </span>
                        </label>
                        <label class="btn btn-primary">
                            <input type="radio" class="sr-only" id="aspectRatio1" name="aspectRatio" value="1.3333333333333333">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="aspectRatio: 4 / 3">
                                4:3
                            </span>
                        </label>
                        <label class="btn btn-primary">
                            <input type="radio" class="sr-only" id="aspectRatio2" name="aspectRatio" value="1">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="aspectRatio: 1 / 1">
                                1:1
                            </span>
                        </label>
                        <label class="btn btn-primary">
                            <input type="radio" class="sr-only" id="aspectRatio3" name="aspectRatio" value="0.6666666666666666">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="aspectRatio: 2 / 3">
                                2:3
                            </span>
                        </label>
                        <label class="btn btn-primary">
                            <input type="radio" class="sr-only" id="aspectRatio4" name="aspectRatio" value="NaN">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="aspectRatio: NaN">
                                Free
                            </span>
                        </label>
                    </div>

                    <div class="btn-group d-flex flex-nowrap" data-toggle="buttons">
                        <label class="btn btn-primary active">
                            <input type="radio" class="sr-only" id="viewMode0" name="viewMode" value="0" checked>
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="View Mode 0">
                                VM0
                            </span>
                        </label>
                        <label class="btn btn-primary">
                            <input type="radio" class="sr-only" id="viewMode1" name="viewMode" value="1">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="View Mode 1">
                                VM1
                            </span>
                        </label>
                        <label class="btn btn-primary">
                            <input type="radio" class="sr-only" id="viewMode2" name="viewMode" value="2">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="View Mode 2">
                                VM2
                            </span>
                        </label>
                        <label class="btn btn-primary">
                            <input type="radio" class="sr-only" id="viewMode3" name="viewMode" value="3">
                            <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="View Mode 3">
                                VM3
                            </span>
                        </label>
                    </div>

                    <div class="dropdown dropup docs-options">
                        <button type="button" class="btn btn-primary btn-block dropdown-toggle" id="toggleOptions" data-toggle="dropdown" aria-expanded="true">
                            Toggle Options
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="toggleOptions" role="menu">
                            <li class="form-check" role="presentation">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" name="responsive" checked>
                                    responsive
                                </label>
                            </li>
                            <li class="form-check" role="presentation">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" name="restore" checked>
                                    restore
                                </label>
                            </li>
                            <li class="form-check" role="presentation">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" name="checkCrossOrigin" checked>
                                    checkCrossOrigin
                                </label>
                            </li>
                            <li class="form-check" role="presentation">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" name="checkOrientation" checked>
                                    checkOrientation
                                </label>
                            </li>

                            <li class="form-check" role="presentation">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" name="modal" checked>
                                    modal
                                </label>
                            </li>
                            <li class="form-check" role="presentation">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" name="guides" checked>
                                    guides
                                </label>
                            </li>
                            <li class="form-check" role="presentation">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" name="center" checked>
                                    center
                                </label>
                            </li>
                            <li class="form-check" role="presentation">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" name="highlight" checked>
                                    highlight
                                </label>
                            </li>
                            <li class="form-check" role="presentation">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" name="background" checked>
                                    background
                                </label>
                            </li>

                            <li class="form-check" role="presentation">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" name="autoCrop" checked>
                                    autoCrop
                                </label>
                            </li>
                            <li class="form-check" role="presentation">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" name="movable" checked>
                                    movable
                                </label>
                            </li>
                            <li class="form-check" role="presentation">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" name="rotatable" checked>
                                    rotatable
                                </label>
                            </li>
                            <li class="form-check" role="presentation">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" name="scalable" checked>
                                    scalable
                                </label>
                            </li>
                            <li class="form-check" role="presentation">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" name="zoomable" checked>
                                    zoomable
                                </label>
                            </li>
                            <li class="form-check" role="presentation">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" name="zoomOnTouch" checked>
                                    zoomOnTouch
                                </label>
                            </li>
                            <li class="form-check" role="presentation">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" name="zoomOnWheel" checked>
                                    zoomOnWheel
                                </label>
                            </li>
                            <li class="form-check" role="presentation">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" name="cropBoxMovable" checked>
                                    cropBoxMovable
                                </label>
                            </li>
                            <li class="form-check" role="presentation">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" name="cropBoxResizable" checked>
                                    cropBoxResizable
                                </label>
                            </li>
                            <li class="form-check" role="presentation">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox" name="toggleDragModeOnDblclick" checked>
                                    toggleDragModeOnDblclick
                                </label>
                            </li>
                        </ul>
                    </div><!-- /.dropdown -->

                </div><!-- /.docs-toggles -->
            </div>
        </div>






<?php echo Html::submitButton('ss');?>

















        <?php ActiveForm::end(); ?>
        <script>
            $(function () {
                $('#form-test').on('beforeSubmit', function (e) {
                    var form = $(this);
                    var formData = form.serialize();
                    $.ajax({
                        url: form.attr("action"),
                        type: form.attr("method"),
                        data: formData,
                        success: function (data) {
                           // alert('Test');
                        },
                        error: function () {
                            alert("Something went wrong");
                        }
                    });
                }).on('submit', function (e) {
                    e.preventDefault();
                });
            });
        </script>
        <?php
//\yii\bootstrap\Modal::end();


        \panix\ext\cropper\Cropper::widget();
        ?>

<script>
$(function () {
/* var $image = $('#image');
var cropBoxData;
var canvasData;

$('#modal').on('shown.bs.modal', function () {
$image.cropper({
//  autoCropArea: 0.5,
aspectRatio: 16 / 9,
//ready: function () {
//  $image.cropper('setCanvasData', canvasData);
//  $image.cropper('setCropBoxData', cropBoxData);
// },
crop: function(e) {


$('#cropperform-x').val(Math.round(e.x));
$('#cropperform-y').val(Math.round(e.y));

$('#cropperform-width').val(Math.round(e.width));
$('#cropperform-height').val(Math.round(e.height));
$('#cropperform-scaley').val(e.scaleY);
$('#cropperform-scalex').val(e.scaleX);
$('#cropperform-rotate').val(e.rotate);

}
});
}).on('hidden.bs.modal', function () {
cropBoxData = $image.cropper('getCropBoxData');
canvasData = $image.cropper('getCanvasData');
$image.cropper('destroy');
});*/
});
</script>