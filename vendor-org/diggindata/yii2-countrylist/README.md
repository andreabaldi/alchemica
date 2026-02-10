<p align="center">
    <a href="https://bitbucket.org/jwerner/" target="_blank">
        <img src="https://www.diggin-data.de/dd-cms/files/Logo_Diggin_Data.jpg" height="100px">
    </a>
    <h1 align="center">Country List Extension for Yii 2</h1>
    <br>
</p>

This package is based on [umpirsky/country-list](https://github.com/umpirsky/country-list) 

It contains an extension for the Yii framework to get a translated list of country names.

[![Latest Stable Version](https://poser.pugx.org/diggindata/yii2-countrylist/v/stable)](https://packagist.org/packages/diggindata/yii2-countrylist)
[![Total Downloads](https://poser.pugx.org/diggindata/yii2-countrylist/downloads)](https://packagist.org/packages/diggindata/yii2-countrylist)

Installation 
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist diggindata/yii2-countrylist "*"
```

or add

```
"diggindata/yii2-countrylist": "*"
```

to the require section of your composer.json.

Then run `composer install` or `composer update`.

Usage
-----

The extension looks at the configured Yii application language `Yii::$app->language`. So if the language is configured to e.g. `de`, it takes the corresponding translation list from the umpirsky package.

Once the extension is installed, simply use the list in your forms as follows:

    use diggindata\countrylist\CountryList;    
    <?= $form->field($model, 'countryCode')->dropDownList(CountryList::getList()) ?>

Alternatively, you may add the extension as an application component.

Add the following to your `config/web.php` file:

    ...
    'components' => [
        'countryList' => [
            'class' => 'diggindata\countrylist\CountryList',
        ],

You can the use it like this:

    <?= $form->field($model, 'countryCode')->dropDownList(Yii::$app->countryList->getList()) ?>

