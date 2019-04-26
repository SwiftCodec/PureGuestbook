<?php

class PDOconn extends PDO
{
    const PARAM_host='194.87.102.173';
    const PARAM_port='3306';
    const PARAM_db_name='alex_superdb';
    const PARAM_user='alex_superuser';
    const PARAM_db_pass='9IsBhLKT2D';
    const PARAM_charset='utf8';

    public function __construct($options=null){
        parent::__construct('mysql:host='.PDOconn::PARAM_host.';port='.PDOconn::PARAM_port.';dbname='.PDOconn::PARAM_db_name.';charset='.PDOconn::PARAM_charset,
            PDOconn::PARAM_user,
            PDOconn::PARAM_db_pass,$options);
    }
}