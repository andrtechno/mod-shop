
# Модуль Интернет-магазина
[Description of English](README.md)

Module for PIXELION CMS

[![Latest Stable Version](https://poser.pugx.org/panix/mod-shop/v/stable)](https://packagist.org/packages/panix/mod-shop)
[![Latest Unstable Version](https://poser.pugx.org/panix/mod-shop/v/unstable)](https://packagist.org/packages/panix/mod-shop)
[![Total Downloads](https://poser.pugx.org/panix/mod-shop/downloads)](https://packagist.org/packages/panix/mod-shop)
[![Monthly Downloads](https://poser.pugx.org/panix/mod-shop/d/monthly)](https://packagist.org/packages/panix/mod-shop)
[![Daily Downloads](https://poser.pugx.org/panix/mod-shop/d/daily)](https://packagist.org/packages/panix/mod-shop)
[![License](https://poser.pugx.org/panix/mod-shop/license)](https://packagist.org/packages/panix/mod-shop)


## Установка

Предпочтительный способ установить это расширение через [composer](http://getcomposer.org/download/).

#### Запустите

```
php composer require --prefer-dist panix/mod-shop "*"
```

или добавте в ваш

```
"panix/mod-shop": "*"
```

в требуемый раздел вашего файла `composer.json`.

#### Добавте в ваш конфиг
```
'modules' => [
    'shop' => ['class' => 'panix\mod\shop\Module'],
],
```
или
```
Установите через интерфейс Админ-панели.
```

#### Миграция
```
php yii migrate --migrationPath=vendor/panix/mod-shop/migrations
```



### Роутинг и параметры
- /catalog
- /catalog/<category_slug>
- /sales `$categories`
- /sales/<category_slug> `$categories`
- /new 

| catalog   |      sales      |  new |
|-----------|:-------------:|------:|
| ---       |`$categories`| --- |
| ---       |    ---       |   --- |


### Автор и лицензия
- [Автор](https://github.com/andrtechno)
- [Лицензия](https://github.com/andrtechno/engine/blob/master/LICENSE.md)

### Смотрите так же
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
<i>Система Управление сайтом "PIXELION CMS"</i>  
[www.pixelion.com.ua](https://pixelion.com.ua)

> Модуль находится на стадии разработке, любой момент может все измениться.
