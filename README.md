Yii2 backup module
==================
Yii2 backup module

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist navatech/yii2-backup "*"
```

or add

```
"navatech/yii2-backup": "*"
```

to the require section of your `composer.json` file.


Usage
-----

In console configure:
```[php]
'components' => [
    'backup' => [
        'class' => 'navatech\backup\Backup',
    ],
]
```
