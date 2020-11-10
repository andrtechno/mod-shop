<?php

namespace panix\mod\shop\bundles;

use panix\engine\web\AssetBundle;

/**
 * Class TypeaheadAsset
 * @package panix\mod\shop\assets
 */
class TypeaheadAsset extends AssetBundle
{

    public $sourcePath = '@bower/jquery-typeahead/dist';

    public $js = [
        'jquery.typeahead.min.js',
    ];
    public $css = [
        'jquery.typeahead.min.css',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
