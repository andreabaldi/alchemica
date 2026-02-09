<?php
namespace diggindata\countrylist;

use Yii;
use yii\base\BaseObject;

class CountryList extends BaseObject
{
    /**
     * @var mixed The list of countries.
     */
    private static $_list;

    /**
     * @var string The Git package name of the original list
     */ 
    private static $_packageName = 'umpirsky/country-list';

    public function __construct($config = [])
    {
        // ... initialization before configuration is applied

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();
        if(!is_dir(Yii::getAlias('@vendor/'.self::$_packageName.'/data')))
            throw new \yii\web\HttpException(500, 'Missing package for country-list');
    }   

    /**
     * Returns a list of translated country names, indexed by 2-char country code
     * @return mixed
     */
    public static function getList()
    {
        // Get current app language, transformed for lists i18n subdirs:
        $language = str_replace('-', '_', Yii::$app->language);

        if(is_array(self::$_list))
            return self::$_list;

        $dir = Yii::getAlias('@vendor/'.self::$_packageName.'/data/'.$language);
        if(is_dir($dir)) {
            self::$_list = require($dir.'/country.php');
        } else {
            if(strlen($language)==5) {
                $language = substr($language, 0, 2);
                self::$_list = require(Yii::getAlias('@vendor/'.self::$_packageName.'/data/'.$language.'/country.php'));
            } else {
                self::$_list = require(Yii::getAlias('@vendor/'.self::$_packageName.'/data/en/country.php'));
            }
        }
        return self::$_list;
    } 

}
