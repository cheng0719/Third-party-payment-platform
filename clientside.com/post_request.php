<title>Post request</title>
    <meta http-equiv="content-type" charset="UTF-8" />

    
        <form method="POST" action="http://www.serverside.com/api_controll.php">
            <!-- Account<input name="Account" type="text" />
            <br /> -->
            MerchantID:<input name="MerchantID" type="text" />
            <br />
            Password:<input name="Password" type="text" />
            <br />
            <!-- IPAddress<input name="IPAddress" type="text" />
            <br /> -->
            
            TradeRecordID:<input name="TradeRecordID" type="text" />
            <br />
            MerchantTradeNum:<input name="MerchantTradeNum" type="text" />
            <br />
            MerchantTradeDate:<input name="MerchantTradeDate" type="text" />
            <br />
            TotalAmount:<input name="TotalAmount" type="number" />
            <br />
            ItemName:<input name="ItemName" type="text" />
            <br />
            ExpireDate:<input name="ExpireDate" type="text" />
            <br />
            TradeDesc:<input name="TradeDesc" type="text" />
            <br />
            Remark:<input name="Remark" type="text" />
            <br />

            <select name="Method">
                <option value="">Request method</option>
                <!-- <option value="Search">Search</option> -->
                <option value="Create">Create</option>
                <option value="Update">Update</option>
                <option value="Delete">Delete</option>
            </select>
            <br/>

            <input type="submit" value="確定送出" />
        </form>