<?php

namespace panix\mod\shop\commands;

use panix\engine\CMS;
use panix\engine\console\controllers\ConsoleController;
use panix\mod\shop\models\Product;
use Yii;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Generate sitemap for shop
 *
 * @package panix\mod\shop\commands
 */
class SitemapController extends ConsoleController
{

    /**
     * @var string folder for sitemaps files
     */
    public $rootDir = '@uploads';

    /**
     * @var string sitemap main file name
     */
    public $sitemapFile = 'sitemap-shop.xml';
    public $sitemapFileJson = 'sitemap.json';

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['rootDir', 'sitemapFile']);
    }

    /**
     * Generate sitemap.xml file
     *
     * @access public
     * @return integer
     */
    public function actionIndex()
    {

        $file = Yii::getAlias($this->rootDir.'/'.$this->sitemapFile);

        //   $module = $this->module;

        //if (!$sitemapData = Yii::$app->cache->get('sitemap')) {
        //    $sitemapData = Yii::$app->getModule('sitemap')->buildSitemap();
        // }


        // print_r($sitemapData);die;


        $this->stdout("Generate sitemap file.".PHP_EOL, Console::FG_PURPLE);
        $this->stdout("Rendering sitemap...".PHP_EOL, Console::FG_PURPLE);
        $sitemap = Yii::$app->sitemap->render();

        $this->stdout("Writing sitemap to $file".PHP_EOL, Console::FG_PURPLE);
        file_put_contents($file, $sitemap[0]['xml']);
        $sitemap_count = count($sitemap);
        for ($i = 1; $i < $sitemap_count; $i++) {
            $file = Yii::getAlias($this->rootDir.'/'.trim($sitemap[$i]['file'], '/'));
            $this->stdout("Writing sitemap to $file".PHP_EOL, Console::FG_PURPLE);
            file_put_contents($file, $sitemap[$i]['xml']);
        }
        $this->stdout("Done!".PHP_EOL, Console::FG_GREEN);
        return ExitCode::OK;
    }




    public function actionQueue()
    {
        ini_set('memory_limit', '3096M');
        $file = Yii::getAlias($this->rootDir.'/'.$this->sitemapFile);


        $this->stdout("Generate sitemap files.".PHP_EOL, Console::FG_PURPLE);
        $sitemap = Yii::$app->sitemap->queue();

        $this->stdout("Generate sitemap to $file".PHP_EOL, Console::FG_PURPLE);
        file_put_contents($file, $sitemap[0]['xml']);
        $sitemap_count = count($sitemap);
        for ($i = 1; $i < $sitemap_count; $i++) {
            $file = Yii::getAlias($this->rootDir.'/'.trim($sitemap[$i]['file'], '/'));
            $this->stdout("Generate sitemap file $file".PHP_EOL, Console::FG_PURPLE);
            file_put_contents($file, "");
        }
        $this->stdout("Done! Memory_usage: ".memory_get_usage()."".PHP_EOL, Console::FG_GREEN);


        return ExitCode::OK;
    }
}
