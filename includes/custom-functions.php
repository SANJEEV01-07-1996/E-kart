<?php
/*
---------------------------------------------
	functions
---------------------------------------------
	1. get_product_by_id($id=null)
	2. get_product_by_variant_id($arr)
	3. convert_to_parent($measurement,$measurement_unit_id)
	4. rows_count($table,$field = '*',$where = '')
	5. get_configurations()
	6. get_balance($id)
	7. get_bonus($id)
	8. get_wallet_balance($id)
	9. update_wallet_balance($balance,$id)
	10. add_wallet_transaction($id,$type,$amount,$message,$status = 1)
	11. update_order_item_status($order_item_ids,$order_id,$status)
	12. validate_promo_code($user_id,$promo_code,$total)
	13. get_settings($variable,$is_json = false)
	14. send_order_update_notification($uid,$title,$message,$type)
	15. get_promo_details($promo_code)
	16. store_return_request($user_id,$order_id,$order_item_id)
	17. get_role($id)
	18. get_permissions($id)
*/
include_once('crud.php');
require_once('firebase.php');
require_once ('push.php');
require_once 'functions.php';
$fn = new functions;
class custom_functions{
    protected $db;
    function __construct(){
        $this->db = new Database();
        $this->db->connect();
        // date_default_timezone_set('Asia/Kolkata');
        } 
    
    function get_product_by_id($id=null){
         if(!empty($id)){
            $sql="SELECT * FROM products WHERE id=".$id;
         }else{
             $sql="SELECT * FROM products";
         }
        $this->db->sql($sql);
        $res = $this->db->getResult();
        $product = array();
        $i=1;
        foreach($res as $row){
            $sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=".$row['id'];
            $this->db->sql($sql);
            $product[$i] = $row;
            $product[$i]['variant'] = $this->db->getResult();
            $i++;
        }
        if(!empty($product)){
            return $product;
        }
    }
    function get_product_by_variant_id($arr){
        $arr = stripslashes($arr);
        if(!empty($arr)){
            $arr = json_decode($arr,1);
            // print_r($arr);
            $i=0;
            foreach($arr as $id){
                $sql="SELECT *,pv.id,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv JOIN products p ON pv.product_id=p.id WHERE pv.id=".$id;
                $this->db->sql($sql);
                $res[$i] = $this->db->getResult()[0];
                $i++;
            }
            if(!empty($res)){
                return $res;
            }
        }
        
    }

