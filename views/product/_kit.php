<?php
use panix\engine\Html;
use panix\engine\CMS;

$sets = [];
foreach ($model->kit as $set) {

    foreach ($set->products as $p) {
        $sets[$p->main_category_id][$set->id] = $p;

    }
}

?>

<?php if ($sets) { ?>
    <div class="container">
        <div class="h3">Вместе дешевле</div>
        <div class="swiper-container swiper-container-h">

            <div class="swiper-wrapper">
                <?php foreach ($sets as $group_id => $set) { ?>
                    <div class="swiper-slide">
                        <div class="row">
                            <div class="col-sm-5">
                                <div style="height: 50px"><strong>Ваш товар:</strong></div>
                                <div>
                                    <?= Html::img($model->getMainImage('300x200')->url, ['alt' => $model->name]); ?></div>
                                <div class="h6 mt-4"><?= $model->name; ?></div>
                                <div>
                                <span class="badge badge-light text-dark">
                                    Код комплекта: <span id="kit-code">
                                        <?= CMS::idToNumber($model->id, 5); ?>
                                        -<?= CMS::idToNumber(key($sets[$group_id]), 5); ?>
                                    </span>
                                </span>
                                </div>
                            </div>
                            <div class="col-sm-7">
                                <div class="swiper-container swiper-container-v">
                                    <div class="swiper-wrapper">

                                        <?php foreach ($set as $set_id => $data) { ?>

                                            <div class="swiper-slide"
                                                 data-kit="<?= CMS::idToNumber($model->id, 5); ?>-<?= CMS::idToNumber($set_id, 5); ?>">
                                                <div class="mt-4">
                                                    <div class="row">
                                                        <div class="col-sm-6">
                                                            <?php
                                                            echo Html::a(Html::img($data->getMainImage('300x200')->url, [
                                                                'alt' => $data->name,
                                                                'class' => 'img-fluid loading'
                                                            ]), $data->getUrl(), []);
                                                            //echo Html::link(Html::image(Yii::app()->createUrl('/site/attachment',array('id'=>33)), $data->name, array('class' => 'img-fluid')), $data->getUrl(), array());
                                                            ?>

                                                            <div class=" mt-4">
                                                                <?= Html::a(Html::encode($data->name), $data->getUrl(), ['class' => 'h6']) ?>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6 d-flex align-items-center">
                                                            <div>
                                                                <div><?= $data->price; ?></div>
                                                                <div><?= Html::a(Yii::t('cart/default', 'BUY_SET'), 'javascript:cart.add_set(' . $set_id . ')', ['class' => 'btn btn-primary']); ?></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="swiper-pagination swiper-pagination-v"></div>
                                    <!-- Add Arrows -->
                                    <div class="swiper-button-up"></div>
                                    <div class="swiper-button-down"></div>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <!-- Add Pagination -->
            <div class="swiper-pagination swiper-pagination-h"></div>
            <!-- Add Arrows -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>
    <?php
    $this->registerCss('
    .swiper-container {
      width: 100%;
      height: 350px;
    }
    .swiper-slide {
      text-align: center;
      /* Center slide text vertically */
      /*display: -webkit-box;
      display: -ms-flexbox;
      display: -webkit-flex;
      display: flex;
      -webkit-box-pack: center;
      -ms-flex-pack: center;
      -webkit-justify-content: center;
      justify-content: center;
      -webkit-box-align: center;
      -ms-flex-align: center;
      -webkit-align-items: center;
      align-items: center;*/
    }
    .swiper-container-v {
      background: #eee;
    }
    .swiper-button-up,
    .swiper-button-down{
        width:30px;
        height:30px;
        position: absolute;
        text-align: center;
        z-index: 10;
        left: 0;
        right: 0;
        margin: 0 auto;
    }
    .swiper-button-up:before,
    .swiper-button-down:before{
        font-family:Pixelion;
        position: absolute;
        width:30px;
        height:30px;
        top:0;
        left:0;
        font-size:24px
    }

    
    .swiper-button-up:before{
        content:"\f007";
    }

    .swiper-button-down:before{
        content:"\f008";
    }
    .swiper-button-up{
        top: 0;
    }
    .swiper-button-down{
        bottom:1rem; 
    }
    

    .swiper-container-horizontal>.swiper-pagination-bullets,
    .swiper-pagination-custom,
    .swiper-pagination-fraction{
        bottom: 0;
    }
    
    .swiper-button-up.swiper-button-disabled,
    .swiper-button-down.swiper-button-disabled{
        color:rgba(0,0,0,0.025);
    }
');

    $this->registerJs("
    var swiperH = new Swiper('.swiper-container-h', {
        spaceBetween: 50,
        //allowTouchMove:false,
        pagination: {
            el: '.swiper-pagination-h',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },

        allowTouchMove:false
    });
    swiperH.on('slideChange',function(){
        console.log('swiperH: slideChange');
    });


$('.swiper-container-v').each(function( index,  element) { 
    var swiperV = new Swiper(element, {
        direction: 'vertical',
        spaceBetween: 50,
        pagination: {
            el: '.swiper-pagination-v',
            clickable: true,
            type:'fraction',
        },
        navigation: {
            nextEl: '.swiper-button-down',
            prevEl: '.swiper-button-up',
        },

        /*on: {
            paginationUpdate: function(swiper,paginationEl){
                console.log(swiper.slides[swiper.activeIndex]);
                console.log(swiper.slides,paginationEl);
            },
            slideChange: function(e){
                console.log('test',e);
            },
        }*/
    });
    swiperV.on('slideChange',function(){
        console.log('swiperV: slideChange');
    });
    
    
    swiperV.on('paginationUpdate',function(swiper,paginationEl){
    var kitCode = $(swiper.slides[swiper.activeIndex]).data('kit');
    $('#kit-code').html(kitCode);
        console.log(swiper,kitCode);
    });
    
});

");
}