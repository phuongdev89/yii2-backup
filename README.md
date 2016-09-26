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
'controllerMap'       => [
    'backup' => [
        'class' => 'navatech\backup\commands\BackupController',
    ],
],
'modules'             => [
    'backup'    => [
        'db'     => [
            'enable' => true,
            'data'   => [ //TODO List of database which need to be backed up
                'db',
                'db1',
            ],
        ],
        'folder' => [
            'enable' => false,
            'data'   => [ //TODO List of directories which need to be backed up
                '@app/web/uploads',
                '@backend/web/uploads',
            ],
        ],
    ],
    'transport' => [
        'mail' => [
            'class'     => '\navatech\backup\transports\Mail',
            'enable'    => true, //TODO default true
            'fromEmail' => 'support@gmail.com',
            'toEmail'   => 'backup@gmail.com',
        ],
        'ftp'  => [
            'class'  => '\navatech\backup\transports\Ftp',
            'enable' => false, //TODO default false
            'host'   => 'ftp.example.com',
            'user'   => 'login',
            'pass'   => 'password',
            'dir'    => '/home/example/public_html/backup',
        ],
    ],
],
```
How to use in command line:
```
php yii help backup
```

####Notice:
* Make sure [yii\swiftmailer\Mailer](http://www.yiiframework.com/doc-2.0/yii-swiftmailer-mailer.html) has been configured or backed up files can't be send through mail.
