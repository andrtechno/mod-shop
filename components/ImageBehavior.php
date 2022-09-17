<?php

namespace panix\mod\shop\components;

use Yii;
use yii\base\Exception;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\BaseFileHelper;
use yii\helpers\FileHelper;
use yii\httpclient\Client;
use panix\engine\CMS;
use panix\engine\components\ImageHandler;
use panix\mod\shop\models\ProductImage;

class ImageBehavior extends \yii\base\Behavior
{

    public $savePath = '@uploads/store/product';
    protected $_file;
    private $imageQuery;

    public function attach($owner)
    {

        parent::attach($owner);


    }

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            //ActiveRecord::EVENT_AFTER_FIND=>'test'
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    public function beforeSave()
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        $owner->file = \yii\web\UploadedFile::getInstances($owner, 'file');
        //if (count($owner->file) > Yii::$app->params['plan'][Yii::$app->params['plan_id']]['product_upload_files']) {
        //    throw new ForbiddenHttpException();
        //}

    }

    public function afterSave()
    {
        if (!Yii::$app instanceof \yii\console\Application) {
            $this->updateMainImage();
            $this->updateImageTitles();
        }
    }

    /**
     * Remove all model images
     */
    public function afterDelete()
    {
        $images = $this->owner->getImages();
        if ($images->count() < 1) {
            return true;
        } else {
            foreach ($images->all() as $image) {
                $this->removeImage($image);
            }

            $path = Yii::getAlias($this->savePath) . DIRECTORY_SEPARATOR . $this->owner->primaryKey;
            BaseFileHelper::removeDirectory($path);
        }
    }

    /**
     * removes concrete model's image
     * @param ProductImage $img
     * @return bool
     * @throws \Exception
     */
    public function removeImage(ProductImage $img)
    {

        $storePath = Yii::getAlias('@uploads/store');

        $fileToRemove = Yii::getAlias($this->savePath);
        if (preg_match('@\.@', $fileToRemove) and is_file($fileToRemove)) {
            unlink($fileToRemove);
        }
        $img->delete();
        return true;
    }

    protected function updateMainImage()
    {
        $post = Yii::$app->request->post('AttachmentsMainId');
        if ($post) {

            //ProductImage::updateAll(['is_main' => 0], 'product_id=:pid', ['pid' => $this->owner->primaryKey]);

            $currentMain = ProductImage::find()->where(['product_id' => $this->owner->primaryKey, 'is_main' => 1])->one();
            $currentMainId = 0;
            if ($currentMain) {
                $currentMainId = $currentMain->id;
                if ($currentMain->id != $post) {
                    $currentMain->is_main = 0;
                    $currentMain->update();
                }
            }


            $customer = ProductImage::findOne($post);
            if ($customer) {
                if ($currentMainId != $post) {
                    $customer->is_main = 1;
                    $customer->update();
                    //$this->owner->main_image = $customer->filename;
                    // $this->owner->save(false);
                }
            }
        }
    }

    protected function updateImageTitles()
    {
        if (sizeof(Yii::$app->request->post('attachment_image_titles', []))) {
            foreach (Yii::$app->request->post('attachment_image_titles', []) as $id => $title) {
                if (!empty($title)) {
                    $customer = ProductImage::findOne($id);
                    if ($customer) {
                        $customer->alt_title = $title;
                        $customer->update();
                    }
                }
            }
        }
    }

    public function downloadFile($url, $saveTo = '@runtime', $newfilename = 'downloadfile')
    {

        $filename = $newfilename . '.' . pathinfo($url, PATHINFO_EXTENSION);
        $savePath = Yii::getAlias($saveTo);
        if (!file_exists($savePath)) {
            FileHelper::createDirectory($savePath, $mode = 0775, $recursive = true);
        }
        $saveTo = $savePath . DIRECTORY_SEPARATOR . $filename;

        //return file of exsts path
        if (file_exists($saveTo)) {
            Yii::info('img exist1 ' . $saveTo, 'forsage');
            Yii::info('img exist2 ' . $url, 'forsage');
            return $saveTo;
        }
        try {

            $fh = fopen($saveTo, 'w');
            $client = new Client([
                'transport' => 'yii\httpclient\CurlTransport'
            ]);
            $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl(str_replace(" ", "%20", $url))
                ->setOptions([
                    'sslVerifyPeer' => false,
                    'timeout' => 8888
                ])
                ->setOutputFile($fh)
                ->send();
            fclose($fh);

            if ($response->isOk) {
                return $saveTo;
            } else {
                Yii::info('img not ok ' . $url, 'forsage');
                Yii::info('img not ok ' . $response->statusCode, 'forsage');
                return false;
            }
        } catch (\Exception $e) {
            Yii::info('img catch ' . $url, 'forsage');
            return false;
        }
    }

    /**
     *
     * Method copies image file to module store and creates db record.
     *
     * @param $file |string UploadedFile Or absolute url
     * @param bool $is_main
     * @param string $alt
     * @return bool|ProductImage
     * @throws \Exception
     */
    public function attachImage($file, $is_main = false, $alt = '')
    {
        $uniqueName = mb_strtolower(\panix\engine\CMS::gen(10));
        $isDownloaded = preg_match('/http(s?)\:\/\//i', $file);
        /*if ($isDownloaded) {
            $download = $this->downloadFile($file);
            //var_dump($download);die;
            if ($download) {
                // $file = $download;
                $newfile = Yii::getAlias('@runtime/') . $uniqueName . '.' . pathinfo($download, PATHINFO_EXTENSION);
                rename($download, $newfile);
                $file = $newfile;
            }else{
                Yii::info( 'img not download '.$file,'forsage');
                return false;
            }
        }*/


        if (!$this->owner->primaryKey) {
            throw new \Exception('Owner must have primaryKey when you attach image!');
        }
        $path = Yii::getAlias($this->savePath) . DIRECTORY_SEPARATOR . $this->owner->primaryKey;

        if ($isDownloaded) {
            $download = $this->downloadFile($file, $path, $uniqueName);
            //echo $download;die;
            if ($download) {
                $file = $download;
                //rename($download, $newfile);
                //$file = $newfile;
            } else {
                Yii::info('img not download ' . $file, 'forsage');
                return false;
            }
        }
        if (!is_object($file)) {
            $pictureFileName = $uniqueName . '.' . pathinfo($file, PATHINFO_EXTENSION);
        } else {
            $pictureFileName = $uniqueName . '.' . $file->extension;
        }

        $newAbsolutePath = $path . DIRECTORY_SEPARATOR . $pictureFileName;

        $createDir = BaseFileHelper::createDirectory($path, 0775, true);


        $image = new ProductImage();
        $image->product_id = $this->owner->primaryKey;
        $image->filename = $pictureFileName;
        $image->alt_title = $alt;
        //$image->urlAlias = $this->getAlias($image);

        if (!$image->save()) {
            Yii::info('img not save ' . $file, 'forsage');
            return false;
        }

        if (count($image->getErrors()) > 0) {

            $ar = array_shift($image->getErrors());
            unlink($newAbsolutePath);
            throw new \Exception(array_shift($ar));
        }

        $img = $this->owner->getImage();

        //If main image not exists
        if ($img == null || $is_main) {
            $this->setMainImage($image);
            $this->owner->image = $image->filename;
        }

        /** @var ImageHandler $img */
        if (is_object($file)) {
            $file->saveAs($newAbsolutePath);
        } else {
            // $copy = copy($file, $newAbsolutePath);
        }

        if (!$isDownloaded) {
            $img = Yii::$app->img->load($newAbsolutePath);
            if ($img->getHeight() > Yii::$app->params['maxUploadImageSize']['height'] || $img->getWidth() > Yii::$app->params['maxUploadImageSize']['width']) {
                $img->resize(Yii::$app->params['maxUploadImageSize']['width'], Yii::$app->params['maxUploadImageSize']['height']);
            }
            if ($img->save($newAbsolutePath)) {
                //   unlink($runtimePath);
            }
        }
        //remove download file
        /*if ($isDownloaded) {
            if (file_exists($file)) {
                unlink($file);
            }
        }*/

        return $image;
    }


    /**
     * returns main model image
     * @param $main
     * @return array|null|ActiveRecord
     */
    public function getImage($main = 1)
    {
        $wheres['product_id'] = $this->owner->primaryKey;

        if ($main)
            $wheres['is_main'] = 1;
        $query = ProductImage::find()->where($wheres);

        //echo $query->createCommand()->rawSql;die;
        $img = $query->one();

        if (!$img) {
            return NULL;
        }


        return $img;
    }

    public function getPathToOrigin($filePath)
    {
        //$base = Yii::$app->getModule('images')->getStorePath();

        if (!file_exists($filePath)) {
            // $this->existImage = false;
            $filePath = Yii::$app->getModule('shop')->getNoImagePath();
        }
        return $filePath;
    }

    public function getExtension($path)
    {
        $ext = pathinfo($this->getPathToOrigin($path), PATHINFO_EXTENSION);
        return $ext;
    }


    public function getMainImageObject($main = 1)
    {
        $wheres['product_id'] = $this->owner->primaryKey;
        $wheres['is_main'] = $main;
        $query = ProductImage::find()->where($wheres);
        //$query->cache(Yii::$app->db->queryCacheDuration, new TagDependency(['tags' => 'product-' . $this->owner->primaryKey]));

        //echo $query->createCommand()->rawSql;die;
        /** @var ProductImage $img */
        $img = $query->one();
        if (!$img) {
            $img = new ProductImage;
            $img->product_id = $this->owner->primaryKey;

        }

        return $img;

    }


    public function getModelSubDir($model)
    {

        $modelName = $this->getShortClass($model);
        $modelDir = \yii\helpers\Inflector::pluralize($modelName) . '/' . $model->id;
        return $modelDir;
    }

    /**
     * Clear all images cache (and resized copies)
     * @return bool
     */
    public function clearImagesCache()
    {

        $subdir = $this->owner->id; //$this->getModelSubDir($this->owner);

        $dirToRemove = Yii::getAlias($this->savePath) . '/' . $subdir;

        if (preg_match('/' . preg_quote(Yii::getAlias($this->savePath), '/') . '/', $dirToRemove)) {
            BaseFileHelper::removeDirectory($dirToRemove);
            //exec('rm -rf ' . $dirToRemove);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Sets main image of model
     * @param $img
     * @throws \Exception
     */
    public function setMainImage($img)
    {

        if ($this->owner->primaryKey != $img->product_id) {
            throw new \Exception('Image must belong to this model');
        }
        $counter = 1;
        /* @var $img ProductImage */
        $img->setMain(true);
        $img->save();


        $images = $this->owner->getImages()->all();
        foreach ($images as $allImg) {

            if ($allImg->id == $img->id) {
                continue;
            } else {
                $counter++;
            }

            $allImg->setMain(false);
            $allImg->save();
        }
        //$this->owner->main_image = $img->filename;
        //$this->owner->save(false);
        // $this->clearImagesCache();
    }

}
