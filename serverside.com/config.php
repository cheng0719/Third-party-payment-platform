<?php
    $host = 'localhost';
    $port = '5432';
    $db = 'test';
    $username = 'postgres';
    $password = 'w2e2i78727';
    $table = 'api_practice';


    function DBConnection(){
        $dsn = 'pgsql:host=localhost;port=5432;dbname=test;';

        return new PDO(
          $dsn,
          'postgres',
          'w2e2i78727',
          [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }