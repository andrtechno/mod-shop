<?php

namespace panix\mod\shop\controllers;

use panix\mod\shop\models\ProductImage;
use Yii;
use panix\engine\controllers\WebController;
use yii\web\Controller;
use yii\web\HttpException;

class ImageController extends Controller
{

    public function actionGetFile($dirtyAlias)
    {



        $dotParts = explode('.', $dirtyAlias);
        if (!isset($dotParts[1])) {
            throw new HttpException(404, 'Image must have extension');
        }
        $dirtyAlias = $dotParts[0];

        $size = isset(explode('_', $dirtyAlias)[1]) ? explode('_', $dirtyAlias)[1] : false;
        $alias = isset(explode('_', $dirtyAlias)[0]) ? explode('_', $dirtyAlias)[0] : false;


        /** @var $image ProductImage */
        $image = \Yii::$app->getModule('shop')->getImage($alias);

        if ($image) {
            $response = Yii::$app->getResponse();
            $response->format = \yii\web\Response::FORMAT_RAW;
            // $image->getContent($size)->show();

            $i = $image->getContent($size);


            if ($i instanceof \panix\engine\components\ImageHandler) {
                $response->format = \yii\web\Response::FORMAT_RAW;
                $i->show();
                die;
            } else {

                if ($i) {
                    $imginfo = getimagesize(Yii::getAlias('@webroot') . $i);
                    header("Content-type: {$imginfo['mime']}");
                    return readfile(Yii::getAlias('@webroot') . $i);
                } else {

                    throw new HttpException(404, 'There is no images [1]');
                }

                // die;
            }
        } else {
            throw new HttpException(404, 'There is no images');
        }
    }


}
