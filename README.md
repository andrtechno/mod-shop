# Module shop
[Описание на Русском](README_RU.md)

Module for PIXELION CMS

[![Latest Stable Version](https://poser.pugx.org/panix/mod-shop/v/stable)](https://packagist.org/packages/panix/mod-shop)
[![Latest Unstable Version](https://poser.pugx.org/panix/mod-shop/v/unstable)](https://packagist.org/packages/panix/mod-shop)
[![Total Downloads](https://poser.pugx.org/panix/mod-shop/downloads)](https://packagist.org/packages/panix/mod-shop)
[![Monthly Downloads](https://poser.pugx.org/panix/mod-shop/d/monthly)](https://packagist.org/packages/panix/mod-shop)
[![Daily Downloads](https://poser.pugx.org/panix/mod-shop/d/daily)](https://packagist.org/packages/panix/mod-shop)
[![License](https://poser.pugx.org/panix/mod-shop/license)](https://packagist.org/packages/panix/mod-shop)


## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

#### Either run

```
php composer require --prefer-dist panix/mod-shop "*"
```

or add

```
"panix/mod-shop": "*"
```

to the require section of your `composer.json` file.

#### Add to web config.
```
'modules' => [
    'shop' => ['class' => 'panix\mod\shop\Module'],
],
```

#### Migrate
```
php yii migrate --migrationPath=vendor/panix/mod-shop/migrations
```


### Роутинг и параметры
- /catalog
- /catalog/<category_slug>
- /sales `$categories`
- /sales/<category_slug> `$categories`
- /new 

### Author & License
- [Author](https://github.com/andrtechno)
- [License](https://github.com/andrtechno/engine/blob/master/LICENSE.md)

### See also
- [PIXELION CMS](https://pixelion.com.ua)
- [Module discounts Github](https://github.com/andrtechno/mod-discounts)
- [Module compare Github](https://github.com/andrtechno/mod-compare)
- [Module wishlist Github](https://github.com/andrtechno/mod-wishlist)
- [Module cart Github](https://github.com/andrtechno/mod-cart)
- [Module sitemap Github](https://github.com/andrtechno/mod-sitemap)
    * [Module cart Github](https://github.com/andrtechno/mod-cart)
    * [Module sitemap Github](https://github.com/andrtechno/mod-sitemap)
* [Module cart Github](https://github.com/andrtechno/mod-cart)
* [Module sitemap Github](https://github.com/andrtechno/mod-sitemap)


> [![PIXELION CMS!](https://pixelion.com.ua/uploads/logo.svg "PIXELION CMS")](https://pixelion.com.ua)  
<i>Content Management System "PIXELION CMS"</i>  
[www.pixelion.com.ua](https://pixelion.com.ua)

> The module is under development, any moment can change everything.
