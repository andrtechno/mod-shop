mod-shop
===========
Module for CORNER CMS

[![Latest Stable Version](https://poser.pugx.org/panix/mod-shop/v/stable)](https://packagist.org/packages/panix/mod-shop) [![Total Downloads](https://poser.pugx.org/panix/mod-shop/downloads)](https://packagist.org/packages/panix/mod-shop) [![Monthly Downloads](https://poser.pugx.org/panix/mod-shop/d/monthly)](https://packagist.org/packages/panix/mod-shop) [![Daily Downloads](https://poser.pugx.org/panix/mod-shop/d/daily)](https://packagist.org/packages/panix/mod-shop) [![Latest Unstable Version](https://poser.pugx.org/panix/mod-shop/v/unstable)](https://packagist.org/packages/panix/mod-shop) [![License](https://poser.pugx.org/panix/mod-shop/license)](https://packagist.org/packages/panix/mod-shop)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist panix/mod-shop "*"
```

or add

```
"panix/mod-shop": "*"
```

to the require section of your `composer.json` file.

Add to web config.
```
'modules' => [
    'shop' => ['class' => 'panix\mod\shop\Module'],
],
```