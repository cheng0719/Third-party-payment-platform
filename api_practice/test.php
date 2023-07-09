<?php
    // $a = random_bytes(20);
    // echo bin2hex($a);
    $Strings = '0123456789abcdefghijklmnopqrstuvwxyz';
    echo "Gen_rand_str: ",substr(str_shuffle($Strings), 0, 20), "\n";
    // $salt = '$5$dkjcjdkbcjvfvfhv$mW7UzqyWGt/tF51e/CvONc.ctTJ.C7B5ssjWdJi//B2';
    // $a = crypt('password', $salt);
    // echo $a . PHP_EOL;

    // $b = crypt('passwod', $salt);
    // echo $b . PHP_EOL;
    
    