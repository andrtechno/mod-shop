<?php

namespace panix\mod\shop\controllers\admin;

use Mpdf\Mpdf;
use panix\engine\CMS;
use Yii;
use panix\engine\controllers\AdminController;

class PrintController extends AdminController
{

    public $icon = 'print';


    public function actionIndex()
    {
        $mpdf = new Mpdf([
            // 'debug' => true,
            'mode' => 'utf-8',
            'default_font_size' => 9,
            'default_font' => 'verdana',
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_footer' => 0,
            'margin_header' => 0,
            'format' => 'A4',
          //  'autoPageBreak' => false
        ]);
        //$mpdf->mirrorMargins = true;
       // $mpdf->autoPageBreak = false;
      //  $mpdf->SetDisplayMode('real', 'two');
        $mpdf->SetCreator(Yii::$app->name);
        $mpdf->SetAuthor(Yii::$app->user->getDisplayName());

        //$mpdf->SetProtection(['copy','print'], 'asdsad', 'MyPassword');
        //$mpdf->SetTitle('asdasd');
       // $mpdf->AddPage();
        $mpdf->WriteHTML(file_get_contents(Yii::getAlias('@shop/views/admin/print/test.css')), 1);
        //$mpdf->WriteHTML($this->renderPartial('_pdf_order', ['model' => $model]), 2);
        $mpdf->WriteHTML($this->renderPartial('index', []), 2);
        echo $mpdf->Output("sdadasd.pdf", 'I');
        die;
    }

    public function actionTermo()
    {
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => [58, 40],
            'margin_top' => 1,
            'margin_bottom' => 0,
            'margin_left' => 1,
            'margin_right' => 1,
            'margin_footer' => 0,
            'margin_header' => 0,
            'mirrorMargins' => false,
            'extrapagebreak'=>false,
        ]);
       // $mpdf->extrapagebreak=false;
        $pr = \panix\mod\shop\models\Product::find()->limit(15)->all();
        foreach ($pr as $k=>$p) {
            $mpdf->AddPage();
           // $mpdf->WriteHTML('<pagebreak sheet-size="58mm 40mm" />');
            $mpdf->WriteHTML($this->renderPartial('_termo', ['product'=>$p]));
        }


        $mpdf->Output();
        die;
    }
}
