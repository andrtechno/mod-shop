<div class="carousel slide" id="myCarousel">
    <div class="carousel-inner row">
        <?php foreach ($model->relatedProducts as $data) { ?>
            <div class="item col-md-3">
                <?php echo $this->render('/category/_view_grid', [
                    'model' => $data
                ]); ?>
            </div>
        <?php } ?>
    </div>
    <nav>
        <ul class="control-box pager">
            <li><a data-slide="prev" href="#myCarousel" class=""><i class="glyphicon glyphicon-chevron-left"></i></a>
            </li>
            <li><a data-slide="next" href="#myCarousel" class=""><i class="glyphicon glyphicon-chevron-right"></i></li>
        </ul>
    </nav>
</div>


