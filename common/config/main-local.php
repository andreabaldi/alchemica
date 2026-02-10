<?php

return [
    'components' => [
        'db' => [
            'class' => \yii\db\Connection::class,
            'dsn' => 'mysql:host=mysql;dbname=Alcheneg',
            'username' => 'alcheneg_user',
            'password' => 'alcheneg_pass',
            'charset' => 'utf8',
        ],
        

        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@common/mail',
            // Impostato su false per inviare email reali
            'useFileTransport' => false,
            
            'transport' => [
                'scheme' => 'smtps', // SSL Implicito per porta 465
                'host' => 'smtp.gmail.com',
                'username' => 'baldi.andrea@gmail.com',
                'password' => 'nxqbucpedwqlofae',
                'port' => 465,
                // DSN ricostruito correttamente per SymfonyMailer OK
                'dsn' => 'smtps://baldi.andrea@gmail.com:nxqbucpedwqlofae@smtp.gmail.com:465',
            ],
        ],
    ],
];
