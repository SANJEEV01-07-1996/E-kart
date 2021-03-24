<?php 

require_once '../includes/functions.php';
include_once('../includes/variables.php');
include_once('verify-token.php');

	
	$response = array(); 
	/* accesskey:90336
		user_id:5 
		token:227 */
	$accesskey=$_POST['accesskey'];

	if($access_key != $accesskey){
		$response['error']= true;
		$response['message']="invalid accesskey";
		print_r(json_encode($response));
		return false;
	}
	if(!verify_token()){
        return false;
    }
	
	if(isset($_POST['token']) && isset($_POST['user_id'])){

		$token = $_POST['token'];
		$user_id = $_POST['user_id'];

		$fn = new functions; 

		$result = $fn->registerDevice($user_id,$token);

		if($result == 0){
			$response['error'] = false; 
			$response['message'] = 'Device registered successfully';
		}elseif($result == 2){
			$response['error'] = true; 
			$response['message'] = 'Device already registered';
		}else{
			$response['error'] = true;
			$response['message']='Device not registered';
		}
	}else{
		$response['error']=true;
		$response['message']='Invalid Request...';
	}

	echo json_encode($response);
?>