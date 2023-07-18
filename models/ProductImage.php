<?php

namespace panix\mod\shop\models;

use panix\engine\CMS;
use panix\mod\shop\components\ExternalFinder;
use Yii;
use panix\engine\db\ActiveRecord;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;
use yii\db\Expression;
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

    public function afterSave2($insert, $changedAttributes)
    {

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
        $module = Yii::$app->getModule('shop');


        $fileToRemove = FileHelper::normalizePath($this->getPathToOrigin());

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


        if ($module->ftp) {
            $ftpClient = ftp_connect($module->ftp['server']);
            ftp_login($ftpClient, $module->ftp['login'], $module->ftp['password']);
            @ftp_pasv($ftpClient, true);

            $assetPather = "/assets/product/{$this->product_id}";
            $deleted = @ftp_delete($ftpClient, "/uploads/product/{$this->product_id}_{$this->filename}");
            $deleted = @ftp_delete($ftpClient, $assetPather . "/medium_{$this->filename}");
            $deleted = @ftp_delete($ftpClient, $assetPather . "/small_{$this->filename}");


            $assetsList = @ftp_nlist($ftpClient, $assetPather);
            if ($assetsList) {
                sort($assetsList);
                unset($assetsList[0], $assetsList[1]); //remove list ".."
                if (!$assetsList) {
                    @ftp_rmdir($ftpClient, $assetPather);
                }
            }
            ftp_close($ftpClient);
        }

    }


    public function get($size = false, array $options = [])
    {
        if (!$this->filename) {
            return $this->getNoImageUrl();
        }
        $module = Yii::$app->getModule('shop');
        //$ftp = $module->ftpClient;
        if ($size == 'preview') {
            $prefix = 'preview_';
            //$size = $module->imgSizePreview;
        } elseif ($size == 'medium') {
            $prefix = 'medium_';
            //$size = $module->imgSizeMedium;
        } elseif ($size == 'small') {
            $prefix = 'small_';
            //$size = $module->imgSizeSmall;
        } else {
            $prefix = $size . '_';
        }

        if (!$size) {
            if ($module->ftp && in_array($_SERVER['REMOTE_ADDR'], ['178.212.194.135'])) {
                return $module->ftp['host'] . "/uploads/product/{$this->product_id}_{$this->filename}";
            }

            $path = Yii::getAlias("@uploads/store/product/{$this->product_id}/{$this->filename}");
            if (!file_exists($path) || !is_file($path)) {
                return $this->getNoImageUrl();
            }
            return "/uploads/store/product/{$this->product_id}/{$this->filename}";
        } else {
            if ($module->ftp && in_array($_SERVER['REMOTE_ADDR'], ['178.212.194.135'])) {
                return $module->ftp['host'] . "/assets/product/{$this->product_id}/{$prefix}{$this->filename}";
            }
            $path = Yii::getAlias("@uploads/store/product/{$this->product_id}/{$this->filename}");
            if (!file_exists($path) || !is_file($path)) {
                return $this->getNoImageUrl();
            }

        }

        return $this->createVersion($size, $options);

    }


    public function createVersion($size = false, $options = [])
    {
        $module = Yii::$app->getModule('shop');
        //$ftp = $module->ftpClient;

        /*$url = '';
        if(in_array($size,['small','medium','preview'])){
            $url = Yii::$app->getModule('shop')->ftp['host'] . "/" . Yii::$app->getModule('shop')->ftp['path'] . "/uploads/product/{$this->product_id}/{$this->filename}";
            if ($ftp) {
                return $url;
            }
        }*/

        //$prefix = '';
        if ($size == 'preview') {
            $prefix = 'preview_';
            //$size = $module->imgSizePreview;
        } elseif ($size == 'medium') {
            $prefix = 'medium_';
            //$size = $module->imgSizeMedium;
        } elseif ($size == 'small') {
            $prefix = 'small_';
            //$size = $module->imgSizeSmall;
        } else {
            $prefix = $size . '_';
        }


        $configApp = Yii::$app->settings->get('shop');
        if (!isset($options['watermark'])) {
            $options['watermark'] = $configApp->watermark_enable;
        }

        $sizes = explode('x', $size);

        $isSaveFile = false;
        $imageAssetPath = Yii::getAlias("@app/web/assets/product/{$this->product_id}");
        $assetPath = "/assets/product/{$this->product_id}";

        $imagePath = Yii::getAlias("@uploads/store/product/{$this->product_id}/{$this->filename}");
        if (!file_exists($imagePath) || !is_file($imagePath)) {
            $imagePath = $this->getNoImagePath();
            $this->existImage = false;
        }

        /** @var $img \panix\engine\components\ImageHandler */
        try {
            $img = Yii::$app->img;
            // echo $imagePath;die;
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

        if (!file_exists($imageAssetPath . DIRECTORY_SEPARATOR . $prefix . $filename . '.' . $extension)) {
            $isSaveFile = true;
            FileHelper::createDirectory($imageAssetPath, 0777);
        } else {
            return $assetPath . '/' . $prefix . $filename . '.' . $extension;
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
            $versionPath = $imageAssetPath . DIRECTORY_SEPARATOR . $prefix . $this->filename;
            $img->save($versionPath);
            /*if ($ftp) {
                $ftpPath = "/assets/product/{$this->product_id}";
                if (!$ftp->mkdir($ftpPath)) {
                    echo "Не удалось создать директорию";
                }
                $ftpPath = "/assets/product/{$this->product_id}/{$size}";
                if (!$ftp->mkdir($ftpPath)) {
                    echo "Не удалось создать директорию";
                }
                $upload = $ftp->put("$ftpPath/{$this->filename}", $versionPath, FTP_IMAGE);
            }*/
        }
        return $assetPath . '/' . $prefix . $this->filename;
        // return $img;

    }

    public $ftp;

    public function createVersionFtp($size = false, array $options = [])
    {
        $module = Yii::$app->getModule('shop');
        //$ftp = $module->ftpClient;

        //$prefix = '';
        if ($size == 'preview') {
            $prefix = 'preview_';
            $size = $module->imgSizePreview;
        } elseif ($size == 'medium') {
            $prefix = 'medium_';
            $size = $module->imgSizeMedium;
        } elseif ($size == 'small') {
            $prefix = 'small_';
            $size = $module->imgSizeSmall;
        } else {
            $prefix = $size . '_';
        }


        $configApp = Yii::$app->settings->get('shop');
        if (!isset($options['watermark'])) {
            $options['watermark'] = $configApp->watermark_enable;
        }

        $sizes = explode('x', $size);

        $isSaveFile = false;
        $imageAssetPath = Yii::getAlias("@app/web/assets/product");
        $assetPath = '/assets/product';


        //$imagePath = Yii::getAlias("@uploads/store/product/{$this->product_id}/{$this->filename}");
        $imagePath = Yii::getAlias("@runtime/{$this->filename}");
        if (!file_exists($imagePath) || !is_file($imagePath)) {
            $imagePath = $this->getNoImagePath();
            $this->existImage = false;
        }

        /** @var $img \panix\engine\components\ImageHandler */
        try {
            $img = Yii::$app->img;
            // echo $imagePath;die;
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


        $isSaveFile = true; //NEW

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
            //FileHelper::createDirectory($imageAssetPath);
            //$versionPath = FileHelper::normalizePath($imageAssetPath . DIRECTORY_SEPARATOR . $this->filename);

            $versionPath = FileHelper::normalizePath(Yii::getAlias("@runtime/{$prefix}{$this->filename}"));
            $img->save($versionPath);
            if ($this->ftp) {
                $ftpPath = "/assets/product/{$this->product_id}";
                if (!@ftp_mkdir($this->ftp, $ftpPath)) {
                    //echo "Не удалось создать директорию";
                }

                $upload = @ftp_put($this->ftp, "$ftpPath/{$prefix}{$this->filename}", $versionPath, FTP_IMAGE);
                FileHelper::unlink($versionPath);
            }
        }
        return $assetPath . '/' . basename($img->getFileName());

    }

}
