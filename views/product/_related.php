<div id="relatedProducts" class="_view_grid">
    <?php foreach ($model->relatedProducts as $data){ ?>

        <?php echo $this->render('current_theme.views.shop.category._view_grid',array(
            'data'=>$data
        )); ?>

    <?php } ?>

</div>

