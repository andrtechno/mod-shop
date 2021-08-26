<?php

namespace panix\mod\shop\models;

use panix\engine\CMS;
use panix\mod\shop\components\ExternalFinder;
use Yii;
use panix\engine\db\ActiveRecord;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\HttpException;


/**
 * Class Kit
 * @property integer $id
 * @property integer $owner_id
 * @property integer $product_id
 */
class ProductImage extends ActiveRecord
{

    const MODULE_ID = 'shop';
    private $existImage = true;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop__product_image}}';
    }

    public static function find22()
    {
        return 'fff';
        $t = new ActiveQuery(get_called_class());
        if ($t) {
            return $t;
        }

    }

    public function getProducts()
    {
        return $this->hasMany(Product::class, ['id' => 'product_id']);
    }

    public function getExtension()
    {
        $ext = pathinfo($this->getPathToOrigin(), PATHINFO_EXTENSION);
        return $ext;
    }

    public static function getSort()
    {
        return new \yii\data\Sort([
            'attributes' => [
                'alt_title',
            ],
        ]);
    }

    public function getNoImagePath()
    {
        return Yii::getAlias('@uploads') . DIRECTORY_SEPARATOR . 'no-image.jpg';
    }

    public function getNoImageUrl()
    {
        return '/uploads/no-image.jpg';
    }

    public function getPathToOrigin()
    {
        $filePath = Yii::getAlias('@uploads/store/product') . DIRECTORY_SEPARATOR . $this->product_id . DIRECTORY_SEPARATOR . $this->filename;
        return $filePath;
    }

    public function getUrlToOrigin()
    {
        $base = '/uploads/store/product/' . $this->product_id . '/' . $this->filename;
        $filePath = $base;
        return $filePath;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id'], 'required'],
            [['product_id'], 'integer'],
        ];
    }

    public function setMain($is_main = true)
    {
        if ($is_main) {
            $this->is_main = true;
        } else {
            $this->is_main = false;
        }
    }

    public function afterDelete()
    {

        $fileToRemove = $this->getPathToOrigin();

        if (preg_match('@\.@', $fileToRemove) and is_file($fileToRemove)) {
            unlink($fileToRemove);
            if (file_exists(Yii::getAlias("@app/web/assets/product/{$this->product_id}"))) {
                FileHelper::removeDirectory(Yii::getAlias("@app/web/assets/product/{$this->product_id}"));
            }
        }
        if (Yii::$app->hasModule('csv')) {
            $external = new ExternalFinder('{{%csv}}');
            $external->deleteObject(ExternalFinder::OBJECT_IMAGE, $this->id);
        }
        parent::afterDelete();
    }

    // public $assetPath;
    public function createVersion___($imagePath, $size = false, array $options = [])
    {
        //if(!$size){
        //    return "/uploads/store/product/{$this->product_id}/{$this->filename}";
        //}
        $configApp = Yii::$app->settings->get('shop');
        if (!isset($options['watermark'])) {
            $options['watermark'] = $configApp->watermark_enable;
        }
        $sizes = explode('x', $size);

        $isSaveFile = false;
        if (isset($sizes[0]) && isset($sizes[1])) {
            $imageAssetPath = Yii::getAlias('@app/web/assets/product') . DIRECTORY_SEPARATOR . $this->product_id . DIRECTORY_SEPARATOR . $size;
            $assetPath = "/assets/product/{$this->product_id}/{$size}";
        } else {
            $imageAssetPath = Yii::getAlias('@app/web/assets/product') . DIRECTORY_SEPARATOR . $this->product_id;
            $assetPath = '/assets/product/' . $this->product_id;
        }

        if (!file_exists($imagePath)) {
            return false;
        }

        /** @var $img \panix\engine\components\ImageHandler */

        $img = Yii::$app->img;
        $img->load($imagePath);
        //echo basename($img->getFileName());
        $fileInfo = explode('.', basename($img->getFileName()));
        $filename = $fileInfo[0];
        if (isset($options['prefix'])) {
            $filename .= '_' . md5(serialize($options));
        }
        $extension = $fileInfo[1];

        if (!file_exists($imageAssetPath . DIRECTORY_SEPARATOR . $filename . '.' . $extension)) {
            $isSaveFile = true;
            FileHelper::createDirectory($imageAssetPath, 0777);
        } else {
            return $assetPath . '/' . $filename . '.' . $extension;
        }


        if ($sizes) {
            $img->resize((!empty($sizes[0])) ? $sizes[0] : 0, (!empty($sizes[1])) ? $sizes[1] : 0);
        }
        if (in_array(mb_strtolower($this->getExtension()), ['png', 'svg']) || !$this->existImage) {
            $options['watermark'] = false;
        }
        if (isset($options['grayscale'])) {
            $img->grayscale();
        }
        if (isset($options['text'])) {
            $img->text($options['text'], Yii::getAlias('@vendor/panix/engine/assets/assets/fonts') . '/Exo2-Light.ttf', $img->getWidth() / 100 * 5, [114, 114, 114], $img::POS_CENTER_BOTTOM, 0, $img->getHeight() / 100 * 5, 0, 0);
        }
        if ($options['watermark'] && $this->existImage) {

            $offsetX = isset($configApp->attachment_wm_offsetx) ? $configApp->attachment_wm_offsetx : 10;
            $offsetY = isset($configApp->attachment_wm_offsety) ? $configApp->attachment_wm_offsety : 10;
            $corner = isset($configApp->attachment_wm_corner) ? $configApp->attachment_wm_corner : 4;
            $path = Yii::getAlias('@uploads') . DIRECTORY_SEPARATOR . $configApp->attachment_wm_path;

            $wm_width = 0;
            $wm_height = 0;
            if (file_exists($path)) {
                if ($imageInfo = @getimagesize($path)) {
                    $wm_width = (float)$imageInfo[0] + $offsetX;
                    $wm_height = (float)$imageInfo[1] + $offsetY;
                }

                $toWidth = min($img->getWidth(), $wm_width);

                if ($wm_width > $img->getWidth() || $wm_height > $img->getHeight()) {
                    $wm_zoom = round($toWidth / $wm_width / 3, 1);
                } else {
                    $wm_zoom = false;
                }

                if (!($img->getWidth() <= $wm_width) || !($img->getHeight() <= $wm_height) || ($corner != 10)) {
                    $img->watermark($path, $offsetX, $offsetY, $corner, $wm_zoom);
                }

            }
        }


        if ($isSaveFile) {
            if (isset($sizes[0]) && isset($sizes[1])) {
                // $img->thumb($sizes[0], $sizes[1]);
            }
            /*$fileInfo = explode('.',basename($img->getFileName()));
            $filename = $fileInfo[0];
            if(isset($options['prefix'])){
                $filename .= '_'.md5(serialize($options));
            }
            $extension = $fileInfo[1];*/

            $img->save($imageAssetPath . DIRECTORY_SEPARATOR . $filename . '.' . $extension);

        }
        return $assetPath . '/' . basename($img->getFileName());
        // return $img;

    }


    public function get($size = false, array $options = [])
    {
        //if(!$size){
        //    return "/uploads/store/product/{$this->product_id}/{$this->filename}";
        //}
        $configApp = Yii::$app->settings->get('shop');
        if (!isset($options['watermark'])) {
            $options['watermark'] = $configApp->watermark_enable;
        }
        $sizes = explode('x', $size);
//print_r($options);die;
        $isSaveFile = false;
        if (isset($sizes[0]) && isset($sizes[1])) {
            $imageAssetPath = Yii::getAlias('@app/web/assets/product') . DIRECTORY_SEPARATOR . $this->product_id . DIRECTORY_SEPARATOR . $size;
            $assetPath = "/assets/product/{$this->product_id}/{$size}";

        } else {
            $imageAssetPath = Yii::getAlias('@app/web/assets/product') . DIRECTORY_SEPARATOR . $this->product_id;
            $assetPath = '/assets/product/' . $this->product_id;
        }
        $imagePath = Yii::getAlias("@uploads/store/product/{$this->product_id}") . DIRECTORY_SEPARATOR . $this->filename;
        if (!file_exists($imagePath) || !is_file($imagePath)) {
            $imagePath = $this->getNoImagePath();
            $this->existImage=false;
        }
        //if (!file_exists($imagePath)) {

        //     return $this->getNoImageUrl();
        // }

        /** @var $img \panix\engine\components\ImageHandler */
        $img = Yii::$app->img;
        $img->load($imagePath);
        //echo basename($img->getFileName());
        $fileInfo = explode('.', basename($img->getFileName()));
        $filename = $fileInfo[0];
        if (isset($options['prefix'])) {
            $filename .= '_' . md5(serialize($options));
        }
        $extension = $fileInfo[1];

        if (!file_exists($imageAssetPath . DIRECTORY_SEPARATOR . $filename . '.' . $extension)) {
            $isSaveFile = true;
            FileHelper::createDirectory($imageAssetPath, 0777);
        } else {

            return $assetPath . '/' . $filename . '.' . $extension;
        }


        if ($sizes) {
            $img->resize((!empty($sizes[0])) ? $sizes[0] : 0, (!empty($sizes[1])) ? $sizes[1] : 0);
        }
        if (in_array(mb_strtolower($this->getExtension()), ['png', 'svg']) || !$this->existImage) {
            $options['watermark'] = false;
        }
        if (isset($options['grayscale'])) {
            $img->grayscale();
        }
        if (isset($options['text'])) {
            $img->text($options['text'], Yii::getAlias('@vendor/panix/engine/assets/assets/fonts') . '/Exo2-Light.ttf', $img->getWidth() / 100 * 5, [114, 114, 114], $img::POS_CENTER_BOTTOM, 0, $img->getHeight() / 100 * 5, 0, 0);
        }

        if ($options['watermark'] && $this->existImage) {

            $offsetX = isset($configApp->attachment_wm_offsetx) ? $configApp->attachment_wm_offsetx : 10;
            $offsetY = isset($configApp->attachment_wm_offsety) ? $configApp->attachment_wm_offsety : 10;
            $corner = isset($configApp->attachment_wm_corner) ? $configApp->attachment_wm_corner : 4;
            $path = Yii::getAlias('@uploads') . DIRECTORY_SEPARATOR . $configApp->attachment_wm_path;

            $wm_width = 0;
            $wm_height = 0;
            if (file_exists($path)) {
                if ($imageInfo = @getimagesize($path)) {
                    $wm_width = (float)$imageInfo[0] + $offsetX;
                    $wm_height = (float)$imageInfo[1] + $offsetY;
                }

                $toWidth = min($img->getWidth(), $wm_width);

                if ($wm_width > $img->getWidth() || $wm_height > $img->getHeight()) {
                    $wm_zoom = round($toWidth / $wm_width / 3, 1);
                } else {
                    $wm_zoom = false;
                }

                if (!($img->getWidth() <= $wm_width) || !($img->getHeight() <= $wm_height) || ($corner != 10)) {
                    $img->watermark($path, $offsetX, $offsetY, $corner, $wm_zoom);
                }

            }
        }


        if ($isSaveFile) {
            if (isset($sizes[0]) && isset($sizes[1])) {
                // $img->thumb($sizes[0], $sizes[1]);
            }
            /*$fileInfo = explode('.',basename($img->getFileName()));
            $filename = $fileInfo[0];
            if(isset($options['prefix'])){
                $filename .= '_'.md5(serialize($options));
            }
            $extension = $fileInfo[1];*/

            $img->save($imageAssetPath . DIRECTORY_SEPARATOR . $filename . '.' . $extension);

        }
        return $assetPath . '/' . basename($img->getFileName());
        // return $img;

    }

}
