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
if(!isset($_POST['item_name']) || !isset($_GET['cid']) || !is_numeric($_GET['cid']))
	die('alert(\'Invalid request\')');

define('CID',$_GET['cid']); 
require_once('../../../../../include.php');
ModuleManager::load_modules();

$qry = array();
$vals = array();
$words = explode(' ', $_POST['item_name']);
foreach ($words as $w) {
	$qry[] = 'f_item_name LIKE '.DB::Concat(DB::qstr('%'), '%s', DB::qstr('%'));
	$vals[] = $w;
}
$ret = DB::SelectLimit('SELECT f_item_name FROM premium_warehouse_items_data_1 WHERE '.implode(' AND ',$qry), 10, 0, $vals);
print('<ul>');
while ($row = $ret->FetchRow()) {
	print('<li>'.$row['f_item_name'].'</li>');
}
print('</ul>');
return;
?>