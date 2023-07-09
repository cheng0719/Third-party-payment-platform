<?php
    $cn = pg_connect(" host=localhost port=5432 dbname=test user=postgres password=w2e2i78727 ");
    if($cn)
    {
        echo "success";
    }
?>

<!-- if(isset($_POST['login']) && !empty($_POST['login']))
    {
        // echo $_POST['Account'];
        $Account = $_POST['Account'];
        $Pass = $_POST['Pass'];

        $query = "select * from login_test where account = '$S1' and password = '$S2'";
        $select = pg_query_params($conn, $query, array($Account, $Pass));
        $row = pg_fetch_array($select);

        if(is_array($row))
        {
            $_SESSION["Account"] = $row['account'];
            $_SESSION["Pass"] = $row['password'];
        }
        else
        {
            echo "<script>alert(Invalid!)</script>";
        }
    }
    if(isset($_SESSION["Account"]))
    {
        header("location: login.php");
    } -->