<?php
    session_start();
    // $conn = pg_connect("host=localhost port=5432 dbname=test user=postgres password=w2e2i78727") or die ('fail to connect');
    // if($conn)
    // {
    //     echo "success";
    // }
    // && !empty($_POST['Login'])
    if(isset($_POST['Login']))
    {
        $_SESSION['Username'] = $_POST['Username'];
        $_SESSION['Account'] = $_POST['Account'];
        $_SESSION['Gender'] = $_POST['Gender'];
        $_SESSION['PassWord'] = $_POST['Password'];
        echo "<script>location.href='/login/login.php'</script>";
    }
    // else
    // {
    //     echo "<script>location.href='/login/index.php'</script>";
    // }
    
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body{
                text-align: center;
            }
            .field{
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <h2>Please Login</h2>
        <div>
            <form action = "/login/index.php" method = "post">
                <input type = "text" class = "field" name = "Username" placeholder = "Username" required><br/>
                <input type = "text" class = "field" name = "Account" placeholder = "Account" required><br/>
                <input type = "text" class = "field" name = "Gender" placeholder = "Gender" required><br/>
                <input type = "password" class = "field" name = "Password" placeholder = "Password" required><br/>
                <input type = "submit" class = "field" name = "Login" value = "Login">
            </form>
        </div>
    </body>
</html>


<!-- // $result = pg_query($conn, "SELECT * FROM login_test");
    // if (!$result) {
    //     echo "An error occured.\n";
    //     exit;
    // }

    // $arr = pg_fetch_array($result);
    // echo $arr['account'] . " <- array\n";
    // echo $arr['password']."\n";

    // $arr = pg_fetch_array($result, 0, PGSQL_NUM);
    // echo $arr[0] . " <- array\n";

    // $arr = pg_fetch_array($result, 1, PGSQL_ASSOC);
    // echo $arr["account"] . " <- array\n"; -->


    