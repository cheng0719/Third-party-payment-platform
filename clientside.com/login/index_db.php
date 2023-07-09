<?php
    session_start();
    $conn = pg_connect("host=localhost port=5432 dbname=test user=postgres password=w2e2i78727") or die ('fail to connect');
    if($conn)
    {
        echo "success\n";
    }
    
    
    
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
            <form action = "/login/index_db.php" method = "post">
                <input type = "text" class = "field" name = "Account" placeholder = "Account" required><br/>
                <input type = "password" class = "field" name = "Password" placeholder = "Password" required><br/>
                <input type = "submit" class = "field" name = "Login" value = "Login">
            </form>
        </div>
        <?php
            if(isset($_POST['Login']) && !empty($_POST['Login']))
            {
                $query = "select * from login_test where account = '{$_POST['Account']}' and password = '{$_POST['Password']}'";
                $result = pg_query($conn, $query);
                $login_check = pg_num_rows($result);

                // $row = pg_fetch_array($result2);
                // $acc = $row['account'];
                // $pass = $row['password'];
                // echo "<script>alert('$acc')</script>";
                // echo "<script>alert('$pass')</script>";

                //echo $row['password'];
        
                if($login_check > 0)
                {
                    echo "<script>alert('Correct!')</script>";
                }
                else
                {
                    echo "<script>alert('Wrong!')</script>";
                }
                
            }
        ?>
    </body>
</html>

