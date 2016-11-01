<?php 

$url = 'http://www.dashboard.monogramathome.com/backoffice/magento_api/login';

$data = array(
    'username' => 'dhruvipatel',
    'password' => 'this.admin'
);

$handle = curl_init($url);

curl_setopt($handle, CURLOPT_POST, true);

curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($data));

curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($handle);

curl_close($handle);

$session_arr = json_decode($response);
$sess_array = array(
    'user_id' => $session_arr->data->inf_logged_in->user_id,
    'user_name' => $session_arr->data->inf_logged_in->user_name,
    'user_type' => $session_arr->data->inf_logged_in->user_type,
    'admin_user_name' => $session_arr->data->inf_logged_in->admin_user_name,
    'admin_user_id' => $session_arr->data->inf_logged_in->admin_user_id,
    'table_prefix' => $session_arr->data->inf_logged_in->table_prefix,
    'mlm_plan' => $session_arr->data->inf_logged_in->mlm_plan,
    'is_logged_in' => true
);

ini_set('session.cookie_domain','.monogramathome.com');
session_start();

$_SESSION['inf_logged_in'] = $sess_array;
print_r($_SESSION); 
?>
