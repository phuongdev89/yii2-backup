Yii2 backup module
==================
Yii2 backup module

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist phuongdev89/yii2-backup "*"
```

or add

```
"phuongdev89/yii2-backup": "*"
```

to the require section of your `composer.json` file.


Migration
-----
```
php yii migrate --migrationPath=@vendor/phuongdev89/yii2-backup/src/migrations
```

Usage
-----

In console configure:
```[php]
'controllerMap'       => [
    'backup' => [
        'class' => 'phuongdev89\backup\commands\BackupController',
    ],
],
'modules'             => [
    'backup' => [
        'class'     => 'phuongdev89\backup\Module',
        'databases'     => [
            'db',
            'db1',
        ],
        'directories' => [
            '@app/web/uploads',
            '@backend/web/uploads',
        ],
        'transport' => [
            'mail' => [
                'class'     => '\phuongdev89\backup\transports\Mail',
            ],
            'ftp'  => [
                'class'  => '\phuongdev89\backup\transports\Ftp',
            ],
        ],
    ],
]
```
How to use in command line:
```
php yii help backup
```

####Notice:
* Make sure [yii\swiftmailer\Mailer](http://www.yiiframework.com/doc-2.0/yii-swiftmailer-mailer.html) has been configured or backed up files can't be send through mail.
