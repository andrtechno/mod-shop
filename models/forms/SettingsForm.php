<?php

namespace panix\mod\shop\models\forms;

use Yii;
use panix\engine\SettingsModel;
use yii\helpers\Html;
use yii\web\UploadedFile;

class SettingsForm extends SettingsModel
{

    public static $category = 'shop';
    protected $module = 'shop';

    public $per_page;
    public $product_related_bilateral;
    public $group_attribute;
    public $label_expire_new;
    public $smart_bc;
    public $smart_title;
    public $email_notify_reviews;
    public $added_to_cart_count;
    public $added_to_cart_period;

    public $watermark_enable;
    public $attachment_wm_path;
    public $attachment_wm_corner;
    public $attachment_wm_offsetx;
    public $attachment_wm_offsety;
    public $enable_reviews;

    public $search_availability;
    public $search_limit;

    public $seo_brand_title_uk;
    public $seo_brand_title_ru;
    public $seo_brand_description_uk;
    public $seo_brand_description_ru;
    public $seo_brand_h1_uk;
    public $seo_brand_h1_ru;


    public $seo_catalog_brand_title_uk;
    public $seo_catalog_brand_title_ru;
    public $seo_catalog_brand_description_uk;
    public $seo_catalog_brand_description_ru;
    public $seo_catalog_brand_h1_uk;
    public $seo_catalog_brand_h1_ru;

    public static $extensionWatermark = ['png'];

    public function rules()
    {
        return [
            [['per_page'], "required"],
            [['product_related_bilateral', 'group_attribute', 'smart_bc', 'smart_title', 'enable_reviews'], 'boolean'],
            [['label_expire_new', 'added_to_cart_count', 'search_limit'], 'integer'],
            [['email_notify_reviews'], '\panix\engine\validators\EmailListValidator'],
            [['added_to_cart_period','seo_brand_h1_uk','seo_brand_h1_ru','seo_brand_title_uk','seo_brand_description_uk','seo_brand_title_ru','seo_brand_description_ru'], 'string'],
            [['seo_catalog_brand_h1_uk','seo_catalog_brand_h1_ru','seo_catalog_brand_title_uk','seo_catalog_brand_description_uk','seo_catalog_brand_title_ru','seo_catalog_brand_description_ru'], 'string'],
            ['search_availability', 'each', 'rule' => ['integer']],
            [['watermark_enable'], 'boolean'],
            [['attachment_wm_corner', 'attachment_wm_offsety', 'attachment_wm_offsetx'], 'integer'],
            ['attachment_wm_path', 'validateWatermarkFile'],
            [['attachment_wm_offsetx', 'attachment_wm_offsety', 'attachment_wm_corner'], "required"],
            [['attachment_wm_path'], 'file', 'skipOnEmpty' => true, 'extensions' => self::$extensionWatermark],
            //[['added_to_cart_period','seo_brand_h1_uk','seo_brand_h1_ru','seo_brand_title_uk','seo_brand_description_uk','seo_brand_title_ru','seo_brand_description_ru'], 'default'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function defaultSettings()
    {
        return [
            'per_page' => '10,20,30',
            'seo_categories' => false,
            'product_related_bilateral' => false,
            'group_attribute' => false,
            'label_expire_new' => 7,
            'smart_bc' => true,
            'smart_title' => true,
            'email_notify_reviews' => NULL,
            'watermark_enable' => false,
            'attachment_wm_path' => 'watermark.png',
            'attachment_wm_offsety' => 10,
            'attachment_wm_offsetx' => 10,
            'attachment_wm_corner' => 5,
            'enable_reviews' => false,
            'search_availability' => '["1","2"]',
            'search_limit' => 20,
            'top_sales_expire' => 30
        ];
    }

    public function getWatermarkCorner()
    {
        return [
            1 => self::t('WM_POS_LEFT_TOP'),
            2 => self::t('WM_POS_RIGHT_TOP'),
            3 => self::t('WM_POS_LEFT_BOTTOM'),
            4 => self::t('WM_POS_RIGHT_BOTTOM'),
            5 => self::t('WM_POS_CENTER'),
            6 => self::t('WM_POS_CENTER_TOP'),
            7 => self::t('WM_POS_CENTER_BOTTOM'),
            8 => self::t('WM_POS_LEFT_CENTER'),
            9 => self::t('WM_POS_RIGHT_CENTER'),
            10 => self::t('WM_POS_REPEAT'),
        ];
    }

    public function renderWatermarkImage()
    {
        $config = Yii::$app->settings->get('shop');
        if (isset($config->attachment_wm_path) && file_exists(Yii::getAlias('@uploads') . DIRECTORY_SEPARATOR . $config->attachment_wm_path))
            return Html::img("/uploads/{$config->attachment_wm_path}?" . time(), ['class' => 'img-fluid img-thumbnail mt-3']);
    }

    public function validateWatermarkFile($attribute)
    {
        $file = UploadedFile::getInstance($this, 'attachment_wm_path');
        if ($file && !in_array($file->extension, self::$extensionWatermark))
            $this->addError($attribute, self::t('ERROR_WM_NO_IMAGE'));

    }

    public static function labelExpireNew()
    {
        return [
            1 => self::t('LABEL_NEW_DAYS', ['n' => 1]),
            2 => self::t('LABEL_NEW_DAYS', ['n' => 2]),
            3 => self::t('LABEL_NEW_DAYS', ['n' => 3]),
            4 => self::t('LABEL_NEW_DAYS', ['n' => 4]),
            5 => self::t('LABEL_NEW_DAYS', ['n' => 5]),
            6 => self::t('LABEL_NEW_DAYS', ['n' => 6]),
            7 => self::t('LABEL_NEW_DAYS', ['n' => 7]),
            8 => self::t('LABEL_NEW_DAYS', ['n' => 8]),
            9 => self::t('LABEL_NEW_DAYS', ['n' => 9]),
            10 => self::t('LABEL_NEW_DAYS', ['n' => 10]),
            11 => self::t('LABEL_NEW_DAYS', ['n' => 11]),
            12 => self::t('LABEL_NEW_DAYS', ['n' => 12]),
            13 => self::t('LABEL_NEW_DAYS', ['n' => 13]),
            14 => self::t('LABEL_NEW_DAYS', ['n' => 14]),
        ];
    }
}
