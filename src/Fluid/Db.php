<?php

namespace Fluid;

use \PDO;

/**
 * Class Db
 * @package Fluid
 */
class Db
{
    protected $config;

    protected $logger;

    /**
     * Db constructor.
     * @param $config array
     * @param $logger Monolog/Logger
     */
    public function __construct($config, $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    function getConnection()
    {
        $dbhost = $this->config['host'];
        $dbuser = $this->config['username'];
        $dbpass = $this->config['password'];
        $dbname = $this->config['database'];
        $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbh;
    }
}