    function convert_to_parent($measurement,$measurement_unit_id){
        $sql="SELECT * FROM unit WHERE id=".$measurement_unit_id;
        $this->db->sql($sql);
        $unit = $this->db->getResult();
        if(!empty($unit[0]['parent_id'])){
            $stock=$measurement/$unit[0]['conversion'];
        }else{
            $stock = ($measurement)*$unit[0]['conversion'];
        }
            return $stock;
    }
    function rows_count($table,$field = '*',$where = ''){
        // Total count
        if(!empty($where))$where = "Where ".$where;
        $sql = "SELECT COUNT(".$field.") as total FROM ".$table." ".$where;
        // echo $sql;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        foreach($res as $row)
        return $row['total'];
    }
    public function get_configurations(){
        $sql = "SELECT value FROM settings WHERE `variable`='system_timezone'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if(!empty($res)){
            return json_decode($res[0]['value'],true);
        }else{
            return false;
        }
    }
    public function get_balance($id){
        $sql = "SELECT balance FROM delivery_boys WHERE id=".$id;
        // echo $sql;
        // echo $sql;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if(!empty($res)){
            return $res[0]['balance'];
        }else{
            return false;
        }
    }
    public function get_bonus($id){
        $sql = "SELECT bonus FROM delivery_boys WHERE id=".$id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if(!empty($res)){
            return $res[0]['bonus'];
        }else{
            return false;
        }
    }
    public function get_wallet_balance($id){
        $sql = "SELECT balance FROM users WHERE id=".$id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if(!empty($res)){
            return $res[0]['balance'];
        }else{
            return 0;
        }
    }
    public function update_wallet_balance($balance,$id){
        $data = array(
            'balance'=>$balance 
        );
        if($this->db->update('users',$data,'id='.$id))
            return true;
         else
            return false;
    }
    
    public function add_wallet_transaction($id,$type,$amount,$message='Used against Order Placement',$status = 1){
        $data = array(
            'user_id'=> $id,
            'type'=> $type,
            'amount'=> $amount,
            'message'=> $message,
            'status'=> $status
        );
        $this->db->insert('wallet_transactions',$data);
        return $this->db->getResult()[0];
    }
    public function update_order_item_status($order_item_ids,$order_id,$status){
        $order_item_ids = stripslashes($order_item_ids);
        if(!empty($order_item_ids)){
            $order_item_ids = json_decode($order_item_ids,1);
            
        }
        $order_item_ids = explode(',',$order_item_ids);
        $status[] = array( $status,date("d-m-Y h:i:sa") );
        $status = json_encode($status);
        $sql = "update order_items set status = '".$status."' WHERE id IN($order_item_ids)";
        echo $sql;
        return false;
    }
    public function validate_promo_code($user_id,$promo_code,$total){
        $sql = "select * from promo_codes where promo_code='".$promo_code."'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if(empty($res)){
            $response['error'] = true;
            $response['message'] = "Invalid promo code.";
            return $response;
            exit();
        }
        if($res[0]['status']==0){
            $response['error'] = true;
            $response['message'] = "This promo code is either expired / invalid.";
            return $response;
            exit();
        }
        
        $sql = "select id from users where id='".$user_id."'";
        $this->db->sql($sql);
        $res_user = $this->db->getResult();
        if(empty($res_user)){
            $response['error'] = true;
            $response['message'] = "Invalid user data.";
            return $response;
            exit();
        }
        
        $start_date = $res[0]['start_date'];
        $end_date = $res[0]['end_date'];
        $date = date('Y-m-d h:i:s a');
        
        if($date<$start_date){
            $response['error'] = true;
            $response['message'] = "This promo code can't be used before ".date('d-m-Y',strtotime($start_date))."";
            return $response;
            exit();
        }
        if($date>$end_date){
            $response['error'] = true;
            $response['message'] = "This promo code can't be used after ".date('d-m-Y',strtotime($end_date))."";
            return $response;
            exit();
        }
        if($total<$res[0]['minimum_order_amount']){
            $response['error'] = true;
            $response['message'] = "This promo code is applicable only for order amount greater than or equal to ".$res[0]['minimum_order_amount']."";
            return $response;
            exit();
    
        }
        //check how many users have used this promo code and no of users used this promo code crossed max users or not
        $sql = "select id from orders where promo_code='".$promo_code."' GROUP BY user_id";
        $this->db->sql($sql);
        $res_order = $this->db->numRows();
        
        if($res_order>=$res[0]['no_of_users']){
            $response['error'] = true;
            $response['message'] = "This promo code is applicable only for first ".$res[0]['no_of_users']." users.";
            return $response;
            exit();
    
        }
        //check how many times user have used this promo code and count crossed max limit or not
        if($res[0]['repeat_usage']==1){
            $sql = "select id from orders where user_id=".$user_id." and promo_code='".$promo_code."'";
            $this->db->sql($sql);
            $total_usage = $this->db->numRows();
            if($total_usage>=$res[0]['no_of_repeat_usage']){
                $response['error'] = true;
                $response['message'] = "This promo code is applicable only for ".$res[0]['no_of_repeat_usage']." times.";
                return $response;
                exit();
            }
    
    
        }
        //check if repeat usage is not allowed and user have already used this promo code 
        if($res[0]['repeat_usage']==0){
            $sql = "select id from orders where user_id=".$user_id." and promo_code='".$promo_code."'";
            $this->db->sql($sql);
            $total_usage = $this->db->numRows();
            if($total_usage>=1){
                $response['error'] = true;
                $response['message'] = "This promo code is applicable only for 1 time.";
                return $response;
                exit();
            }
    
    
        }
        if($res[0]['discount_type']=='percentage'){
            $percentage = $res[0]['discount'];
            $discount = $total/100*$percentage;
            if($discount>$res[0]['max_discount_amount']){
                $discount=$res[0]['max_discount_amount'];
            }
        }else{
            $discount=$res[0]['discount'];
        }
        $discounted_amount = $total - $discount;
        $response['error'] = false;
        $response['message'] = "promo code applied successfully.";
        $response['promo_code'] = $promo_code;
        $response['promo_code_message'] = $res[0]['message'];
        $response['total'] = $total;
        $response['discount'] = "$discount";
        $response['discounted_amount'] = "$discounted_amount";
        return $response;
        exit();
    }
    public function get_settings($variable,$is_json = false){
        if($variable=='logo' || $variable=='Logo'){
            $sql = "select value from `settings` where variable='Logo' OR variable='logo'";
        }else{
            $sql = "SELECT value FROM `settings` WHERE `variable`='$variable'";
        }
        
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if(!empty($res) && isset($res[0]['value'])){
            if($is_json)
                return json_decode($res[0]['value'],true);
            else
                return $res[0]['value'];
        }else{
            return false;
        }
    }
    public function send_order_update_notification($uid,$title,$message,$type){
        if($_SERVER['REQUEST_METHOD']=='POST'){
        //hecking the required params 
            //creating a new push
            /*dynamically getting the domain of the app*/
            $url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $url .= $_SERVER['SERVER_NAME'];
            $url .= $_SERVER['REQUEST_URI'];
            $server_url = dirname($url).'/';
            
            $push = null;
            //first check if the push has an image with it
                //if the push don't have an image give null in place of image
                $push = new Push(
                    $title,
                    $message,
                    null,
                    $type,
                    null
                );
            //getting the push from push object
            $mPushNotification = $push->getPush();
            
            //getting the token from database object
            $sql="SELECT fcm_id FROM users WHERE id = '".$uid."'";
            $this->db->sql($sql); 
            $res=$this->db->getResult();
            $token = array(); 
            foreach($res as $row){
                array_push($token, $row['fcm_id']);
            }
            
            //creating firebase class object 
            $firebase = new Firebase(); 
    
            //sending push notification and displaying result 
            $firebase->send($token, $mPushNotification);
            $response['error']=false;
            $response['message']="Successfully Send";
        }else{
            $response['error']=true;
            $response['message']='Invalid request';
        }
        // echo str_replace("\\/","/",json_encode($response['message']));
        // echo(json_encode($response));
    }
    public function get_promo_details($promo_code){
        $sql = "SELECT * FROM `promo_codes` WHERE `promo_code`='$promo_code'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if(!empty($res)){
                return $res;
        }else{
            return false;
        }
    }
    public function store_return_request($user_id,$order_id,$order_item_id){
        $sql = "select product_variant_id from order_items where id=".$order_item_id;
        $this->db->sql($sql);
        $res=$this->db->getResult();
        $pv_id = $res[0]['product_variant_id'];
        $sql = "select product_id from product_variant where id=".$pv_id;
        $this->db->sql($sql);
        $res=$this->db->getResult();

        $data = array(
            'user_id'=> $user_id,
            'order_id'=> $order_id,
            'order_item_id'=> $order_item_id,
            'product_id'=> $res[0]['product_id'],
            'product_variant_id'=> $pv_id
        );
        $this->db->insert('return_requests',$data);
        return $this->db->getResult()[0];
    }
    public function get_role($id){
        $sql = "SELECT role FROM admin WHERE id=".$id;
        // echo $sql;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if(!empty($res) && isset($res[0]['role'])){
            return $res[0]['role'];
        }else{
            return 0;
        }
    }
    public function get_permissions($id){
        $sql = "SELECT permissions FROM admin WHERE id=".$id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if(!empty($res) && isset($res[0]['permissions'])){
            return json_decode($res[0]['permissions'],true);
        }else{
            return 0;
        }
    }
}

?>