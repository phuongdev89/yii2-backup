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


Migration
-----
```
php yii migrate --migrationPath=@vendor/navatech/yii2-backup/src/migrations
```

Usage
-----

In console configure:
```[php]
'controllerMap'       => [
    'backup' => [
        'class' => 'navatech\backup\commands\BackupController',
    ],
],
'modules'             => [
    'backup' => [
        'class'     => 'navatech\backup\Module',
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
                'class'     => '\navatech\backup\transports\Mail',
            ],
            'ftp'  => [
                'class'  => '\navatech\backup\transports\Ftp',
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
