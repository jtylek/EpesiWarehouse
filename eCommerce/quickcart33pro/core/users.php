<?php
function login( $v ){
	global $config;

	$uid = DB::GetRow('SELECT e.id, c.id as cid, c.f_company_name FROM premium_ecommerce_users_data_1 e INNER JOIN contact_data_1 c ON c.id=e.f_contact WHERE e.f_password=%s AND c.f_email=%s AND e.active=1 AND c.active=1',array(md5($v['sPassword']),$v['sEmail']));
	if(!$uid) return false;

	$oPage =& Pages::getInstance( );
      	
      	$_SESSION['user'] = $uid['id'];
      	$_SESSION['contact'] = $uid['cid'];
      	$company = explode('__',trim($uid['f_company_name'],'__'));
      	$_SESSION['company'] = array_shift($company);

        if( $_SESSION['iOrderQuantity'.LANGUAGE] && isset( $config['order_page'] ) && isset( $oPage->aPages[$config['order_page']] ) ){
          header( 'Location: '.REDIRECT.$oPage->aPages[$config['order_page']]['sLinkName'] );
        } else {
          header( 'Location: '.REDIRECT);
        }
        exit;
} 

function change_password($v) {
	global $config;

	if($v['sPassword']!=$v['sPassword2'])
		return false;
	
	$ok = DB::GetOne('SELECT 1 FROM premium_ecommerce_users_data_1 WHERE f_password=%s AND id=%d',array(md5($v['sOldPassword']),$_SESSION['user']));
	if(!$ok) return false;
	
	DB::Execute('UPDATE premium_ecommerce_users_data_1 SET f_password=%s WHERE id=%d',array(md5($v['sPassword']),$_SESSION['user']));

	$oPage =& Pages::getInstance( );	
	header( 'Location: '.REDIRECT.$oPage->aPages[43]['sLinkName'] );
        exit;
}

function logout( ){
         unset($_SESSION['user']);
         unset($_SESSION['contact']);
         unset($_SESSION['company']);
         header( 'Location: '.REDIRECT);
         exit;
} 

function logged() {
	return isset($_SESSION['contact']);
}

$aUser = array();
if(logged()) {
	$aUser = DB::GetRow('SELECT f_email as sEmail, f_last_name as sLastName, f_first_name as sFirstName, f_address_1 as sStreet, f_postal_code as sZipCode, f_city as sCity, f_country as sCountry, f_work_phone as sPhone, f_company_name FROM contact_data_1 WHERE id=%d',array($_SESSION['contact']));
	if(isset($_SESSION['company'])) {
		$aUser += DB::GetRow('SELECT f_company_name as sCompanyName, f_tax_id as sNip FROM company_data_1 WHERE id=%d',array($_SESSION['company']));
	}
}
?>