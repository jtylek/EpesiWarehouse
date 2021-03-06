<?php
/**
 * 
 * @author shacky@poczta.fm
 * @copyright Telaxus LLC
 * @license MIT
 * @version 0.1
 * @package epesi-Premium/Warehouse/eCommerce
 * @subpackage CurrencyUpdatePrices
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_eCommerce_CompareUpdatePricesInstall extends ModuleInstall {

	public function install() {
		Utils_CommonDataCommon::new_array('Premium/Warehouse/eCommerce/CompareServices',array('ceneo'=>_M('Ceneo'),'skapiec'=>_M('Skąpiec')),true,true);
		
		//product prices
		$fields = array(
			array('name' => _M('Plugin'), 	'type'=>'commondata', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>array('Premium/Warehouse/eCommerce/CompareServices')),
			array('name' => _M('Item Name'), 	'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items::Item Name;Premium_Warehouse_Items_OrdersCommon::products_crits', 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_eCommerceCommon', 'display_item_name')),
			array('name' => _M('URL'), 	'type'=>'text', 'required'=>true, 'param'=>256, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Currency'), 	'type'=>'integer', 'required'=>false, 'extra'=>false,'visible'=>true, 'display_callback'=>array('Premium_Warehouse_eCommerceCommon', 'display_currency'),'QFfield_callback'=>array('Premium_Warehouse_eCommerceCommon', 'QFfield_currency')),
			array('name' => _M('Gross Price'),'type'=>'float', 'required'=>false, 'extra'=>false,'visible'=>true),
			array('name' => _M('Tax Rate'), 	'type'=>'select', 'required'=>true, 'extra'=>false, 'visible'=>true, 'param'=>'data_tax_rates::Name', 'style'=>'integer'),
			array('name' => _M('Position'), 	'type'=>'integer', 'required'=>true, 'extra'=>false, 'visible'=>true, 'style'=>'integer'),
			array('name' => _M('Auto update'),	'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true)
		);
		Utils_RecordBrowserCommon::install_new_recordset('premium_ecommerce_compare_prices', $fields);
		DB::CreateIndex('ecommerce_compare_prices_name_currency__idx','premium_ecommerce_compare_prices_data_1',array('f_item_name','f_currency','active'));

		Utils_RecordBrowserCommon::set_favorites('premium_ecommerce_compare_prices', false);
		Utils_RecordBrowserCommon::set_caption('premium_ecommerce_compare_prices', _M('eCommerce - compare prices'));

		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/eCommerce/CompareUpdatePrices', 'item_addon', 'Premium_Warehouse_eCommerce_CompareUpdatePricesCommon::item_addon_parameters');
		Utils_RecordBrowserCommon::new_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce/CompareUpdatePrices', 'product_addon', _M('Compare Services'));
		
		Utils_RecordBrowserCommon::add_access('premium_ecommerce_compare_prices', 'view', 'ACCESS:employee');
		Utils_RecordBrowserCommon::add_access('premium_ecommerce_compare_prices', 'add', 'ACCESS:employee');
		Utils_RecordBrowserCommon::add_access('premium_ecommerce_compare_prices', 'edit', 'ACCESS:employee');
		Utils_RecordBrowserCommon::add_access('premium_ecommerce_compare_prices', 'delete', array('ACCESS:employee', 'ACCESS:manager'));
		
		$this->create_data_dir();
		
		Variable::set('premium_ecommerce_compare_servic',0);
		
		return true;
	}
	
	public function uninstall() {
		Variable::delete('premium_ecommerce_compare_servic');
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items', 'Premium/Warehouse/eCommerce/CompareUpdatePrices', 'item_addon');
		Utils_RecordBrowserCommon::delete_addon('premium_ecommerce_products', 'Premium/Warehouse/eCommerce/CompareUpdatePrices', 'product_addon');
		
		Utils_RecordBrowserCommon::uninstall_recordset('premium_ecommerce_compare_prices');
		return true;
	}
	
	public function version() {
		return array("0.1");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Premium/Warehouse/eCommerce','version'=>0),
			array('name'=>'Data/TaxRates','version'=>0),
			array('name'=>'Utils/CurrencyField','version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'',
			'Author'=>'shacky@poczta.fm',
			'License'=>'MIT');
	}
	
	public static function simple_setup() {
        return array('package'=>__('old eCommerce'));
	}
	
}

?>