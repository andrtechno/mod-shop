<?php

namespace panix\mod\shop\models;

use panix\engine\CMS;
use panix\mod\shop\components\ExternalFinder;
use Yii;
use panix\engine\db\ActiveRecord;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\HttpException;


/**
 * Class ProductImage
 * @property integer $id
 * @property integer $owner_id
 * @property integer $product_id
 * @property string $alt_title
 * @property boolean $is_main
 * @property string $filename
 */
class ProductImage extends ActiveRecord
{

    const MODULE_ID = 'shop';
    private $existImage = true;

    public function afterSave($insert, $changedAttributes)
    {
        //print_r($changedAttributes);
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop__product_image}}';
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
            [['alt_title'], 'string'],
            [['is_main'], 'boolean'],
        ];
    }

    public function setMain($is_main = true)
    {
        if ($is_main) {
            $this->is_main = true;
            TagDependency::invalidate(Yii::$app->cache, ['product-mainImage-' . $this->product_id]);
        } else {
            $this->is_main = false;
        }
    }

    public function afterDelete()
    {

        $fileToRemove = FileHelper::normalizePath($this->getPathToOrigin());

        $ftp = Yii::$app->getModule('shop')->ftpClient;
        if ($ftp) {
            $deleted = $ftp->delete(Yii::$app->getModule('shop')->ftp['path'] . "/uploads/store/product/{$this->product_id}/{$this->filename}");
        }

        if (preg_match('@\.@', $fileToRemove) and is_file($fileToRemove)) {
            FileHelper::unlink($fileToRemove);
        }
        $assetPath = FileHelper::normalizePath(Yii::getAlias("@app/web/assets/product/{$this->product_id}"));
        if (file_exists($assetPath)) {
            FileHelper::removeDirectory($assetPath, ['traverseSymlinks' => true]);
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

        /*if ($size = 'preview') {
            $size = Yii::$app->getModule('shop')->imgSizePreview;
        } elseif ($size = 'medium') {
            $size = Yii::$app->getModule('shop')->imgSizeMedium;
        } elseif ($size = 'small') {
            $size = Yii::$app->getModule('shop')->imgSizeSmall;
        }*/

        $ftp = Yii::$app->getModule('shop')->ftpClient;

        if (!$size) {
            if ($ftp) {
                return "http://testftp.loc/kvobuv/uploads/product/{$this->product_id}/{$this->filename}";
            }
            $path = Yii::getAlias("@uploads/store/product/{$this->product_id}/{$this->filename}");
            if (!file_exists($path) || !is_file($path)) {
                return $this->getNoImageUrl();
            }
            return "/uploads/store/product/{$this->product_id}/{$this->filename}";
        }
        $configApp = Yii::$app->settings->get('shop');
        if (!isset($options['watermark'])) {
            $options['watermark'] = $configApp->watermark_enable;
        }
        $sizes = explode('x', $size);

        $isSaveFile = false;
        if (isset($sizes[0]) && isset($sizes[1])) {
            $imageAssetPath = Yii::getAlias("@app/web/assets/product/{$this->product_id}/{$size}");
            $assetPath = "/assets/product/{$this->product_id}/{$size}";

        } else {
            $imageAssetPath = Yii::getAlias("@app/web/assets/product/{$this->product_id}");
            $assetPath = '/assets/product/' . $this->product_id;
        }
        $imagePath = Yii::getAlias("@uploads/store/product/{$this->product_id}/{$this->filename}");
        if (!file_exists($imagePath) || !is_file($imagePath)) {
            $imagePath = $this->getNoImagePath();
            $this->existImage = false;
        }
        //if (!file_exists($imagePath)) {

        //     return $this->getNoImageUrl();
        // }

        /** @var $img \panix\engine\components\ImageHandler */
        try {
            $img = Yii::$app->img;

            if ($ftp) {
                //upload version to ftp server
                ////$ftp->connect(Yii::$app->getModule('shop')->ftp['server']);
                //$ftp->login(Yii::$app->getModule('shop')->ftp['login'], Yii::$app->getModule('shop')->ftp['password']);
                //$ftp->pasv(true);
                $imagePath = "http://testftp.loc/uploads/store/product/{$this->product_id}/{$this->filename}";


            }
            $img->load($imagePath);
        } catch (\Exception $e) {
            return false;
        }

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
            if ($ftp) {
                //upload version to ftp server
                $ftp->connect(Yii::$app->getModule('shop')->ftp['server']);
                $ftp->login(Yii::$app->getModule('shop')->ftp['login'], Yii::$app->getModule('shop')->ftp['password']);
                $ftp->pasv(true);

                //$handle = fopen($imagePath, 'r');
                $ftpPath = Yii::$app->getModule('shop')->ftp['path'] . "/assets/product/{$this->product_id}/{$size}";

                if (!$ftp->mkdir($ftpPath)) {
                    // echo "Не удалось создать директорию";
                }


                echo $ftpPath . "/" . $filename . '.' . $extension;
//var_dump($img->getImage());die;
                $upload = $ftp->fput($ftpPath . "/" . $filename . '.' . $extension, $img->getImage(), FTP_IMAGE);
                if (!$upload) {
                    echo "При загрузке произошла проблема";
                }
                die;
                return "http://testftp.loc/uploads/store/product/{$this->product_id}/{$this->filename}";
            } else {
                $img->save($imageAssetPath . DIRECTORY_SEPARATOR . $filename . '.' . $extension);
            }


        }
        return $assetPath . '/' . basename($img->getFileName());
        // return $img;

    }

}
