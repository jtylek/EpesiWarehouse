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

class Premium_Warehouse_eCommerce_CurrencyUpdatePricesInstall extends ModuleInstall {

	public function install() {
		Utils_RecordBrowserCommon::new_record_field('premium_ecommerce_prices',array('name' => _M('Auto update'),	'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true));
        Variable::set('ecommerce_price_updater', null);
        Variable::set('ecommerce_price_updater_last_upd', null);
        Variable::set('ecommerce_price_updater_rates', null);
		return true;
	}
	
	public function uninstall() {
		Utils_RecordBrowserCommon::delete_record_field('premium_ecommerce_prices','Auto update');
	    Variable::delete('ecommerce_price_updater');
	    Variable::delete('ecommerce_price_updater_last_upd');
	    Variable::delete('ecommerce_price_updater_rates');
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