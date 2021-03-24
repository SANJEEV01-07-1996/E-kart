<?php
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


include_once('../../includes/crud.php');
$db=new Database();
$db->connect(); 
include_once('../../includes/variables.php');

/* 
-------------------------------------------
APIs for Delivery Boys
-------------------------------------------
1. login
2. get_delivery_boy_by_id  
3. update_delivery_boy_profile 
4. get_orders_by_delivery_boy_id 
5. get_order_by_id
6. update_order_status
7. get_fund_transfers
-------------------------------------------

-------------------------------------------

*/
if(!$_POST['accesskey'] AND $_POST['accesskey'] == $access_key){
    $response['error'] = true;
	$response['message'] = "No Accsess key found!";
	print_r(json_encode($response));
	return false;
	exit();
}

if(isset($_POST['login'])){
     /* 
    1.Login
        accesskey:90336
        mobile:9876543210
        password:12345678
    */
    
    if(empty(trim($_POST['mobile']))){
        $response['error'] = true;
    	$response['message'] = "Mobile should be filled!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }

    if(empty($_POST['password'])){
        $response['error'] = true;
    	$response['message'] = "Password should be filled!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }
    
    $mobile = $db->escapeString(trim($_POST['mobile']));
    $password = md5($_POST['password']);
    $sql = "SELECT * FROM delivery_boys	WHERE mobile = '".$mobile."' AND password = '".$password."'";
	$db->sql($sql);
	$res=$db->getResult();
	$num = $db->numRows($res);
	$db->disconnect(); 
	if($num == 1){
	    if($res[0]['status'] == 0){
	        $response['error'] = true;
	        $response['message'] = "It seems your acount is not active please contact admin for more info!"; 
	        $response['data'] = array();
		}else{
			$response['error'] = false;
            $response['message'] = "Delivery Boy Login Susseccfully";
            $response['data'] = $res;
		}
	}else{
		$response['error'] = true;
		$response['message'] = "No data found!";
	}
	print_r(json_encode($response));
}else {
	$response['error'] = true;
	$response['message'] = "Invalid Call of API!";
}


/* 
---------------------------------------------------------------------------------------------------------
*/
	
if(isset($_POST['get_delivery_boy_by_id'])){
    
    /* 
    2.get_delivery_boy_by_id
        accesskey:90336
        id:78
    */
    $id = $db->escapeString(trim($_POST['id']));
    $sql = "SELECT * FROM delivery_boys	WHERE id = '".$id."'";
	$db->sql($sql);
	$res=$db->getResult();
	$num = $db->numRows($res);
	$db->disconnect(); 
	if($num == 1){
    	$response['error'] = false;
        $response['message'] = "Delivery Boy Data Fetched Susseccfully";
        /*foreach($res as $row){
            $data = array(
				'id'=>$row['id'], 
				'name'=>$row['name'], 
				'mobile'=>$row['mobile'], 
				'password'=>$row['password'], 
				'address'=>$row['address'], 
				'bonus'=>$row['bonus'], 
				'balance'=>$row['balance'], 
				'status'=>$row['status']
			); 
        }*/
        $response['data'] = $res;
	}else{
		$response['error'] = true;
		$response['message'] = "No data found!";
	}
	print_r(json_encode($response));
}else {
	$response['error'] = true;
	$response['message'] = "Invalid Call of API!";

}

/* 
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['get_orders_by_delivery_boy_id'])){
    
    /* 
    4.get_orders_by_delivery_boy_id
        accesskey:90336
        id:40        // {optional}          
        order_id:1001        // {optional}  
        offset:0        // {optional}
        limit:10        // {optional}
        
        sort:id / user_id           // {optional}
        order:DESC / ASC            // {optional}
        
        search:search_value         // {optional}
        filter_order:filter_order_status         // {optional} 
    */
    $response_data = array();
    
    $id = ( isset($_POST['id']) && !empty(trim($_POST['id'])) && is_numeric($_POST['id']) ) ? $db->escapeString(trim($_POST['id'])) : '';
    $order_id = ( isset($_POST['order_id']) && !empty(trim($_POST['order_id'])) && is_numeric($_POST['order_id']) ) ? $db->escapeString(trim($_POST['order_id'])) : '';
    $where = '';
    $offset = ( isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset']) ) ? $db->escapeString(trim($_POST['offset'])) : 0;
    $limit = ( isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit']) ) ? $db->escapeString(trim($_POST['limit'])) : 10;
    
    $sort = ( isset($_POST['sort']) && !empty(trim($_POST['sort'])) ) ? $db->escapeString(trim($_POST['sort'])) : 'id';
    $order = ( isset($_POST['order']) && !empty(trim($_POST['order'])) ) ? $db->escapeString(trim($_POST['order'])) : 'DESC';
    if(isset($_POST['search']) && !empty($_POST['search'])){
        $search = $_POST['search'];
        $where .= " where (name like '%".$search."%' OR o.id like '%".$search."%' OR o.mobile like '%".$search."%' OR address like '%".$search."%' OR `payment_method` like '%".$search."%' OR `delivery_charge` like '%".$search."%' OR `delivery_time` like '%".$search."%' OR o.`status` like '%".$search."%' OR `date_added` like '%".$search."%')";
    }
    
    if(isset($_POST['filter_order']) && $_POST['filter_order']!=''){
        $filter_order=$db->escapeString($_POST['filter_order']);
        if(isset($_POST['search']) && $_POST['search']!='' ){
            $where .=" and `active_status`='".$filter_order."'";
        }else{
            $where .=" where `active_status`='".$filter_order."'";
        }
    }
    
    if(empty($where)){
        if($id==""){
           $where .= (!empty($order_id))?" AND o.id = $order_id":""; 
        }else{
           $where .= " WHERE delivery_boy_id = ".$id; 
        }   
    }else{
        if($id==""){
           $where .= (!empty($order_id))?" AND o.id = $order_id":""; 
        }else{
            $where .= " AND delivery_boy_id = ".$id; 
        } 
    }
    
    $sql = "SELECT COUNT(o.id) as total FROM `orders` o JOIN users u ON u.id=o.user_id".$where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach($res as $row){
        $total = $row['total'];
    }
    $sql="select o.*,u.name as name FROM orders o JOIN users u ON u.id=o.user_id".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
    $db->sql($sql);
    $res = $db->getResult();
    
    for($i=0;$i<count($res);$i++) {
        $sql="select oi.*,p.name as name, v.measurement, (SELECT short_code FROM unit un where un.id=v.measurement_unit_id)as mesurement_unit_name,(SELECT status FROM orders o where o.id=oi.order_id)as order_status from `order_items` oi 
            join product_variant v on oi.product_variant_id=v.id 
            join products p on p.id=v.product_id 
            where oi.order_id=".$res[$i]['id'];
            
        $db->sql($sql);
        $res[$i]['items'] = $db->getResult();
    }
    $rows1 = $tempRow = array();
    $response_data['total'] = $total;
    
    foreach($res as $row){
        $items = $row['items'];
        $items1 = $temp = array();
        $total_amt = 0;
        /*  */
        foreach($items as $item){  
            $temp = array(
                'id' => $item['id'], 
                'product_variant_id' => $item['product_variant_id'], 
                'name' => $item['name'], 
                'unit' => $item['measurement'].$item['mesurement_unit_name'], 
                'price' => $item['price'], 
                'quantity' => $item['quantity'], 
                'subtotal' => $item['quantity'] * $item['price']
            ); 
            $total_amt += $item['sub_total'];
            $items1[] = $temp;
        }
        
        if($row['active_status'] == 'received'){
            $active_status = $row['active_status'];
        }
        if($row['active_status'] == 'processed'){
            $active_status = $row['active_status'];
        }
        if($row['active_status'] == 'shipped'){
            $active_status = $row['active_status'];
        }
        if($row['active_status']=='delivered'){
            $active_status = $row['active_status'];
        }
        if($row['active_status']=='returned' || $row['active_status'] == 'cancelled' ){
            $active_status = $row['active_status'];
        }
        
        $discounted_amount = $row['total'] * $row['items'][0]['discount'] / 100;
        $final_total = $row['total'] - $discounted_amount;
        
        $discount_in_rupees = $row['total']-$final_total;
        $discount_in_rupees = floor($discount_in_rupees);
        $tempRow['id'] = $row['id'];
        $tempRow['user_id'] = $row['user_id'];
        $tempRow['name'] = $row['name'];
        $tempRow['mobile'] = $row['mobile'];
        $tempRow['delivery_charge'] = $row['delivery_charge'];
        $tempRow['items'] = $items1;
        $tempRow['total'] = $row['total'];
        $tempRow['tax'] = $row['tax_amount'].'('.$row['tax_percentage'].'%)';
        $tempRow['promo_discount'] = $row['promo_discount'];
        $tempRow['wallet_balance'] = $row['wallet_balance'];
        $tempRow['discount'] = $discount_in_rupees.'('.$row['items'][0]['discount'].'%)';
        $tempRow['qty'] = $row['items'][0]['quantity'];
        $tempRow['final_total'] = ceil($row['final_total']);
        $tempRow['promo_code'] = $row['promo_code'];
        $tempRow['deliver_by'] = $row['items'][0]['deliver_by'];
        $tempRow['payment_method'] = $row['payment_method'];
        $tempRow['address'] = $row['address'];
        $tempRow['delivery_time'] = $row['delivery_time'];
        $tempRow['active_status'] = $active_status;
        $tempRow['wallet_balance'] = $row['wallet_balance'];
        $tempRow['date_added'] = date('d-m-Y',strtotime($row['date_added']));
        $rows1[] = $tempRow;
    }
    $response_data['data'] = $rows1;
    print_r(json_encode($response_data));
}else {
    $response_data['error'] = true;
    $response_data['message'] = "Invalid Call of API!";

}

/*
---------------------------------------------------------------------------------------------------------
*/
	
if(isset($_POST['get_fund_transfers'])){
    
    /* 
    7. get_fund_transfers
        accesskey:90336
        id:82
        offset:0        // {optional}
        limit:10        // {optional}
        
        sort:id / user_id           // {optional}
        order:DESC / ASC            // {optional}
        
        search:search_value         // {optional}
        
    */
    
    $json_response=array();
    $id =  $db->escapeString(trim($_POST['id']));
    $where = '';
    $offset = ( isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset']) ) ? $db->escapeString(trim($_POST['offset'])) : 0;
    $limit = ( isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit']) ) ? $db->escapeString(trim($_POST['limit'])) : 10;
    
    $sort = ( isset($_POST['sort']) && !empty(trim($_POST['sort'])) ) ? $db->escapeString(trim($_POST['sort'])) : 'id';
    $order = ( isset($_POST['order']) && !empty(trim($_POST['order'])) ) ? $db->escapeString(trim($_POST['order'])) : 'DESC';
    if(isset($_POST['search']) && !empty($_POST['search'])){
		$search = $_POST['search'];
		$where = " Where f.`id` like '%".$search."%' OR f.`delivery_boy_id` like '%".$search."%' OR d.`name` like '%".$search."%' OR f.`message` like '%".$search."%' OR d.`mobile` like '%".$search."%' OR d.`address` like '%".$search."%' OR f.`opening_balance` like '%".$search."%' OR f.`closing_balance` like '%".$search."%' OR d.`balance` like '%".$search."%' OR f.`date_created` like '%".$search."%'" ;
	}
	
    if(empty($where)){
		$where .= " WHERE delivery_boy_id = ".$id;
	}else{
		$where .= " AND delivery_boy_id = ".$id;
	}
	
	$sql = "SELECT COUNT(*) as total FROM `fund_transfers` f JOIN `delivery_boys` d ON f.delivery_boy_id=d.id".$where;
	$db->sql($sql);
	$res = $db->getResult();
	foreach($res as $row)
		$total = $row['total'];
	
	$sql = "SELECT f.*,d.name,d.mobile,d.address FROM `fund_transfers` f JOIN `delivery_boys` d ON f.delivery_boy_id=d.id ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
	$db->sql($sql);
	$res = $db->getResult();
	
	$json_response['total'] = $total;
	$rows = array();
	$tempRow = array();
	foreach($res as $row){
		$tempRow['id'] = $row['id'];
		$tempRow['name'] = $row['name'];
		$tempRow['mobile'] = $row['mobile'];
		$tempRow['address'] = $row['address'];
		$tempRow['delivery_boy_id'] = $row['delivery_boy_id'];
		$tempRow['opening_balance'] = $row['opening_balance'];
		$tempRow['closing_balance'] = $row['closing_balance'];
		$tempRow['status'] = $row['status'];
		$tempRow['message'] = $row['message'];
		$tempRow['date_created'] = $row['date_created'];
		
		$rows = $tempRow;
	}
	$json_response['data'] = $rows;
	print_r(json_encode($json_response));

}else {
	$json_response['error'] = true;
	$json_response['message'] = "Invalid Call of API!";
}

/* 
---------------------------------------------------------------------------------------------------------
*/
	
if(isset($_POST['update_delivery_boy_profile']) && isset($_POST['id'])){
    
    /* 
    2.update_delivery_boy_profile
        accesskey:90336
        id:82
        update_name:
        update_address:Jl Komplek Polri
        old_password:
        update_password:
        confirm_password:
    */
    $json_response=array();
    $id =  $db->escapeString(trim($_POST['id']));
    if(isset($_POST['old_password']) && $_POST['old_password'] != ''){
        $old_password = md5($_POST['old_password']);
        $sql = "SELECT `password` FROM delivery_boys WHERE id=".$id;
        $db->sql($sql);
        $res = $db->getResult();
        if($res['password'] != $old_password){
            $json_response['error'] = true;
	        $json_response['message'] = "Old password does't match.";
	        return false;
	        exit();
        }
    }
    if($_POST['update_password'] !='' && $_POST['old_password'] == ''){
        $json_response['error'] = true;
        $json_response['message'] = "Please enter old password.";
        return false;
        exit();
    }
    $name = $db->escapeString($_POST['update_name']);  
    $address = $db->escapeString($_POST['update_address']);
    $update_password = $db->escapeString($_POST['update_password']);
    $confirm_password = md5($_POST['confirm_password']);
    if($update_password == $confirm_password){
        $sql = "Update delivery_boys set `name`='".$name."',password='".$update_password."',`address`='".$address."' where `id`=".$id;
    }else{
        $sql = "Update delivery_boys set `name`='".$name."',`address`='".$address."' where `id`=".$id;
    }
    if($db->sql($sql)){
        $json_response['error'] = false;
        $json_response['message'] = "Information Updated Successfully.";
    }else{
        $json_response['error'] = true;
        $json_response['message'] = "Some Error Occurred! Please Try Again.";
    }

}else {
	$json_response['error'] = true;
	$json_response['message'] = "Invalid Call of API!";

}



?>