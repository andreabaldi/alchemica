<?php

/**
 * Local configuration for Alchemica Lab.
 * Credenziali SMTP rimosse per sicurezza e invio reale disabilitato.
 */

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
            /**
             * MODALITÀ BETA: useFileTransport è true.
             * Le email non vengono spedite ma salvate in: frontend/runtime/mail/
             */
            'useFileTransport' => true,

            'transport' => [
                'scheme' => 'smtps',
                'host' => 'smtp.gmail.com',
                'username' => '', // Svuotato per sicurezza
                'password' => '', // Svuotato per sicurezza
                'port' => 465,
                // DSN rimosso per evitare esposizione credenziali
            ],
        ],
    ],
];