<?php

namespace panix\mod\shop\bundles;

use panix\engine\web\AssetBundle;

/**
 * Class TypeaheadAsset
 * @package panix\mod\shop\assets
 */
class TypeaheadAsset1 extends AssetBundle
{

    public $sourcePath = '@bower/typeahead.js/dist';

    public $js = [

        'typeahead.jquery.min.js',
        //'typeahead.bundle.min.js',
        //'bloodhound.min.js',
    ];
    public $depends = [
        //'yii\web\JqueryAsset',
    ];
}
