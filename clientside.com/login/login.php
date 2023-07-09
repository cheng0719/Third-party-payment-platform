<?php
    session_set_cookie_params(0);
    session_start();
    echo "<h1>welcome " . $_SESSION['Username'] . " !!!</h1>";
    echo "<h2>your account: " . $_SESSION['Account'] . ".</h2>";
    echo "<h2>your gender:  " . $_SESSION['Gender'] . ".</h2>";
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
                margin-top: 20px;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div>
            <form action = "/login/login.php" method = "post">
                <input type = "submit" class = "field" name = "Logout" value = "Logout">
            </form>
        </div>
        <?php
            if(isset($_POST['Logout']) && !empty($_POST['Logout']))
            {
                unset($_SESSION['Account']);
                unset($_SESSION['Password']);
                session_destroy();
                //header("location: /login/login.php");
                echo "<script>location.href='/login/index.php';</script>";
            }
        ?>
    </body>
</html>