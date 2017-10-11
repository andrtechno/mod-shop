<script>
    function checkAllDuplicateAttributes(el){
        if($(el).prev().attr('checked')){
            $('#duplicate_products_dialog form input').attr('checked', false);
            $(el).prev().attr('checked', false);
        }else{
            $('#duplicate_products_dialog form input').attr('checked', true);
            $(el).prev().attr('checked', true);
        }
    }

</script>
<form action="" class="form-horizontal">
    <div class="form-group">
        <div class="col-sm-6"><label for="Product_sku" class="control-label"><?= Yii::t('shop/admin', 'Изображения') ?></label></div>
        <div class="col-sm-6"><input type="checkbox" name="copy[]" value="images" class="check" checked/></div>
    </div>
    <div class="form-group">
        <div class="col-sm-6"><label for="Product_sku" class="control-label"><?= Yii::t('shop/admin', 'Варианты') ?></label></div>
        <div class="col-sm-6"><input type="checkbox" name="copy[]" value="variants" class="check" checked/></div>
    </div>
    <div class="form-group">
        <div class="col-sm-6"><label for="Product_sku" class="control-label"><?= Yii::t('shop/admin', 'Сопутствующие продукты') ?></label></div>
        <div class="col-sm-6"><input type="checkbox" name="copy[]" value="related" class="check" checked/></div>
    </div>
    <div class="form-group">
        <div class="col-sm-6"><label for="Product_sku" class="control-label"><?php echo Yii::t('shop/admin', 'Характеристики') ?></label></div>
        <div class="col-sm-6"><input type="checkbox" name="copy[]" value="attributes" class="check" checked/></div>
    </div>
    <div class="form-group">
        <div class="col-sm-6"><a href="javascript:void(0)" class="control-label" style="color: #309bbf" onclick="return checkAllDuplicateAttributes(this);">Отметить все</a></div>
        <div class="col-sm-6"><input type="checkbox" value="1" class="check" checked="checked"/></div>
    </div>

</form>
