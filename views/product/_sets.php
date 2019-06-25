<?php
use panix\engine\Html;

?>


<div class="container">
    <h3>Вместе дешевле</h3>
    <div class="swiper-container swiper-container-h">

        <div class="swiper-wrapper">


            <?php
            foreach ($model->sets as $set) { ?>

                <div class="swiper-slide row">

                    <div class="col-sm-5">
                        <div><strong>Ваш товар:</strong></div>
                        <?php
                        echo Html::a($model->name . '1', $model->getUrl());
                        ?>
                    </div>
                    <div class="col-sm-5">
                        <div class="swiper-container swiper-container-v">
                            <div class="swiper-wrapper">
                                <?php foreach ($set->products as $data) { ?>
                                    <div class="swiper-slide">
                                        <?php //echo $data->discount; ?>
                                        <?php echo $data->product2->name; ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="swiper-pagination swiper-pagination-v"></div>
                            <!-- Add Arrows -->
                            <div class="swiper-button-up"></div>
                            <div class="swiper-button-down"></div>
                        </div>

                    </div>
                    <div class="col-sm-2">
                        <?php
                        //echo $this->render('/category/_view_grid', [
                        //     'model' => $data
                        //]);
                        echo Html::a(Yii::t('cart/default', 'BUY_SET'), 'javascript:cart.add_set(' . $set->id . ')', array('class' => 'btn btn-primary'));
                        ?>
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



