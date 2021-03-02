<?php

namespace panix\mod\shop\widgets\notify;

use panix\engine\data\Widget;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\ProductNotifications;
use Yii;
use yii\base\Event;
use yii\web\View;

class NotifyWidget extends Widget
{
    public $model;
    public $modalView = 'modal';

    public function run()
    {
        $product = Product::findOne($this->model->id);
        $model = new ProductNotifications;
        $this->view->on(View::EVENT_END_BODY, function ($event) use ($model, $product) {
            echo $this->render($this->modalView, ['model' => $model, 'product' => $product]);

        });
        $js = <<<JS
    $('form#notify-form').on('beforeSubmit', function(){
       var data = $(this).serialize();
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: data,
            dataType:'json',
            success: function(response){
                console.log(response);
                if(response.success){
                    $('#modal-notify').modal('hide');
                    common.notify(response.message,'success');
                    
                }else{
                    common.notify(response.message);
                }
            },
            error: function(){
               // alert('Error!');
            }
        });
        return false;
    });
JS;

        $this->view->registerJs($js);
        return $this->render($this->skin, ['model' => $model, 'product' => $product]);
    }

}
