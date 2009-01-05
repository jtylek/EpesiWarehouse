<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * Warehouse - Items Orders
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-items-orders
 */
if(!isset($_POST['rec_id']) || !isset($_POST['trans']) || !isset($_POST['cid']))
	die('alert(\'Invalid request\')');

define('JS_OUTPUT',1);
define('CID',$_POST['cid']); 
require_once('../../../../../include.php');
ModuleManager::load_modules();

$id = trim($_POST['rec_id'], '"');
$trans_id = trim($_POST['trans'], '"');
if (!is_numeric($id)) die(json_encode(''));
if (!is_numeric($trans_id)) die(json_encode(''));
$rec = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$id);
$trans = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders',$trans_id);

$location_id = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$id,'warehouse'=>$trans['warehouse'],'!quantity'=>0));
$location_id = array_shift($location_id);
if (!isset($location_id) || !$location_id)
	$location_id = null;

$js = '';
$js .= '$("description").value="'.$rec['description'].'";';
$js .= 'if($("description"))focus_by_id("description");';
if ($trans['transaction_type']<2) {
	$js .= 'if($("tax_rate"))$("tax_rate").value="'.$rec['tax_rate'].'";';
	$js .= 'if($("net_price"))$("net_price").value="'.($trans['transaction_type']==0?(isset($rec['last_purchase_price'])&&$rec['last_purchase_price']?$rec['last_purchase_price']:$rec['cost']):(isset($rec['last_sale_price'])&&$rec['last_sale_price']?$rec['last_sale_price']:$rec['net_price'])).'";';
//	if ($rec['item_type']==1) {
//		$js .= '$("quantity").value=1;';
//		if ($trans['transaction_type']==1) {
//			$js .= '$("quantity").style.display="none";';
//			$js .= 'if($("serial"))$("serial").style.display="inline";';
//			$js .= 'var new_opts={';
//			$locs = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$id, '!quantity'=>0, 'warehouse'=>$trans['warehouse'], 'rental_item'=>array('',0)), array(), array('serial'=>'ASC'));
//			$first = true;
//			foreach ($locs as $k=>$v) {
//				if (!$first) $js .= ',';
//				$first = false;
//				$js .= '"'.$v['id'].'":"'.Premium_Warehouse_Items_LocationCommon::mark_used($v['used']).$v['serial'].'"';
//			}
//			$js .= '};';
//			$js .= 'var obj=$("serial");';
//			$js .= 'var opts=obj.options;';
//			$js .= 'opts.length=0;';
//			$js .= 'for(y in new_opts) {';
//			$js .= 'opts[opts.length] = new Option(new_opts[y],y);';
//			$js .= '}';
//		}
//	} else 
	{
		$js .= 'if($("quantity"))$("quantity").style.display="inline";';
		$js .= 'if(!$("quantity").value)$("quantity").value=1;';
	}
}
if ($trans['transaction_type']==2) {
//	$js .= '$("quantity").value=1;';
	$js .= '$("'.Utils_RecordBrowserCommon::get_calcualted_id('premium_warehouse_items_orders_details', 'credit', null).'").innerHTML="'.Epesi::escapeJS('<input type="text" name="order_details_credit" id="order_details_credit" value="" onkeyup="if(this.value)$(\'order_details_debit\').style.display=\'none\';else $(\'order_details_debit\').style.display=\'inline\';" />').'";';
	$js .= '$("'.Utils_RecordBrowserCommon::get_calcualted_id('premium_warehouse_items_orders_details', 'debit', null).'").innerHTML="'.Epesi::escapeJS('<input type="text" name="order_details_debit" id="order_details_debit" value="" onkeyup="if(this.value)$(\'order_details_credit\').style.display=\'none\';else $(\'order_details_credit\').style.display=\'inline\';" />').'";';
	if ($location_id!==null) $js .= '$("order_details_debit").style.display="inline";';
	else $js .= '$("order_details_debit").style.display="none";';
}
if ($trans['transaction_type']==3) {
	$js .= 'var new_opts={';
	$locs = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$id, '!quantity'=>0, 'warehouse'=>$trans['warehouse']), array(), array('serial'=>'ASC'));
	$first = true;
	foreach ($locs as $k=>$v) {
		if (!$first) $js .= ',';
		$first = false;
		$js .= '"'.$v['id'].'":"'.Premium_Warehouse_Items_LocationCommon::mark_used($v['used']).$v['serial'].'"';
	}
	$js .= '};';
	$js .= 'var obj=$("serial");';
	$js .= 'var opts=obj.options;';
	$js .= 'opts.length=0;';
	$js .= 'for(y in new_opts) {';
	$js .= 'opts[opts.length] = new Option(new_opts[y],y);';
	$js .= '}';
}

print($js);
?>