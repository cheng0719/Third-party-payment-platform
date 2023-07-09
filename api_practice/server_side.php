<?php
    // //session_start();
    // header("Content-type: application/json; charset=utf-8");

    // //echo json_encode(array('state'=>'success'));
    // //echo file_get_contents('php://input');
    // //print_r(file_get_contents('php://input'));
    // //exit();
    // $input = file_get_contents('php://input');
    // $input_decode = json_decode($input);
    
    // // var_dump($input_decode);
    // // exit();
    // //if($_SERVER['REQUEST_METHOD'] === 'POST')
    // if(!empty($input))
    // //if(isset($json))
    // {
    //     // echo $input;
    //     echo json_encode(array('state'=>$input_decode->account, 'method'=>$_SERVER['REQUEST_METHOD']));
    //     // $acc = $input['account'];
    //     // echo $acc;
        
    // }
    // else
    // {
    //     echo json_encode(array('state'=>'failed'));
    // }
    require_once 'config.php';

    
    
    try {
        $data = array(
            'host' => $host,
            'port' => $port,
            'dbname' => $db,
            'username' => $username,
            'password' => $password
        );

        $Host = $data['host'];
        $Port = $data['port'];
        $Dbname = $data['dbname'];
        $Username = $data['username'];
        $Password = $data['password'];
        $dsn = "pgsql:host=$Host;port=$Port;dbname=$Dbname;";
        // make a database connection
        $pdo = new PDO($dsn, $Username, $Password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
        if ($pdo) {
            echo "Connected to the $db database successfully!";
        }
    } catch (PDOException $e) {
        die($e->getMessage());
    } finally {
        if ($pdo) {
            $pdo = null;
        }
    }
?>