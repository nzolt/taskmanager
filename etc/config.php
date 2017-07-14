<?php
namespace Config;

use Monolog;

class Config
{
    public function getConfig()
    {
        $config = [
            'settings' => [
                'displayErrorDetails' => true,

                'logger' => [
                    'name' => 'fluid-app',
                    'path' => 'log/app.log',
                ],

                'db' => [
                    'host' => 'localhost',
                    'database' => 'fluid',
                    'username' => 'fluid',
                    'password' => 'fluidPa55',
                    'charset' => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix' => '',
                    'limit' => 30,
                ]
            ],
        ];
        return $config;
    }
}
