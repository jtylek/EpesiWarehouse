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
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items_OrdersInstall extends ModuleInstall {
    const version = '1.5.6';

	public function install() {
		Base_LangCommon::install_translations($this->get_type());
		
		Base_ThemeCommon::install_default_theme($this->get_type());
		$fields = array(
			array('name' => _M('Transaction ID'), 'type'=>'calculated', 'required'=>false, 'param'=>Utils_RecordBrowserCommon::actual_db_type('text',16), 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_transaction_id')),
			array('name' => _M('Transaction Type'),'type'=>'commondata', 'required'=>true, 'extra'=>false, 'visible'=>true, 'filter'=>true, 'param'=>array('order_by_key'=>true,'Premium_Items_Orders_Trans_Types'), 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_transaction_type_order')),
			array('name' => _M('Warehouse'), 		'type'=>'select', 'required'=>false, 'extra'=>false, 'filter'=>true, 'visible'=>true, 'param'=>'premium_warehouse::Warehouse;::', 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_warehouse')),
			array('name' => _M('Target Warehouse'),'type'=>'select', 'required'=>false, 'extra'=>false, 'visible'=>false, 'param'=>'premium_warehouse::Warehouse;::', 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_warehouse')),
			array('name' => _M('Ref No'), 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>false, 'visible'=>true),
            array('name' => _M('Split Transaction'),'type' => 'select','extra' => false,'visible' => false,'param' => "premium_warehouse_items_orders::Transaction ID",'QFfield_callback' => array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_split_transaction')),
            array('name' => _M('Employee'), 		'type'=>'crm_contact', 'filter'=>true, 'param'=>array('field_type'=>'select','crits'=>array('Premium_Warehouse_ItemsCommon','employee_crits'), 'format'=>array('CRM_ContactsCommon','contact_format_no_company')), 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Transaction Date'),'type'=>'date', 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name' => _M('Return Date'),	'type'=>'date', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Expiration Date'),'type'=>'date', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Payment'), 		'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Payment Type'), 	'type'=>'commondata', 'param'=>array('order_by_key'=>true,'Premium_Items_Orders_Payment_Types'), 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Payment No'), 	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Shipment Type'), 	'type'=>'commondata', 'param'=>array('order_by_key'=>true,'Premium_Items_Orders_Shipment_Types'), 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Shipment No'),	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_shipment_no')),
			array('name' => _M('Shipment Date'),	'type'=>'date', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Shipment Employee'),'type'=>'crm_contact', 'param'=>array('field_type'=>'select','crits'=>array('Premium_Warehouse_ItemsCommon','employee_crits'), 'format'=>array('CRM_ContactsCommon','contact_format_no_company')), 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Shipment ETA'),	'type'=>'date', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Shipment Cost'),	'type'=>'currency', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Handling Cost'),	'type'=>'currency', 'required'=>false, 'extra'=>false, 'visible'=>false),
			array('name' => _M('Weight'), 		'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_weight')),
			array('name' => _M('Volume'),	 		'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_volume')),
			array('name' => _M('Total Value'),	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_total_value'), 'style'=>'currency'),
			array('name' => _M('Tax Value'),		'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_tax_value'), 'style'=>'currency'),
			array('name' => _M('Status'),			'type'=>'text', 'extra'=>false, 'param'=>'8', 'visible'=>true, 'filter'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_status'),'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_status')),
			array('name' => _M('Related'),		'type'=>'select', 'required'=>false, 'param'=>'premium_warehouse_items_orders::Transaction ID;Premium_Warehouse_Items_OrdersCommon::related_transactions_crits', 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_related_transaction')),

			array('name' => _M('Memo'),			'type'=>'long text', 'required'=>false, 'param'=>'255', 'extra'=>false),

			array('name' => _M('Contact Details'),'type'=>'page_split', 'required'=>true),

			array('name' => _M('Receipt'),	 	'type'=>'checkbox', 'param'=>'', 'required'=>false, 'extra'=>true, 'visible'=>false, 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_receipt')),
			array('name' => _M('Company'), 		'type'=>'crm_company', 'param'=>array('field_type'=>'select','crits'=>array('Premium_Warehouse_Items_OrdersCommon','company_crits')), 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name' => _M('Contact'), 		'type'=>'crm_contact', 'param'=>array('field_type'=>'select', 'format'=>array('CRM_ContactsCommon','contact_format_no_company')), 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name' => _M('Company Name'), 	'type'=>'text', 'param'=>'128', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_company_name'), 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon','QFfield_company_name')),
			array('name' => _M('Last Name'), 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_first_name')),
			array('name' => _M('First Name'), 	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_last_name')),
			array('name' => _M('Address 1'), 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('CRM_ContactsCommon','maplink')),
			array('name' => _M('Address 2'), 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('CRM_ContactsCommon','maplink')),
			array('name' => _M('City'),	 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('CRM_ContactsCommon','maplink')),
			array('name' => _M('Country'),		'type'=>'commondata', 'required'=>false, 'param'=>array('Countries'), 'extra'=>true, 'QFfield_callback'=>array('Data_CountriesCommon', 'QFfield_country')),
			array('name' => _M('Zone'),			'type'=>'commondata', 'required'=>false, 'param'=>array('Countries','Country'), 'extra'=>true, 'visible'=>false, 'QFfield_callback'=>array('Data_CountriesCommon', 'QFfield_zone')),
			array('name' => _M('Postal Code'),	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name' => _M('Phone'),	 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name' => _M('Tax ID'),	 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name' => _M('Notes'),			'type'=>'calculated',	'visible'=>true,'extra'=>false,	'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_notes')),

			array('name' => _M('Shipping Address'),'type'=>'page_split', 'required'=>true),
			array('name' => _M('Shipping Company'), 		'type'=>'crm_company', 'param'=>array('field_type'=>'select','crits'=>array('Premium_Warehouse_Items_OrdersCommon','company_crits')), 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name' => _M('Shipping Contact'), 		'type'=>'crm_contact', 'param'=>array('field_type'=>'select', 'format'=>array('CRM_ContactsCommon','contact_format_no_company')), 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name' => _M('Shipping Company Name'), 	'type'=>'text', 'param'=>'128', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_company_name'), 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon','QFfield_company_name')),
			array('name' => _M('Shipping Last Name'), 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_first_name')),
			array('name' => _M('Shipping First Name'), 	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_last_name')),
			array('name' => _M('Shipping Address 1'), 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','shipping_maplink')),
			array('name' => _M('Shipping Address 2'), 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','shipping_maplink')),
			array('name' => _M('Shipping City'),	 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','shipping_maplink')),
			array('name' => _M('Shipping Country'),		'type'=>'commondata', 'required'=>false, 'param'=>array('Countries'), 'extra'=>true, 'QFfield_callback'=>array('Data_CountriesCommon', 'QFfield_country')),
			array('name' => _M('Shipping Zone'),			'type'=>'commondata', 'required'=>false, 'param'=>array('Countries','Shipping Country'), 'extra'=>true, 'visible'=>false, 'QFfield_callback'=>array('Data_CountriesCommon', 'QFfield_zone')),
			array('name' => _M('Shipping Postal Code'),	'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false),
			array('name' => _M('Shipping Phone'),	 		'type'=>'text', 'param'=>'64', 'required'=>false, 'extra'=>true, 'visible'=>false)
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_warehouse_items_orders', $fields);

//		Utils_RecordBrowserCommon::set_quickjump('premium_warehouse_items_orders', 'Item');
		Utils_RecordBrowserCommon::set_favorites('premium_warehouse_items_orders', true);
		Utils_RecordBrowserCommon::set_recent('premium_warehouse_items_orders', 15);
		Utils_RecordBrowserCommon::set_printer('premium_warehouse_items_orders', 'Premium_Warehouse_Items_Orders_Printer');
		Utils_RecordBrowserCommon::set_caption('premium_warehouse_items_orders', _M('Items Transactions'));
//		Utils_RecordBrowserCommon::set_icon('premium_warehouse_items_orders', Base_ThemeCommon::get_template_filename('Premium/Warehouse/Items/Orders', 'icon.png'));
		Utils_RecordBrowserCommon::enable_watchdog('premium_warehouse_items_orders', array('Premium_Warehouse_Items_OrdersCommon','watchdog_label'));
		Utils_RecordBrowserCommon::register_processing_callback('premium_warehouse_items_orders', array('Premium_Warehouse_Items_OrdersCommon', 'submit_order'));
        Utils_RecordBrowserCommon::new_browse_mode_details_callback('premium_warehouse_items_orders', 'Premium/Warehouse/Items/Orders', 'browse_mode_details');
		Utils_RecordBrowserCommon::set_description_callback('premium_warehouse_items_orders', array('Premium_Warehouse_Items_OrdersCommon', 'transaction_caption'));
			
		$fields = array(
			array('name' => _M('Transaction ID'), 	'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items_orders::Transaction ID;Premium_Warehouse_Items_OrdersCommon::transactions_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_transaction_id_in_details')),

			array('name' => _M('Transaction Type'), 	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_transaction_type')),
			array('name' => _M('Transaction Status'), 'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_transaction_status')),
			array('name' => _M('Transaction Date'), 	'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_transaction_date')),
			array('name' => _M('Warehouse'), 			'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_transaction_warehouse')),

			array('name' => _M('Item Name'), 			'type'=>'select', 'required'=>true, 'param'=>'premium_warehouse_items::SKU|Item Name;Premium_Warehouse_Items_OrdersCommon::items_crits', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_item_name'), 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_item_name')),

			array('name' => _M('Description'), 		'type'=>'long text', 'required'=>false, 'param'=>'255', 'extra'=>false, 'visible'=>true),
			array('name' => _M('Debit'),				'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_debit'), 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_debit')),
			array('name' => _M('Credit'),				'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_credit'), 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_credit')),
			array('name' => _M('Quantity'),			'type'=>'integer', 'required'=>true, 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_quantity')),

			array('name' => _M('Return Date'), 		'type'=>'date', 'required'=>false, 'extra'=>false, 'visible'=>false, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon','display_return_date'), 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon','QFfield_return_date')),
			array('name' => _M('Returned'), 			'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>false),

            array('name' => _M('Unit Price'), 			'type'=>'currency', 'required'=>false, 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_unit_price')),
            array('name' => _M('Markup/Discount Rate'), 			'type'=>'float', 'required'=>false, 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_discount_rate')),

			array('name' => _M('Net Price'), 			'type'=>'currency', 'required'=>false, 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_net_price')),
			array('name' => _M('Gross Price'), 		'type'=>'currency', 'required'=>false, 'extra'=>false, 'visible'=>true, 'QFfield_callback'=>array($this->get_type().'Common', 'QFfield_gross_price'), 'display_callback'=>array($this->get_type().'Common', 'display_gross_price')),
			array('name' => _M('Tax Rate'), 			'type'=>'select', 'required'=>false, 'extra'=>false, 'visible'=>true, 'param'=>'data_tax_rates::Name', 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_details_tax_rate')),
			array('name' => _M('Net Total'), 			'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_order_details_total'), 'style'=>'currency'),
			array('name' => _M('Tax Value'), 			'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_order_details_tax_value'), 'style'=>'currency'),
			array('name' => _M('Gross Total'), 		'type'=>'calculated', 'required'=>false, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_order_details_gross_price'), 'style'=>'currency'),
			array('name' => _M('Serials'),			'type'=>'page_split', 'required'=>false),
			array('name' => _M('Serial'), 			'type'=>'calculated', 'required'=>false, 'extra'=>true, 'visible'=>false, 'QFfield_callback'=>array('Premium_Warehouse_Items_OrdersCommon', 'display_serials'))
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_warehouse_items_orders_details', $fields);
		
//		Utils_RecordBrowserCommon::set_quickjump('premium_warehouse_items_orders_details', 'Item SKU');
		Utils_RecordBrowserCommon::set_favorites('premium_warehouse_items_orders_details', false);
		Utils_RecordBrowserCommon::set_recent('premium_warehouse_items_orders_details', 15);
		Utils_RecordBrowserCommon::set_caption('premium_warehouse_items_orders_details', _M('Items Order Details'));
		Utils_RecordBrowserCommon::set_icon('premium_warehouse_items_orders_details', Base_ThemeCommon::get_template_filename('Premium/Warehouse/Items/Orders', 'details_icon.png'));
//		Utils_RecordBrowserCommon::enable_watchdog('premium_warehouse_items_orders_details', array('Premium_Warehouse_Items_OrdersCommon','watchdog_label'));
		Utils_RecordBrowserCommon::register_processing_callback('premium_warehouse_items_orders_details', array('Premium_Warehouse_Items_OrdersCommon', 'submit_order_details'));
		
		DB::Execute('UPDATE premium_warehouse_items_orders_details_field SET param=%s WHERE field=%s', array('premium_warehouse_items_orders::Transaction Date/Transaction ID', 'Transaction Date'));

		DB::CreateIndex('premium_warehouse_items_ord_det__it_name__idx','premium_warehouse_items_orders_details_data_1',array('f_item_name'));

// ************ addons ************** //
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_orders', 'Premium/Warehouse/Items/Orders', 'order_details_addon', _M('Items'));
		Utils_AttachmentCommon::new_addon('premium_warehouse_items_orders');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items', 'Premium/Warehouse/Items/Orders', 'transaction_history_addon', 'Transaction History');
		Utils_RecordBrowserCommon::new_addon('contact', 'Premium/Warehouse/Items/Orders', 'contact_orders_addon', 'Premium_Warehouse_Items_OrdersCommon::contact_orders_label');
		Utils_RecordBrowserCommon::new_addon('company', 'Premium/Warehouse/Items/Orders', 'company_orders_addon', 'Premium_Warehouse_Items_OrdersCommon::company_orders_label');
		Utils_RecordBrowserCommon::new_addon('premium_warehouse_items_orders', 'Premium/Warehouse/Items/Orders', 'order_serial_addon', 'Serial Numbers');
		
		Utils_RecordBrowserCommon::set_addon_pos('premium_warehouse_items', 'Premium/Warehouse/Items/Orders', 'transaction_history_addon', 2);
		Utils_RecordBrowserCommon::set_addon_pos('premium_warehouse_items', 'Premium/Warehouse/Items/Location', 'location_addon', 1);

// ************ other ************** //
		Utils_RecordBrowserCommon::field_deny_access('premium_warehouse_items', 'Quantity on Hand', 'edit');
		Utils_RecordBrowserCommon::register_processing_callback('premium_warehouse_items', array('Premium_Warehouse_Items_OrdersCommon', 'submit_new_item_from_order'));

		Utils_CommonDataCommon::new_array('Premium_Items_Orders_Trans_Types',array(0=>_M('Purchase'),1=>_M('Sale'),2=>_M('Inventory Adjustment'),3=>_M('Rental'),4=>_M('Transfer')),true,true);
		Utils_CommonDataCommon::new_array('Premium_Items_Orders_Payment_Types',array(0=>_M('Cash'),1=>_M('Check')),true,true);
		Utils_CommonDataCommon::new_array('Premium_Items_Orders_Shipment_Types',array(0=>_M('Pickup'),1=>_M('USPS'),2=>_M('UPS'),3=>_M('DHL'),4=>_M('FedEx'),5=>_M('Courier'),6=>_M('Other')),true,true);

		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items', _M('Quantity En Route'), 'calculated', true, false, '', 'integer', false, false, 10);
		Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items', 'Quantity En Route', array('Premium_Warehouse_Items_OrdersCommon', 'display_quantity_on_route'));
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items', _M('Last Sale Price'), 'currency', false, false, '', 'currency', false, false);
		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items', _M('Last Purchase Price'), 'currency', false, false, '', 'currency', false, false);
		Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items','Last Sale Price',array('Premium_Warehouse_Items_OrdersCommon', 'display_last_price'));
		Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items','Last Purchase Price',array('Premium_Warehouse_Items_OrdersCommon', 'display_last_price'));

		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items',_M('Reserved Qty'),'calculated', true, false, '', 'integer', false, false, 11);
		Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items','Reserved Qty',array('Premium_Warehouse_Items_OrdersCommon', 'display_reserved_qty'));

		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items',_M('Available Qty'),'calculated', true, false, '', 'integer', false, false, 11);
		Utils_RecordBrowserCommon::set_display_callback('premium_warehouse_items','Available Qty',array('Premium_Warehouse_Items_OrdersCommon', 'display_available_qty'));

		DB::CreateTable('premium_warehouse_location_orders_serial',
					'serial_id I,'.
					'order_details_id I',
					array('constraints'=>''));
					
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'view', 'ACCESS:employee');
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'view', 'ALL', array('contact'=>'USER'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'view', array('ALL','ACCESS:manager'), array('company'=>'USER_COMPANY'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'add', 'ACCESS:employee', array(), array('transaction_type'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'edit', 'ACCESS:employee', array('employee'=>'USER', '(>=transaction_date'=>'-1 week', '|<status'=>20), array('transaction_type', 'warehouse'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'edit', 'ACCESS:employee', array('transaction_type'=>4, '<status'=>20), array('transaction_type', 'warehouse'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'edit', 'ACCESS:employee', array('employee'=>'USER', 'warehouse'=>''), array('transaction_type'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'edit', array('ACCESS:employee','ACCESS:manager'), array(), array('transaction_type', 'warehouse'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'delete', 'ACCESS:employee', array(':Created_by'=>'USER_ID'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders', 'delete', array('ACCESS:employee','ACCESS:manager'));

		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders_details', 'view', 'ACCESS:employee');
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders_details', 'view', 'ALL', array('transaction_id[contact]'=>'USER'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders_details', 'view', array('ALL','ACCESS:manager'), array('transaction_id[company]'=>'USER_COMPANY'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders_details', 'add', 'ACCESS:employee', array('transaction_id[employee]'=>'USER', '(>=transaction_id[transaction_date]'=>'-1 week', '|<transaction_id[status]'=>20), array('transaction_id'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders_details', 'add', array('ACCESS:employee','ACCESS:manager'), array(), array('transaction_id'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders_details', 'edit', 'ACCESS:employee', array('transaction_id[employee]'=>'USER', '(>=transaction_id[transaction_date]'=>'-1 week', '|<transaction_id[status]'=>20), array('transaction_id'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders_details', 'edit', array('ACCESS:employee','ACCESS:manager'), array(), array('transaction_id'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders_details', 'delete', 'ACCESS:employee', array(':Created_by'=>'USER_ID'));
		Utils_RecordBrowserCommon::add_access('premium_warehouse_items_orders_details', 'delete', array('ACCESS:employee','ACCESS:manager'));

		Base_AclCommon::add_permission(_M('Inventory - Sell at loss'),array('ACCESS:employee','ACCESS:manager'));

		Utils_RecordBrowserCommon::new_record_field('premium_warehouse_items_orders', array('name' => 'Tax Calculation', 'type'=>'commondata', 'param'=>array('Premium_Items_Orders_TaxCalc'), 'required'=>true, 'extra'=>true, 'filter'=>false, 'visible'=>false, 'position'=>'Related'));
		Utils_CommonDataCommon::new_array('Premium_Items_Orders_TaxCalc',array(0=>'Per Item',1=>'By Total'),true,true);

        Utils_RecordBrowserCommon::new_record_field('company', array('name' => 'Default Transactions Markup', 'type'=>'float', 'required'=>false, 'extra'=>false, 'filter'=>false, 'visible'=>false, 'position'=>'Tax ID'));
		
		// deny access to disallow edit
        Utils_RecordBrowserCommon::field_deny_access('premium_warehouse_items_orders', 'Split Transaction', 'add');
        Utils_RecordBrowserCommon::field_deny_access('premium_warehouse_items_orders', 'Split Transaction', 'edit');

        // add allow negative field
        $recordset = 'premium_warehouse_items';
        $definition = array('name' => _M('Allow negative quantity'),
                            'type' => 'checkbox',
                            'extra' => false,
                            'visible' => false,
                            'QFfield_callback' => array('Premium_Warehouse_Items_OrdersCommon', 'QFfield_negative_qty'),
                            'display_callback' => array('Premium_Warehouse_Items_OrdersCommon', 'display_negative_qty')
        );
        Utils_RecordBrowserCommon::new_record_field($recordset, $definition);
        Variable::set('premium_warehouse_negative_qty', 'all');

        return true;
	}
	
	public function uninstall() {
        Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items', 'Allow negative quantity');
		Base_AclCommon::delete_permission('Inventory - Sell at loss');
		DB::DropTable('premium_warehouse_location_orders_serial');
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_warehouse_items', array('Premium_Warehouse_Items_OrdersCommon', 'submit_new_item_from_order'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_warehouse_items_orders', array('Premium_Warehouse_Items_OrdersCommon', 'submit_order'));
		Utils_RecordBrowserCommon::unregister_processing_callback('premium_warehouse_items_orders_details', array('Premium_Warehouse_Items_OrdersCommon', 'submit_order_details'));
		
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items','Reserved Qty');
		Utils_RecordBrowserCommon::delete_record_field('premium_warehouse_items','Available Qty');
		Utils_RecordBrowserCommon::delete_record_field('company', 'Default Transactions Markup');

		Base_ThemeCommon::uninstall_default_theme($this->get_type());
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items_orders', 'Premium/Warehouse/Items/Orders', 'order_details_addon');
		Utils_AttachmentCommon::delete_addon('premium_warehouse_items_orders');
		Utils_RecordBrowserCommon::delete_addon('premium_warehouse_items', 'Premium/Warehouse/Items/Orders', 'transaction_history_addon');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_warehouse_items_orders');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_warehouse_items_orders_details');

		Utils_CommonDataCommon::remove('Premium_Items_Orders_Trans_Types');
		Utils_CommonDataCommon::remove('Premium_Items_Orders_Payment_Types');
		Utils_CommonDataCommon::remove('Premium_Items_Orders_Shipment_Types');
		return true;
	}
	
	public function version() {
		return array(self::version);
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Base/Lang','version'=>0),
			array('name'=>'Premium/Warehouse/Items/Location','version'=>0),
			array('name'=>'Utils/RecordBrowser', 'version'=>0),
			array('name'=>'Utils/LeightboxPrompt', 'version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'Items Orders - Premium Module',
			'Author'=>'abisaga@telaxus.com',
			'License'=>'Commercial');
	}
	
	public static function simple_setup() {
        return array('package'=>__('Inventory Management'));
	}
}

?>
