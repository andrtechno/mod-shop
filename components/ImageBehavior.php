<?php

namespace panix\mod\shop\components;


use panix\mod\shop\models\Product;
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
use yii\web\UploadedFile;

class ImageBehavior extends \yii\base\Behavior
{

    public $savePath = '@uploads/store/product';
    protected $_file;
    private $imageQuery;

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
        $url = str_replace(" ", "%20", $url);

        $filename = $newfilename . '.' . pathinfo($url, PATHINFO_EXTENSION);
        $savePath = Yii::getAlias($saveTo);

        $saveTo = $savePath . DIRECTORY_SEPARATOR . $filename;

        //return file of exsts path
        if (file_exists($saveTo)) {
            return $saveTo;
        }

        try {
            $ftp = Yii::$app->getModule('shop')->ftpClient;
            if ($ftp) {
                $ftpPath = Yii::$app->getModule('shop')->ftp['path'] . "/uploads/store/product/{$this->owner->id}";
                if (!$ftp->mkdir($ftpPath)) {
                    echo "Не удалось создать директорию";
                    Yii::info('FTP: Не удалось создать директорию', 'forsage');
                }
                $handle = fopen($url, 'r');
                $upload = $ftp->fput($ftpPath . "/" . $filename, $handle, FTP_IMAGE);
                if (!$upload) {
                    Yii::info('FTP: При загрузке произошла проблема', 'forsage');
                    echo "При загрузке произошла проблема";
                }
                fclose($handle);
                return $saveTo;
            } else {
                if (!file_exists($savePath)) {
                    FileHelper::createDirectory($savePath, $mode = 0775, $recursive = true);
                }
                $handle = fopen($saveTo, 'w');
                $client = new Client([
                    'transport' => 'yii\httpclient\CurlTransport'
                ]);
                $response = $client->createRequest()
                    ->setMethod('GET')
                    ->setUrl($url)
                    ->setOptions([
                        'sslVerifyPeer' => false,
                        'timeout' => 10000
                    ])
                    ->setOutputFile($handle)
                    ->send();
                fclose($handle);
                //var_dump($ftp);die;
                /*if ($ftp) {
                    if ($ftp->alloc(filesize($saveTo), $result)) {
                        echo "Место ".CMS::fileSize(filesize($saveTo))." на сервере успешно зарезервировано.";
                        $upload = $ftp->put("testftp.loc/uploads/store/product/{$this->owner->id}/" . basename($saveTo), $saveTo, FTP_IMAGE);
                        if ($upload) {
                            echo "Файл успешно загружен\n";
                            unlink($saveTo); //remove from site server
                        } else {
                            echo "При загрузке произошла проблема\n";
                        }
                    } else {
                        echo "Не удалось зарезервировать место на сервере. Ответ сервера: $result\n";
                    }
                    return $saveTo;
                }*/

                if ($response->isOk) {
                    return $saveTo;
                }
            }
        } catch (\Exception $e) {
            Yii::info('img catch ' . $url, 'forsage');
            return false;
        }
        return false;
    }

    /**
     *
     * Method copies image file to module store and creates db record.
     *
     * @param string|UploadedFile $file Or absolute url
     * @param bool $is_main
     * @param string $alt
     * @return bool|ProductImage
     * @throws \Exception
     */
    public function attachImage($file, $is_main = false, $alt = '')
    {
        $uniqueName = mb_strtolower(\panix\engine\CMS::gen(10));
        $isDownloaded = preg_match('/http(s?)\:\/\//i', $file);

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
        }


        $ftp = Yii::$app->getModule('shop')->ftpClient;

        if ($ftp) {
            if (is_object($file)) {
                $target = fopen($file->tempName, 'r');
                $upload = $ftp->fput("testftp.loc/uploads/store/product/{$image->product_id}/" . $pictureFileName, $target, FTP_IMAGE);
                @fclose($target);
                if ($upload) {
                    echo "Файл успешно загружен\n";
                } else {
                    echo "При загрузке произошла проблема\n";
                }

                return $image;
            }
        }

        $createDir = BaseFileHelper::createDirectory($path, 0775, true);

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
        //$query->cache(0, new TagDependency(['tags' => 'product-mainImage-' . $this->owner->primaryKey])); //Есть баги

        /** @var ProductImage $img */
        $img = $query->one();
        if (!$img) {
            $img = new ProductImage;
            $img->product_id = $this->owner->primaryKey;

        }

        return $img;

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
        //делаем именно так, потому что срабатывает 2 раза сохранение модели.
        Yii::$app->db->createCommand()->update(Product::tableName(), ['image' => $img->filename], ['id' => $this->owner->id])->execute();
        //$this->owner->image = $img->filename;
        //$this->owner->save(false);
    }

}
