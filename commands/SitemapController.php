<?php

namespace panix\mod\shop\commands;

use panix\engine\console\controllers\ConsoleController;
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
    public $rootDir = '@runtime';

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




    public function actionTest()
    {

        $file = Yii::getAlias($this->rootDir.'/'.$this->sitemapFileJson);


        $this->stdout("Generate sitemap file.".PHP_EOL, Console::FG_PURPLE);
        $this->stdout("Rendering sitemap...".PHP_EOL, Console::FG_PURPLE);
        $sitemap = Yii::$app->sitemap->render();

//print_r($sitemap);die;
        $xml = new \SimpleXMLElement($sitemap[0]['xml']);
//print_r($xml);die;
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
}
