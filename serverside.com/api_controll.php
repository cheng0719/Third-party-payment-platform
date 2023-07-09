<?php
    date_default_timezone_set("Asia/Taipei");

    error_reporting(E_ALL ^ E_WARNING);
    
    ini_set('error_log', __DIR__ . "/api_error.log");

    require_once 'C:\Apache24\htdocs\serverside.com\config.php';
    require_once 'C:\Apache24\htdocs\serverside.com\CryptAES.php';

    function event_log($msg){
        $log_time = date('Y-m-d H:i:s');
        $log_filename = 'sys_event_log.log';
        $log_msg = '[' . $log_time . '] ' . $msg;

        file_put_contents($log_filename, $log_msg . PHP_EOL, FILE_APPEND);
    }


    set_error_handler(function ($error_no, $error_msg, $error_file, $error_line) {
        switch ($error_no) {
            case E_WARNING:
                if (strcmp('Division by zero', $error_msg) == 0) {
                    throw new \DivisionByZeroError($error_msg);
                }    
                $level_tips = 'PHP Warning: ';
                break;
            case E_NOTICE:
                $level_tips = 'PHP Notice: ';
                break;
            case E_DEPRECATED:
                $level_tips = 'PHP Deprecated: ';
                break;
            case E_USER_ERROR:
                $level_tips = 'User Error: ';
                break;
            case E_USER_WARNING:
                $level_tips = 'User Warning: ';
                break;
            case E_USER_NOTICE:
                $level_tips = 'User Notice: ';
                break;
            case E_USER_DEPRECATED:
                $level_tips = 'User Deprecated: ';
                break;
            case E_STRICT:
                $level_tips = 'PHP Strict: ';
                break;
            default:
                $level_tips = 'Unknow Type Error: ';
                break;
        }
    
        // do some handle
        $error = $level_tips . $error_msg . ' in ' . $error_file . ' on ' . $error_line;
        echo $error . PHP_EOL;
        
        // or throw a ErrorException back to try ... catch block
        // throw new \ErrorException($error);

    }, E_ALL | E_STRICT);

    

    function database_event_log($account, $ipAddress, $requestType, $eventType, $eventDescription, $tradeRecordID, $merchantID, $merchantTradeNum, $merchantTradeDate, $totalAmount, $itemName, $tradeDesc, $remark, $expireDate, $paymentStatus){
        try {
            // $dsn = 'pgsql:host=host;port=port;dbname=db;'
            $dsn = 'pgsql:host=localhost;port=5432;dbname=test;';
            $conn = new PDO(
                $dsn,
                'postgres',
                'w2e2i78727',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $stmt = $conn->prepare('insert into api_event_log values (Current_timestamp(0), :account, :ipaddress, :requesttype, :eventtype, :eventdescription, :traderecordid, :merchantid, :merchanttradenum, :merchanttradedate, :totalamount, :itemname, :tradedesc, :remark, :expiredate, :paymentstatus);');
            if(!$stmt->execute(array(
                'account' => $account,
                'ipaddress' => $ipAddress,
                'requesttype' => $requestType,
                'eventtype' => $eventType,
                'eventdescription' => $eventDescription,
                'traderecordid' => $tradeRecordID,
                'merchantid' => $merchantID,
                'merchanttradenum' => $merchantTradeNum,
                'merchanttradedate' => $merchantTradeDate,
                'totalamount' => $totalAmount,
                'itemname' => $itemName,
                'tradedesc' => $tradeDesc,
                'remark' => $remark,
                'expiredate' => $expireDate,
                'paymentstatus' => $paymentStatus
            )))
            {
                throw new Exception('Database event log failed');
            }

        } catch (PDOException $e) {
            event_log('Database event log failed. User[' . $account . '] IP_address[' . $ipAddress . '] Reuqest_type[' . $requestType . '] Event_type[' . $eventType . '] Event_description[' . $eventDescription . '] TradeRecordID[' . $tradeRecordID . '] MerchantID[' . $merchantID . '] MerchantTradeNum[' . $merchantTradeNum . '] MerchantTradeDate[' . $merchantTradeDate . '] TotalAmount[' . $totalAmount . '] ItemName[' . $itemName . '] TradeDesc[' . $tradeDesc . '] Remark[' . $remark . '] ExpireDate[' . $expireDate . '] PaymentStatus[' . $paymentstatus . ']');
            error_log($e . PHP_EOL);
        } catch (Exception $e) {
            event_log('Database event log failed. User[' . $account . '] IP_address[' . $ipAddress . '] Reuqest_type[' . $requestType . '] Event_type[' . $eventType . '] Event_description[' . $eventDescription . '] TradeRecordID[' . $tradeRecordID . '] MerchantID[' . $merchantID . '] MerchantTradeNum[' . $merchantTradeNum . '] MerchantTradeDate[' . $merchantTradeDate . '] TotalAmount[' . $totalAmount . '] ItemName[' . $itemName . '] TradeDesc[' . $tradeDesc . '] Remark[' . $remark . '] ExpireDate[' . $expireDate . '] PaymentStatus[' . $paymentstatus . ']');
            error_log($e . PHP_EOL);
        }
    }

    class ReturnCode
    {
        const SUCCESS = 100;
        const DATA_NOT_EXIST = 101;
        const INVALID_REQUEST = 102;
        const INVALID_AUTHENTICATION = 201;
        const INVALID_PERMISSION = 202;
        const SERVER_INTERNAL_ERROR = 202;
        const FAILED_DATABASE_CONNECTING = 301;
        const FAILED_DATABASE_PROCESSING = 302;
    }

    class TransactionData
    {
        private $TradeRecordID;
        private $MerchantID;
        private $MerchantTradeNum;
        private $MerchantTradeDate;
        private $TotalAmount;
        private $ItemName;
        private $ExpireDate;
        private $TradeDesc;
        private $Remark;
        private $Deletion;
        private $PaymentCompletion;

        /***
         *  Constructor
         */
        function __construct($traderecordid, $merchantid, $merchanttradenum, $merchanttradedate, $totalamount, $itemname, $expiredate, $tradedesc, $remark, $deletion, $paymentcompletion){
            try {
                $this->TradeRecordID = $traderecordid;
                $this->MerchantID = $merchantid;
                $this->MerchantTradeNum = $merchanttradenum;
                $this->MerchantTradeDate = $merchanttradedate;
                $this->TotalAmount = $totalamount;
                $this->ItemName = $itemname;
                $this->ExpireDate = $expiredate;
                $this->TradeDesc = $tradedesc;
                $this->Remark = $remark;
                $this->Deletion = $deletion;
                $this->PaymentCompletion = $paymentcompletion;
            } catch (Exception $e) {
                error_log($e . PHP_EOL);
                throw new Exception('Server internal error', ReturnCode::SERVER_INTERNAL_ERROR, $e);
            }
        }

        function get_tradeRecordID(){
            return $this->TradeRecordID;
        }
        function get_merchantID(){
            return $this->MerchantID;
        }
        function get_merchantTradeNum(){
            return $this->MerchantTradeNum;
        }
        function get_merchantTradeDate(){
            return $this->MerchantTradeDate;
        }
        function get_totalAmount(){
            return $this->TotalAmount;
        }
        function get_itemName(){
            return $this->ItemName;
        } 
        function get_expireDate(){
            return $this->ExpireDate;
        }   
        function get_tradeDesc(){
            return $this->TradeDesc;
        } 
        function get_remark(){
            return $this->Remark;
        } 
        function get_deletion(){
            return $this->Deletion;
        } 
        function get_paymentCompletion(){
            return $this->PaymentCompletion;
        } 
    }




    class DataFactory
    {
        /***
         *  make a database connection
         */
        static function DBConnection($array): PDO
        {
            try {
                // $dsn = 'pgsql:host=host;port=port;dbname=db;'
                $dsn = 'pgsql:host=' . $array['host'] . ';port=' . $array['port'] . ';dbname=' . $array['dbname'] . ';';

                return new PDO(
                    $dsn,
                    $array['username'],
                    $array['password'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            } catch (PDOException $e) {
                error_log('PDO error: ' . $e . PHP_EOL);
                throw new Exception('Fail database connecting.', ReturnCode::FAILED_DATABASE_CONNECTING, $e);
            }
        }
        
        static function Validation($db_array, $merchantid, $password, $ip_address): bool
        {
            try {
                $conn = self::DBConnection($db_array);
                $stmt = $conn->prepare('select * from account where merchantid = :merchantid;');
                $stmt->bindParam(':merchantid', $merchantid);
                if(!$stmt->execute())
                {                    
                    throw new Exception('Failed database processing', ReturnCode::FAILED_DATABASE_PROCESSING);
                }
                $data = $stmt->fetch();
                if(empty($data))
                {
                    // $strings = '0123456789abcdefghijklmnopqrstuvwxyz';
                    // $str_gen = substr(str_shuffle($strings), 0, 15);
                    // $salt = '$5$' . $str_gen;
                    // $crypted_password = crypt($password, $salt);
                    // $stmt_1 = $conn->prepare('insert into account (merchantid, password, ip_address) values (:merchantid, :password, :ip_address);');
                    // $stmt_1->bindParam(':merchantid', $merchantid);
                    // $stmt_1->bindParam(':password', $crypted_password);
                    // $stmt_1->bindParam(':ip_address', $ip_address);
                    // $stmt_1->execute();
                    return false;
                }
                if(hash_equals($data['password'], crypt($password, $data['password'])))
                {
                    return true;
                }
                else
                {
                    // $strings = '0123456789abcdefghijklmnopqrstuvwxyz';
                    // $str_gen = substr(str_shuffle($strings), 0, 15);
                    // $salt = '$5$' . $str_gen;
                    // $crypted_password = crypt($password, $salt);
                    // $stmt_1 = $conn->prepare('insert into account (merchantid, password, ip_address) values (:merchantid, :password, :ip_address);');
                    // $stmt_1->bindParam(':merchantid', $merchantid);
                    // $stmt_1->bindParam(':password', $crypted_password);
                    // $stmt_1->bindParam(':ip_address', $ip_address);
                    // $stmt_1->execute();
                    return false;
                }  
                               
            } catch (Exception $e) {
                if(!strcmp($e->getMessage(), 'Failed database processing'))
                {
                    throw $e;
                }
                else
                {
                    error_log($e . PHP_EOL);
                    throw new Exception('Failed database processing', ReturnCode::FAILED_DATABASE_PROCESSING, $e);
                }
            }
        }

        static function SearchData($db_array, $input_array)
        {
            try {
                $conn = self::DBConnection($db_array);
                $stmt = $conn->prepare('select * from api_practice where traderecordid = :traderecordid;');
                $stmt->bindParam(':traderecordid', $input_array['traderecordid']);
                
                if(!$stmt->execute())
                {                    
                    throw new Exception('Failed database processing', ReturnCode::FAILED_DATABASE_PROCESSING);
                }

                $returnData = $stmt->fetch();

                if(empty($returnData))
                {                    
                    throw new Exception('Data not exist', ReturnCode::DATA_NOT_EXIST);
                }

                if($input_array['merchantid'] != $returnData['merchantid'])
                {                    
                    throw new Exception('Invalid permission', ReturnCode::INVALID_PERMISSION);
                }
                if($returnData['deletion'])
                {
                    throw new Exception('Data not exist', ReturnCode::DATA_NOT_EXIST);
                }

                // return $data;
                $return_obj = new TransactionData($returnData['traderecordid'], $returnData['merchantid'], $returnData['merchanttradenum'], $returnData['merchanttradedate'], $returnData['totalamount'], $returnData['itemname'], $returnData['expiredate'], $returnData['tradedesc'], $returnData['remark'], $returnData['deletion'], $returnData['paymentcompletion']);
                return $return_obj;

            } catch (Exception $e) {
                if(!strcmp($e->getMessage(), 'Failed database processing'))
                {
                    database_event_log($input_array['merchantid'], $input_array['ipaddress'], 'Search', 'Error', 'Database connection failed during process.', $input_array['traderecordid'], null, null, null, null, null, null, null, null, null);
                    throw $e;
                }
                else if(!strcmp($e->getMessage(), 'Data not exist'))
                {
                    database_event_log($input_array['merchantid'], $input_array['ipaddress'], 'Search', 'Error', 'Data not exist.', $input_array['traderecordid'], null, null, null, null, null, null, null, null, null);
                    throw $e;
                }
                else if(!strcmp($e->getMessage(), 'Invalid permission'))
                {
                    database_event_log($input_array['merchantid'], $input_array['ipaddress'], 'Search', 'Error', 'User permission is invalid.', $input_array['traderecordid'], null, null, null, null, null, null, null, null, null);
                    throw $e;
                }
                else
                {
                    database_event_log($input_array['merchantid'], $input_array['ipaddress'], 'Search', 'Error', 'Database connection failed during process.', $input_array['traderecordid'], null, null, null, null, null, null, null, null, null);
                    error_log($e . PHP_EOL);
                    throw new Exception('Failed database processing', ReturnCode::FAILED_DATABASE_PROCESSING, $e);
                }
            }
        }

        static function If_already_exist($db_array, $input_array)
        {
            try {
                $conn = self::DBConnection($db_array);
                $stmt_search = $conn->prepare('select * from api_practice where merchanttradenum = :merchanttradenum;');
                $stmt_search->bindParam(':merchanttradenum', $input_array['merchanttradenum']);
                if(!$stmt_search->execute())
                {
                    throw new Exception('Failed database processing', ReturnCode::FAILED_DATABASE_PROCESSING);
                }
                $search_data = $stmt_search->fetch();
                if(empty($search_data))
                {
                    return $search_data;
                }
                if($search_data['merchantid'] != $input_array['merchantid'] | $search_data['merchanttradedate'] != $input_array['merchanttradedate'] | $search_data['totalamount'] != $input_array['totalamount'] | $search_data['itemname'] != $input_array['itemname'] | $search_data['expiredate'] != $input_array['expiredate'] | $search_data['tradedesc'] != $input_array['tradedesc'] | $search_data['remark'] != $input_array['remark'])
                {
                    throw new Exception('Duplicated MerchantTradeNum', 0);
                }
                
                return $search_data;

            } catch (Exception $e) {
                if(!strcmp($e->getMessage(), 'Failed database processing'))
                {
                    database_event_log($input_array['merchantid'], $input_array['ipaddress'], 'Create', 'Error', 'Database connection failed during process.', null, $input_array['merchantid'], $input_array['merchanttradenum'], $input_array['merchanttradedate'], $input_array['totalamount'], $input_array['itemname'], $input_array['tradedesc'], $input_array['remark'], $input_array['expiredate'], null);
                    throw $e;
                }
                else if(!strcmp($e->getMessage(), 'Duplicated MerchantTradeNum'))
                {
                    database_event_log($input_array['merchantid'], $input_array['ipaddress'], 'Create', 'Error', 'Duplicated MerchantTradeNum.', null, $input_array['merchantid'], $input_array['merchanttradenum'], $input_array['merchanttradedate'], $input_array['totalamount'], $input_array['itemname'], $input_array['tradedesc'], $input_array['remark'], $input_array['expiredate'], null);
                    throw $e;
                }
                else
                {
                    database_event_log($input_array['merchantid'], $input_array['ipaddress'], 'Create', 'Error', 'Database connection failed during process', null, $input_array['merchantid'], $input_array['merchanttradenum'], $input_array['merchanttradedate'], $input_array['totalamount'], $input_array['itemname'], $input_array['tradedesc'], $input_array['remark'], $input_array['expiredate'], null);
                    error_log($e . PHP_EOL);
                    throw new Exception('Failed database processing', ReturnCode::FAILED_DATABASE_PROCESSING, $e);
                }
            }
            
        }

        static function InsertData($db_array, $input_array)
        {
            $check_data = self::If_already_exist($db_array, $input_array);
            if(!empty($check_data))
            {
                //return $check_data['traderecordid'];
                $return_obj = new TransactionData($check_data['traderecordid'], $check_data['merchantid'], $check_data['merchanttradenum'], $check_data['merchanttradedate'], $check_data['totalamount'], $check_data['itemname'], $check_data['expiredate'], $check_data['tradedesc'], $check_data['remark'], $check_data['deletion'], $check_data['paymentcompletion']);
                return $return_obj;
            }
            else
            {
                try {
                    $conn = self::DBConnection($db_array);
                    $stmt_insert = $conn->prepare('insert into api_practice (merchantid, merchanttradenum, merchanttradedate, 
                                                    totalamount, itemname, expiredate, tradedesc, remark) values (:merchantid, :merchanttradenum, :merchanttradedate, 
                                                    :totalamount, :itemname, :expiredate, :tradedesc, :remark) returning traderecordid;');
                    if(!$stmt_insert->execute(array(
                        'merchantid' => $input_array['merchantid'],
                        'merchanttradenum' => $input_array['merchanttradenum'],
                        'merchanttradedate' => $input_array['merchanttradedate'],
                        'totalamount' => $input_array['totalamount'],
                        'itemname' => $input_array['itemname'],
                        'expiredate' => $input_array['expiredate'],
                        'tradedesc' => $input_array['tradedesc'],
                        'remark' => $input_array['remark']
                    )))
                    {
                        throw new Exception('Failed database processing', ReturnCode::FAILED_DATABASE_PROCESSING);
                    }

                    $last_insert_id = $stmt_insert->fetch();
                    //return $last_insert_id['traderecordid'];
                    $stmt_search = $conn->prepare('select * from api_practice where traderecordid = :traderecordid');
                    $stmt_search->bindParam(':traderecordid', $last_insert_id['traderecordid']);
                    if(!$stmt_search->execute())
                    {
                        throw new Exception('Failed database processing', ReturnCode::FAILED_DATABASE_PROCESSING);
                    }
                    $receive_data = $stmt_search->fetch();
                    if(empty($receive_data))
                    {
                        throw new Exception('Failed database processing', ReturnCode::FAILED_DATABASE_PROCESSING);
                    }
                    $return_obj = new TransactionData($receive_data['traderecordid'], $receive_data['merchantid'], $receive_data['merchanttradenum'], $receive_data['merchanttradedate'], $receive_data['totalamount'], $receive_data['itemname'], $receive_data['expiredate'], $receive_data['tradedesc'], $receive_data['remark']);
                    return $return_obj;

                } catch (Exception $e) {
                    if(!strcmp($e->getMessage(), 'Failed database processing'))
                    {
                        database_event_log($input_array['merchantid'], $input_array['ipaddress'], 'Create', 'Error', 'Database connection failed during process', null, $input_array['merchantid'], $input_array['merchanttradenum'], $input_array['merchanttradedate'], $input_array['totalamount'], $input_array['itemname'], $input_array['tradedesc'], $input_array['remark'], $input_array['expiredate'], null);
                        throw $e;
                    }
                    else
                    {
                        database_event_log($input_array['merchantid'], $input_array['ipaddress'], 'Create', 'Error', 'Database connection failed during process', null, $input_array['merchantid'], $input_array['merchanttradenum'], $input_array['merchanttradedate'], $input_array['totalamount'], $input_array['itemname'], $input_array['tradedesc'], $input_array['remark'], $input_array['expiredate'], null);
                        error_log($e . PHP_EOL);
                        throw new Exception('Failed database processing', ReturnCode::FAILED_DATABASE_PROCESSING, $e);
                    }                   
                }               
            }
        }

        static function DeleteData($db_array, $input_array)
        {
            try {
                $conn = self::DBConnection($db_array);
                //check user permission
                $stmt_check = $conn->prepare('select merchantid from api_practice where 
                                                traderecordid = :traderecordid
                                                and merchantid = :merchantid
                                                and merchanttradenum = :merchanttradenum
                                                and merchanttradedate = :merchanttradedate
                                                and totalamount = :totalamount
                                                and itemname = :itemname
                                                and expiredate = :expiredate
                                                and tradedesc = :tradedesc
                                                and remark = :remark');
                
                if(!$stmt_check->execute(array(
                    'traderecordid' => $input_array['traderecordid'],
                    'merchantid' => $input_array['merchantid'],
                    'merchanttradenum' => $input_array['merchanttradenum'],
                    'merchanttradedate' => $input_array['merchanttradedate'],
                    'totalamount' => $input_array['totalamount'],
                    'itemname' => $input_array['itemname'],
                    'expiredate' => $input_array['expiredate'],
                    'tradedesc' => $input_array['tradedesc'],
                    'remark' => $input_array['remark']
                )))
                {
                    throw new Exception('Failed database processing', ReturnCode::FAILED_DATABASE_PROCESSING);
                }
                $receive_data = $stmt_check->fetch();
                if(empty($receive_data))
                {
                    throw new Exception('Data not exist', ReturnCode::DATA_NOT_EXIST);
                }
                if($receive_data['merchantid'] != $input_array['merchantid'])
                {
                    throw new Exception('Invalid permission', ReturnCode::INVALID_PERMISSION);
                }
                // delete data
                $stmt_delete = $conn->prepare('update api_practice
                                                set deletion = TRUE
                                                where traderecordid = :traderecordid;');
                $stmt_delete->bindParam(':traderecordid', $input_array['traderecordid']);
                if(!$stmt_delete->execute())
                {
                    throw new Exception('Failed database processing', ReturnCode::FAILED_DATABASE_PROCESSING);
                }
                // search again and return the data
                $stmt_search = $conn->prepare('select * from api_practice where traderecordid = :traderecordid and deletion = TRUE;');
                $stmt_search->bindParam(':traderecordid', $input_array['traderecordid']);
                if(!$stmt_search->execute())
                {
                    throw new Exception('Failed database processing', ReturnCode::FAILED_DATABASE_PROCESSING);
                }
                $receive_data = $stmt_search->fetch();
                if(empty($receive_data))
                {
                    throw new Exception('Failed database processing', ReturnCode::FAILED_DATABASE_PROCESSING);
                }
                $return_obj = new TransactionData($receive_data['traderecordid'], $receive_data['merchantid'], $receive_data['merchanttradenum'], $receive_data['merchanttradedate'], $receive_data['totalamount'], $receive_data['itemname'], $receive_data['expiredate'], $receive_data['tradedesc'], $receive_data['remark'], $receive_data['deletion'], $receive_data['paymentcompletion']);
                return $return_obj;
                

            } catch (Exception $e) {
                if(!strcmp($e->getMessage(), 'Failed database processing'))
                {
                    database_event_log($input_array['merchantid'], $input_array['ipaddress'], 'Delete', 'Error', 'Database connection failed during process.', $input_array['traderecordid'], null, null, null, null, null, null, null, null, null);
                    throw $e;
                }
                else if(!strcmp($e->getMessage(), 'Data not exist'))
                {
                    database_event_log($input_array['merchantid'], $input_array['ipaddress'], 'Delete', 'Error', 'Data not exist.', $input_array['traderecordid'], null, null, null, null, null, null, null, null, null);
                    throw $e;
                }
                else if(!strcmp($e->getMessage(), 'Invalid permission'))
                {
                    database_event_log($input_array['merchantid'], $input_array['ipaddress'], 'Delete', 'Error', 'User permission is invalid.', $input_array['traderecordid'], null, null, null, null, null, null, null, null, null);
                    throw $e;
                }
                else
                {
                    database_event_log($input_array['merchantid'], $input_array['ipaddress'], 'Delete', 'Error', 'Database connection failed during process.', $input_array['traderecordid'], null, null, null, null, null, null, null, null, null);
                    error_log($e . PHP_EOL);
                    throw new Exception('Failed database processing', ReturnCode::FAILED_DATABASE_PROCESSING, $e);
                }
            }
        }

        static function UpdateData($db_array, $input_array)
        {
            try {
                $conn = self::DBConnection($db_array);

                //check user permission
                $stmt_check = $conn->prepare('select * from api_practice where traderecordid = :traderecordid');
                $stmt_check->bindParam(':traderecordid', $input_array['traderecordid']);
                if(!$stmt_check->execute())
                {
                    throw new Exception('Failed database processing', ReturnCode::FAILED_DATABASE_PROCESSING);
                }
                $receive_data = $stmt_check->fetch();
                if(empty($receive_data))
                {
                    throw new Exception('Data not exist', ReturnCode::DATA_NOT_EXIST);
                }
                if($receive_data['merchantid'] != $input_array['merchantid'])
                {
                    throw new Exception('Invalid permission', ReturnCode::INVALID_PERMISSION);
                }

                // update data
                $stmt_delete = $conn->prepare('update api_practice
                                                set merchanttradedate = :merchanttradedate,
                                                    totalamount = :totalamount,
                                                    itemname = :itemname,
                                                    expiredate = :expiredate,
                                                    tradedesc = :tradedesc,
                                                    remark = :remark
                                                where traderecordid = :traderecordid;');
                if(!$stmt_delete->execute(array(
                    'merchanttradedate' => $input_array['merchanttradedate'],
                    'totalamount' => $input_array['totalamount'],
                    'itemname' => $input_array['itemname'],
                    'expiredate' => $input_array['expiredate'],
                    'tradedesc' => $input_array['tradedesc'],
                    'remark' => $input_array['remark'],
                    'traderecordid' => $input_array['traderecordid']
                )))
                {
                    throw new Exception('Failed database processing', ReturnCode::FAILED_DATABASE_PROCESSING);
                }
                
                // search again and return the data
                $stmt_search = $conn->prepare('select * from api_practice where traderecordid = :traderecordid');
                $stmt_search->bindParam('traderecordid', $input_array['traderecordid']);
                if(!$stmt_search->execute())
                {
                    throw new Exception('Failed database processing', ReturnCode::FAILED_DATABASE_PROCESSING);
                }
                $receive_data = $stmt_search->fetch();
                $return_obj = new TransactionData($receive_data['traderecordid'], $receive_data['merchantid'], $receive_data['merchanttradenum'], $receive_data['merchanttradedate'], $receive_data['totalamount'], $receive_data['itemname'], $receive_data['expiredate'], $receive_data['tradedesc'], $receive_data['remark'], $receive_data['deletion'], $receive_data['paymentcompletion']);
                return $return_obj;
                

            } catch (Exception $e) {
                if(!strcmp($e->getMessage(), 'Failed database processing'))
                {
                    database_event_log($input_array['merchantid'], $input_array['ipaddress'], 'Delete', 'Error', 'Database connection failed during process.', $input_array['traderecordid'], null, null, null, null, null, null, null, null, null);
                    throw $e;
                }
                else if(!strcmp($e->getMessage(), 'Data not exist'))
                {
                    database_event_log($input_array['merchantid'], $input_array['ipaddress'], 'Delete', 'Error', 'Data not exist.', $input_array['traderecordid'], null, null, null, null, null, null, null, null, null);
                    throw $e;
                }
                else if(!strcmp($e->getMessage(), 'Invalid permission'))
                {
                    database_event_log($input_array['merchantid'], $input_array['ipaddress'], 'Delete', 'Error', 'User permission is invalid.', $input_array['traderecordid'], null, null, null, null, null, null, null, null, null);
                    throw $e;
                }
                else
                {
                    database_event_log($input_array['merchantid'], $input_array['ipaddress'], 'Delete', 'Error', 'Database connection failed during process.', $input_array['traderecordid'], null, null, null, null, null, null, null, null);
                    error_log($e . PHP_EOL);
                    throw new Exception('Failed database processing', ReturnCode::FAILED_DATABASE_PROCESSING, $e);
                }
            }
        }
    }
    
    class Response
    {
        private $code;
        private $msg;
        private $transaction_data;
        private $url_param;

        function __construct(){

        }

        function set_code($code){
            $this->code = $code;
        }

        function set_msg($msg){
            $this->msg = $msg;
        }

        function set_transactionData($data){
            $this->transaction_data = $data;
        }

        function set_urlParam($tradeRecordID, $db_data){
            $key = 'abcdefghijklmnop';
            $iv = '1234567890123456';
            $password = 'abcdefghijklmnop';
            //$randomstring = 'vjfhnvkjfv';
            
            $bytes = random_bytes(8);
            $randomstring = bin2hex($bytes);

            // $db_data = array(
            //     'host' => $host,
            //     'port' => $port,
            //     'dbname' => $db,
            //     'username' => $username,
            //     'password' => $password,
            // );
            $conn = DataFactory::DBConnection($db_data);
            $stmt = $conn->prepare('update api_practice
                                    set randomstring = :randomstring
                                    where traderecordid = :traderecordid;');
            $stmt->bindParam(':randomstring', $randomstring);
            $stmt->bindParam(':traderecordid', $tradeRecordID);
            $stmt->execute();

            $encryption = new CryptAES($key, $iv, $password);
            $cipher = $encryption->encrypt($tradeRecordID . ' ' . $randomstring);

            $this->url_param = bin2hex($cipher);
        }

        // private function encrypt($string){
        //     $key = 'abcdefghijklmnop';
        //     $iv = '1234567890123456';
        //     $password = 'abcdefghijklmnop';
        //     $randomstring = 'ahdkvhey';

        //     $encryption = new CryptAES($key, $iv, $password);
        //     $cipher = $encryption->encrypt($string . ' ' . $randomstring);
        //     return $cipher;
        // }

        function produce_response(){
            $response_msg = array(
                'returnCode' => $this->code,
                'returnMessage' => $this->msg,
                'paymentUrl' => 'http://www.serverside.com/payment_page/payment.php' . '?id=' . $this->url_param,
                'info' => (empty($this->transaction_data)) ? array() : $this->transaction_data
            );
            return $response_msg;
        }
    }


    try {
        $db_data = array(
            'host' => $host,
            'port' => $port,
            'dbname' => $db,
            'username' => $username,
            'password' => $password,
        );
    
        $response_msg = new Response();
        
        if($_SERVER['REQUEST_METHOD'] == 'POST')  // deal with POST form and cURL POST
        {
            try{

                $result = null;

                if($_POST['Method'] == 'Create')  // Method = Create
                {
                    if(empty($_POST['MerchantID']))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Create', 'Error', 'Parameter [MerchantID] can not be empty.', null, $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);
                        throw new Exception('Invalid Request', ReturnCode::INVALID_REQUEST);
                    }

                    if(!DataFactory::Validation($db_data, $_POST['MerchantID'], $_POST['Password'], $_SERVER['REMOTE_ADDR']))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Create', 'Error', 'User login failed.', null, $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);
                        throw new Exception('Invalid authentication', ReturnCode::INVALID_AUTHENTICATION);
                    }
                    database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Create', 'Info', 'User login successfully.', null, $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);

                    if(empty($_POST['MerchantTradeNum']))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Create', 'Error', 'Parameter [MerchantTradeNum] can not be empty.', null, $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);
                        throw new Exception('Invalid Request', ReturnCode::INVALID_REQUEST);
                    }
                    if(empty($_POST['MerchantTradeDate']))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Create', 'Error', 'Parameter [MerchantTradeDate] can not be empty.', null, $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);
                        throw new Exception('Invalid Request', ReturnCode::INVALID_REQUEST);
                    }
                    if(empty($_POST['TotalAmount']))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Create', 'Error', 'Parameter [TotalAmount] can not be empty.', null, $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);
                        throw new Exception('Invalid Request', ReturnCode::INVALID_REQUEST);
                    }
                    if(empty($_POST['ItemName']))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Create', 'Error', 'Parameter [ItemName] can not be empty.', null, $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);
                        throw new Exception('Invalid Request', ReturnCode::INVALID_REQUEST);
                    }
                    if(empty($_POST['ExpireDate']))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Create', 'Error', 'Parameter [ExpireDate] can not be empty.', null, $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);
                        throw new Exception('Invalid Request', ReturnCode::INVALID_REQUEST);
                    }


                    $transaction_data = array(
                        'ipaddress' => $_SERVER['REMOTE_ADDR'],
                        'merchantid' => $_POST['MerchantID'],
                        'merchanttradenum' => $_POST['MerchantTradeNum'],
                        'merchanttradedate' => $_POST['MerchantTradeDate'],
                        'totalamount' => intval($_POST['TotalAmount']),
                        'itemname' => $_POST['ItemName'],
                        'expiredate' => $_POST['ExpireDate'],
                        'tradedesc' => $_POST['TradeDesc'],
                        'remark' => $_POST['Remark']
                    );
                    $result = DataFactory::InsertData($db_data, $transaction_data);

                    if(!isset($result))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Create', 'Error', 'Server internal error', $result->get_tradeRecordID(), $result->get_merchantID(), $result->get_merchantTradeNum(), $result->get_merchantTradeDate(), $result->get_totalAmount(), $result->get_itemName(), $result->get_tradeDesc(), $result->get_remark(), $result->get_expireDate(), null);
                        throw new Exception('Server internal error', ReturnCode::SERVER_INTERNAL_ERROR);
                    }
        
                    if(empty($result))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Create', 'Error', 'Server internal error', $result->get_tradeRecordID(), $result->get_merchantID(), $result->get_merchantTradeNum(), $result->get_merchantTradeDate(), $result->get_totalAmount(), $result->get_itemName(), $result->get_tradeDesc(), $result->get_remark(), $result->get_expireDate(), null);
                        throw new Exception('Server internal error', ReturnCode::SERVER_INTERNAL_ERROR);
                    }

                    $response_msg->set_code(ReturnCode::SUCCESS);
                    $response_msg->set_msg('Data saved successfully');
                    $response_msg->set_urlParam($result->get_tradeRecordID(), $db_data);
                    //$response_msg->set_transactionData(array('TradeRecordID' => $result));
                    database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Create', 'Info', 'Transaction data saved successfully.', $result->get_tradeRecordID(), $result->get_merchantID(), $result->get_merchantTradeNum(), $result->get_merchantTradeDate(), $result->get_totalAmount(), $result->get_itemName(), $result->get_tradeDesc(), $result->get_remark(), $result->get_expireDate(), null);
                }
                else if($_POST['Method'] == 'Search')
                {
                    if(empty($_POST['MerchantID']))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Search', 'Error', 'Parameter [MerchantID] can not be empty.', $_POST['TradeRecordID'], $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);
                        throw new Exception('Invalid Request', ReturnCode::INVALID_REQUEST);
                    }

                    if(!DataFactory::Validation($db_data, $_POST['MerchantID'], $_POST['Password'], $_SERVER['REMOTE_ADDR']))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Search', 'Error', 'User login failed.', $_POST['TradeRecordID'], $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);
                        throw new Exception('Invalid authentication', ReturnCode::INVALID_AUTHENTICATION);
                    }
                    database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Search', 'Info', 'User login successfully.', $_POST['TradeRecordID'], $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);

                    if(empty($_POST['TradeRecordID']) | $_POST['TradeRecordID'] <= 0)
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Search', 'Error', 'Parameter traderecordid can not be empty or less than 1.', $_POST['TradeRecordID'], null, null, null, null, null, null, null, null ,null);
                        throw new Exception('Invalid Request', ReturnCode::INVALID_REQUEST);
                    }

                    $input_array = array(
                        'merchantid' => $_POST['MerchantID'],
                        'ipaddress' => $_SERVER['REMOTE_ADDR'],
                        'traderecordid' => intval($_POST['TradeRecordID'])
                    );
    
                    $result = DataFactory::SearchData($db_data, $input_array);
    
                    if(!isset($result))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Search', 'Error', 'Database connection failed during process.', $_POST['TradeRecordID'], null, null, null, null, null, null, null, null, null);
                        throw new Exception('Server internal error', ReturnCode::SERVER_INTERNAL_ERROR);
                    }
                    if(empty($result))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Search', 'Error', 'Database connection failed during process.', $_POST['TradeRecordID'], null, null, null, null, null, null, null, null, null);
                        throw new Exception('Server internal error', ReturnCode::SERVER_INTERNAL_ERROR);
                    }

                    // $response = array(
                    //     'TradeRecordID' => $result->get_tradeRecordID(),
                    //     'MerchantID' => $result->get_merchantID(),
                    //     'MerchantTradeNum' => $result->get_merchantTradeNum(),
                    //     'MerchantTradeDate' => $result->get_merchantTradeDate(),
                    //     'TotalAmount' => $result->get_totalAmount(),
                    //     'ItemName' => $result->get_itemName(),
                    //     'ExpireDate' => $result->get_expireDate(),
                    //     'TradeDesc' => $reuslt->get_tradeDesc(),
                    //     'Remark' => $result->get_remark()
                    // );
                    $response_msg->set_code(ReturnCode::SUCCESS);
                    $response_msg->set_msg('Data searched successfully');
                    $response_msg->set_transactionData($response);
                    database_event_log($_GET['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Search', 'Info', 'Data searched successfully.', $result->get_tradeRecordID(), $result->get_merchantID(), $result->get_merchantTradeNum(), $result->get_merchantTradeDate(), $result->get_totalAmount(), $result->get_itemName(), $result->get_tradeDesc(), $result->get_remark(), $result->get_expireDate(), null);
    

                }
                else if($_POST['Method'] == 'Update')  // Method = Update
                {
                    if(empty($_POST['MerchantID']))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Update', 'Error', 'Parameter [MerchantID] can not be empty.', $_POST['TradeRecordID'], $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);
                        throw new Exception('Invalid Request', ReturnCode::INVALID_REQUEST);
                    }

                    if(!DataFactory::Validation($db_data, $_POST['MerchantID'], $_POST['Password'], $_SERVER['REMOTE_ADDR']))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Update', 'Error', 'User login failed.', $_POST['TradeRecordID'], $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);
                        throw new Exception('Invalid authentication', ReturnCode::INVALID_AUTHENTICATION);
                    }
                    database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Update', 'Info', 'User login successfully.', $_POST['TradeRecordID'], $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);

                    if(empty($_POST['TradeRecordID']) | empty($_POST['MerchantTradeNum']) | empty($_POST['MerchantTradeDate']) | empty($_POST['TotalAmount']) | empty($_POST['ItemName']))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Update', 'Error', 'Parameter can not be empty.', $_POST['TradeRecordID'], $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);
                        throw new Exception('Invalid Request', ReturnCode::INVALID_REQUEST);
                    }

                    $update_data = array(
                        'traderecordid' => intval($_POST['TradeRecordID']),
                        'merchantid' => $_POST['MerchantID'],
                        'ipaddress' => $_SERVER['REMOTE_ADDR'],
                        'merchanttradedate' => $_POST['MerchantTradeDate'],
                        'totalamount' => intval($_POST['TotalAmount']),
                        'itemname' => $_POST['ItemName'],
                        'expiredate' => $_POST['ExpireDate'],
                        'tradedesc' => (empty($_POST['TradeDesc'])) ? null : $_POST['TradeDesc'],
                        'remark' => (empty($_POST['Remark'])) ? null : $_POST['Remark']
                    );

                    $result = DataFactory::UpdateData($db_data, $update_data);
                    if(!isset($result))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Update', 'Error', 'Server internal error', $result->get_tradeRecordID(), $result->get_merchantID(), $result->get_merchantTradeNum(), $result->get_merchantTradeDate(), $result->get_totalAmount(), $result->get_itemName(), $result->get_tradeDesc(), $result->get_remark(), $result->get_expireDate(), null);
                        throw new Exception('Server internal error', ReturnCode::SERVER_INTERNAL_ERROR);
                    }
        
                    if(empty($result))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Update', 'Error', 'Server internal error', $result->get_tradeRecordID(), $result->get_merchantID(), $result->get_merchantTradeNum(), $result->get_merchantTradeDate(), $result->get_totalAmount(), $result->get_itemName(), $result->get_tradeDesc(), $result->get_remark(), $result->get_expireDate(), null);
                        throw new Exception('Server internal error', ReturnCode::SERVER_INTERNAL_ERROR);
                    }

                    $response_msg->set_code(ReturnCode::SUCCESS);
                    $response_msg->set_msg('Data updated successfully');

                    database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Update', 'Info', 'Transaction data updated successfully.', $result->get_tradeRecordID(), $result->get_merchantID(), $result->get_merchantTradeNum(), $result->get_merchantTradeDate(), $result->get_totalAmount(), $result->get_itemName(), $result->get_tradeDesc(), $result->get_remark(), $result->get_expireDate(), null);

                    // if(DataFactory::UpdateData($db_data, $update_data))
                    // {
                    //     $response_msg->set_code(ReturnCode::SUCCESS);
                    //     $response_msg->set_msg('Data updated successfully');    
                    //     //$response_msg->set_transactionData(array('TradeRecordID' => $result));
                    //     database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Update', 'Info', 'Transaction data deleted successfully.', $_POST['TradeRecordID'], null, null, null, null, null, null, null, null);
                    // }
                    // else
                    // {
                    //     $response_msg->set_code(ReturnCode::SERVER_INTERNAL_ERROR);
                    //     $response_msg->set_msg('Server internal error');
                    //     //$response_msg->set_transactionData(array('TradeRecordID' => $result));
                    //     database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Update', 'Error', 'Server internal error.', $_POST['TradeRecordID'], null, null, null, null, null, null, null, null);
                    // }
                }
                else if($_POST['Method'] == 'Delete')  // Method = Delete
                {
                    if(empty($_POST['MerchantID']))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Delete', 'Error', 'Parameter [MerchantID] can not be empty.', $_POST['TradeRecordID'], $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);
                        throw new Exception('Invalid Request', ReturnCode::INVALID_REQUEST);
                    }

                    if(!DataFactory::Validation($db_data, $_POST['MerchantID'], $_POST['Password'], $_SERVER['REMOTE_ADDR']))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Delete', 'Error', 'User login failed.', $_POST['TradeRecordID'], $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);
                        throw new Exception('Invalid authentication', ReturnCode::INVALID_AUTHENTICATION);
                    }
                    database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Delete', 'Info', 'User login successfully.', $_POST['TradeRecordID'], $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);

                    if(empty($_POST['MerchantTradeNum']))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Delete', 'Error', 'Parameter [MerchantTradeNum] can not be empty.', $_POST['TradeRecordID'], $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);
                        throw new Exception('Invalid Request', ReturnCode::INVALID_REQUEST);
                    }
                    if(empty($_POST['MerchantTradeDate']))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Delete', 'Error', 'Parameter [MerchantTradeDate] can not be empty.', $_POST['TradeRecordID'], $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);
                        throw new Exception('Invalid Request', ReturnCode::INVALID_REQUEST);
                    }
                    if(empty($_POST['TotalAmount']))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Delete', 'Error', 'Parameter [TotalAmount] can not be empty.', $_POST['TradeRecordID'], $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);
                        throw new Exception('Invalid Request', ReturnCode::INVALID_REQUEST);
                    }
                    if(empty($_POST['ItemName']))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Delete', 'Error', 'Parameter [ItemName] can not be empty.', $_POST['TradeRecordID'], $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);
                        throw new Exception('Invalid Request', ReturnCode::INVALID_REQUEST);
                    }
                    if(empty($_POST['ExpireDate']))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Delete', 'Error', 'Parameter [ExpireDate] can not be empty.', $_POST['TradeRecordID'], $_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'], $_POST['TotalAmount'], $_POST['ItemName'], $_POST['TradeDesc'], $_POST['Remark'], $_POST['ExpireDate'], null);
                        throw new Exception('Invalid Request', ReturnCode::INVALID_REQUEST);
                    }

                    $deleted_data = array(
                        'ipaddress' => $_SERVER['REMOTE_ADDR'],
                        'traderecordid' => intval($_POST['TradeRecordID']),
                        'merchantid' => $_POST['MerchantID'],
                        'merchanttradenum' => $_POST['MerchantTradeNum'],
                        'merchanttradedate' => $_POST['MerchantTradeDate'],
                        'totalamount' => intval($_POST['TotalAmount']),
                        'itemname' => $_POST['ItemName'],
                        'expiredate' => $_POST['ExpireDate'],
                        'tradedesc' => (empty($_POST['TradeDesc'])) ? null : $_POST['TradeDesc'],
                        'remark' => (empty($_POST['Remark'])) ? null : $_POST['Remark']
                    );

                    $result = DataFactory::DeleteData($db_data, $deleted_data);
                    if(!isset($result))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Delete', 'Error', 'Server internal error', $result->get_tradeRecordID(), $result->get_merchantID(), $result->get_merchantTradeNum(), $result->get_merchantTradeDate(), $result->get_totalAmount(), $result->get_itemName(), $result->get_tradeDesc(), $result->get_remark(), $result->get_expireDate(), null);
                        throw new Exception('Server internal error', ReturnCode::SERVER_INTERNAL_ERROR);
                    }
        
                    if(empty($result))
                    {
                        database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Delete', 'Error', 'Server internal error', $result->get_tradeRecordID(), $result->get_merchantID(), $result->get_merchantTradeNum(), $result->get_merchantTradeDate(), $result->get_totalAmount(), $result->get_itemName(), $result->get_tradeDesc(), $result->get_remark(), $result->get_expireDate(), null);
                        throw new Exception('Server internal error', ReturnCode::SERVER_INTERNAL_ERROR);
                    }

                    $response_msg->set_code(ReturnCode::SUCCESS);
                    $response_msg->set_msg('Data deleted successfully');

                    database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Delete', 'Info', 'Transaction data deleted successfully.', $result->get_tradeRecordID(), $result->get_merchantID(), $result->get_merchantTradeNum(), $result->get_merchantTradeDate(), $result->get_totalAmount(), $result->get_itemName(), $result->get_tradeDesc(), $result->get_remark(), $result->get_expireDate(), null);

                    // if(DataFactory::DeleteData($db_data, $deleted_data))
                    // {
                    //     $response_msg->set_code(ReturnCode::SUCCESS);
                    //     $response_msg->set_msg('Data deleted successfully');
                    //     //$response_msg->set_transactionData(array('TradeRecordID' => $result));
                    //     database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Delete', 'Info', 'Transaction data deleted successfully.', $_POST['TradeRecordID'], null, null, null, null, null, null, null, null);
                    // }
                    // else
                    // {
                    //     $response_msg->set_code(ReturnCode::SERVER_INTERNAL_ERROR);
                    //     $response_msg->set_msg('Server internal error');
                    //     //$response_msg->set_transactionData(array('TradeRecordID' => $result));
                    //     database_event_log($_POST['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Delete', 'Error', 'Server internal error.', $_POST['TradeRecordID'], null, null, null, null, null, null, null, null);
                    // }
                }

                
    
                $response_transactionData = array(
                    'TradeRecordID' => $result->get_tradeRecordID(),
                    'MerchantID' => $result->get_merchantID(),
                    'MerchantTradeNum' => $result->get_merchantTradeNum(),
                    'MerchantTradeDate' => $result->get_merchantTradeDate(),
                    'TotalAmount' => $result->get_totalAmount(),
                    'ItemName' => $result->get_itemName(),
                    'ExpireDate' => $result->get_expireDate(),
                    'TradeDesc' => (empty($result->get_tradeDesc())) ? '' : $result->get_tradeDesc(),
                    'Remark' => (empty($result->get_remark())) ? '' : $result->get_remark(),
                    'Deletion' => (empty($result->get_deletion())) ? '' : $result->get_deletion(),
                    'PaymentCompletion' => (empty($result->get_paymentCompletion())) ? '' : $result->get_paymentCompletion()
                );
                $response_msg->set_transactionData($response_transactionData);
                

            } catch (Exception $e) {
                $response_msg->set_code($e->getCode());
                $response_msg->set_msg($e->getMessage());
            }
    
            // $response_msg_ = json_encode($response_msg->produce_response(), JSON_PRETTY_PRINT);
            // echo "<pre>".$response_msg_."<pre/>";
            $response_msg_ = json_encode($response_msg->produce_response());
            echo $response_msg_;
        }
        else  // deal with GET form
        {
            try{  
                if(!DataFactory::Validation($db_data, $_GET['MerchantID'], $_GET['Password'], $_SERVER['REMOTE_ADDR']))
                {
                    database_event_log($_GET['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Search', 'Error', 'User login failed.', $_GET['TradeRecordID'], null, null, null, null, null, null, null, null,  null);
                    throw new Exception('Invalid authentication', ReturnCode::INVALID_AUTHENTICATION);
                }
                database_event_log($_GET['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Search', 'Info', 'User login successfully.', $_GET['TradeRecordID'], null, null, null, null, null, null, null, null, null);

                if(empty($_GET['TradeRecordID']) | $_GET['TradeRecordID'] <= 0)
                {
                    database_event_log($_GET['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Search', 'Error', 'Parameter traderecordid can not be empty or less than 1.', $_GET['TradeRecordID'], null, null, null, null, null, null, null, null ,null);
                    throw new Exception('Invalid Request', ReturnCode::INVALID_REQUEST);
                }

                $input_array = array(
                    'merchantid' => $_GET['MerchantID'],
                    'ipaddress' => $_SERVER['REMOTE_ADDR'],
                    'traderecordid' => intval($_GET['TradeRecordID'])
                );

                $result = DataFactory::SearchData($db_data, $input_array);

                if(!isset($result))
                {
                    database_event_log($_GET['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Search', 'Error', 'Database connection failed during process.', $_GET['TradeRecordID'], null, null, null, null, null, null, null, null, null);
                    throw new Exception('Server internal error', ReturnCode::SERVER_INTERNAL_ERROR);
                }
                if(empty($result))
                {
                    database_event_log($_GET['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Search', 'Error', 'Database connection failed during process.', $_GET['TradeRecordID'], null, null, null, null, null, null, null, null, null);
                    throw new Exception('Server internal error', ReturnCode::SERVER_INTERNAL_ERROR);
                }
    
                $response = array(
                    'TradeRecordID' => $result->get_tradeRecordID(),
                    'MerchantID' => $result->get_merchantID(),
                    'MerchantTradeNum' => $result->get_merchantTradeNum(),
                    'MerchantTradeDate' => $result->get_merchantTradeDate(),
                    'TotalAmount' => $result->get_totalAmount(),
                    'ItemName' => $result->get_itemName(),
                    'ExpireDate' => $result->get_expireDate(),
                    'TradeDesc' => $result->get_tradeDesc(),
                    'Remark' => $result->get_remark(),
                    'Deletion' => $result->get_deletion(),
                    'PaymentCompletion' => $result->get_paymentCompletion()
                );
                $response_msg->set_code(ReturnCode::SUCCESS);
                $response_msg->set_msg('Data searched successfully');
                $response_msg->set_transactionData($response);
                database_event_log($_GET['MerchantID'], $_SERVER['REMOTE_ADDR'], 'Search', 'Info', 'Data searched successfully.', $result->get_tradeRecordID(), $result->get_merchantID(), $result->get_merchantTradeNum(), $result->get_merchantTradeDate(), $result->get_totalAmount(), $result->get_itemName(), $result->get_tradeDesc(), $result->get_remark(), $result->get_expireDate(), null);

            } catch (Exception $e) {
                $response_msg->set_code($e->getCode());
                $response_msg->set_msg($e->getMessage());
                
            }
    
            $response_msg_ = json_encode($response_msg->produce_response(), JSON_PRETTY_PRINT);
            echo "<pre>".$response_msg_."<pre/>";
            
        }
    } catch (\ErrorException $errorException) {

        error_log('ErrorException: ' . $errorException . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo 'ErrorException' . PHP_EOL;

    } catch (\Exception $exception) {

        error_log('Exception: ' . $exception . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo 'Exception' . PHP_EOL;

    } catch (\ParseError $parseError) {

        error_log('Parse Error: ' . $parseError . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo 'ParseError' . PHP_EOL;

    } catch (\ArgumentCountError $argumentCountError ) {

        error_log('Argument Count Error: ' . $argumentCountError . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo 'ArgumentCountError' . PHP_EOL;

    } catch (\TypeError $typeError) {

        error_log('Type Error: ' . $typeError . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo 'TypeError' . PHP_EOL;

    } catch (\DivisionByZeroError $divisionByZeroError) {

        error_log('Division By Zero Error: ' . $divisionByZeroError . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo 'DivisionByZeroError' . PHP_EOL;

    } catch (\ArithmeticError $arithmeticError) {

        error_log('Arithmetic Error: ' . $arithmeticError . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo 'ArithmeticError' . PHP_EOL;

    } catch (\AssertionError $assertionError) {

        error_log('Assertion Error: ' . $assertionError . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo 'AssertionError' . PHP_EOL;

    } catch (\Error $error) {

        error_log('Error: ' . $error . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo 'Error' . PHP_EOL;

    }
?>