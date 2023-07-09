<!-- <!DOCTYPE html> -->
<html>
    <head>
        <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
        <meta http-equiv="content-type" charset="UTF-8" />
        <title>Search Page</title>
    </head>
    <body>
        <div class="container">
            <form class="input_content" method="GET" action="http://localhost/api_practice/api_controll.php">
                MerchantID : <input class="merchantid" name="MerchantID" type="text" />
                <br />
                Password : <input class="password" name="Password" type="text" />
                <br />
                TradeRecordID : <input class="traderecordid" name="TradeRecordID" type="text" />
                <br />
                <input type="submit" value="確定送出" />
            </form>
        </div>
    </body>
</html>