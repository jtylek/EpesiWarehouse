<?php
/**
 * Warehouse
 * @author abisaga@telaxus.com
 * @copyright abisaga@telaxus.com
 * @license SPL
 * @version 0.3
 * @package premium-warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_WarehouseCommon extends ModuleCommon {
    public static function get_warehouse($id) {
		return Utils_RecordBrowserCommon::get_record('premium_warehouse', $id);
    }

	public static function get_warehouses($crits=array(),$cols=array()) {
    		return Utils_RecordBrowserCommon::get_records('premium_warehouse', $crits, $cols);
	}

    public static function display_warehouse($v, $nolink=false) {
		return Utils_RecordBrowserCommon::create_linked_label_r('premium_warehouse', 'Warehouse', $v, $nolink);
	}
	
	public static function user_settings(){
		$rec = Utils_RecordBrowserCommon::get_records('premium_warehouse', array(), array('warehouse'), array('warehouse'=>'ASC'));
		$warehouses = array(''=>'---');
		foreach ($rec as $v)
			$warehouses[$v['id']] = $v['warehouse'];
		return array('Warehouse'=>array(
			array('name'=>'my_warehouse','label'=>'My main Warehouse','type'=>'select','values'=>$warehouses,'default'=>'')
			));
	}
	
	public static function access_warehouse($action, $param){
		$i = self::Instance();
		switch ($action) {
			case 'add':
			case 'browse':	return $i->acl_check('browse warehouses');
			case 'view':	if($i->acl_check('view warehouses')) return true;
							return false;
			case 'edit':	return $i->acl_check('edit warehouses');
			case 'delete':	return $i->acl_check('delete warehouses');
		}
		return false;
    }

    public static function menu() {
		return array('Warehouse'=>array('__submenu__'=>1,'Warehouses'=>array()));
	}

	public static function watchdog_label($rid = null, $events = array(), $details = true) {
		return Utils_RecordBrowserCommon::watchdog_label(
				'premium_warehouse',
				Base_LangCommon::ts('Premium_Warehouse','Warehouse'),
				$rid,
				$events,
				'warehouse',
				$details
			);
	}
	
}
?>
