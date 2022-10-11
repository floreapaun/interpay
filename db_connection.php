<?php

    $dsn = "pgsql:dbname=interpay host=localhost";
    $options = [
      PDO::ATTR_EMULATE_PREPARES   => false, 
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
    ];
    try {
      $pdo = new PDO($dsn, "postgres", "4137", $options);
    } catch (Exception $e) {
      error_log($e->getMessage());
      exit('Fatal error!');
    }
