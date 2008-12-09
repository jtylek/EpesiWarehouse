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

class Premium_Warehouse_Items_Orders extends Module {
	private $rb;
	private $lang;
	private $href = '';

	public function construct() {
		$this->lang = $this->init_module('Base/Lang');
	}
	
	public function body() {
		$lang = $this->init_module('Base/Lang');
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders','premium_warehouse_items_orders');
		$this->rb->set_default_order(array('transaction_date'=>'DESC'));
		$this->rb->set_button(false);
		$me = CRM_ContactsCommon::get_my_record();
		$defaults = array(	'country'=>Base_User_SettingsCommon::get('Base_RegionalSettings','default_country'),
							'zone'=>Base_User_SettingsCommon::get('Base_RegionalSettings','default_state'),
							'transaction_date'=>date('Y-m-d'),
							'employee'=>$me['id'],
							'warehouse'=>Base_User_SettingsCommon::get('Premium_Warehouse','my_warehouse'),
							'terms'=>0);
		$this->rb->set_defaults(array(
			$lang->t('Purchase')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'purchase.png'), 'defaults'=>array_merge($defaults,array('transaction_type'=>0))),
			$lang->t('Sale')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'sale.png'), 'defaults'=>array_merge($defaults,array('transaction_type'=>1))),
			$lang->t('Inv. Adjustment')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'inv_adj.png'), 'defaults'=>array_merge($defaults,array('transaction_type'=>2))),
			$lang->t('Rental')=>array('icon'=>Base_ThemeCommon::get_template_file($this->get_type(),'rental.png'), 'defaults'=>array_merge($defaults,array('transaction_type'=>3)))
			), true);
		$this->rb->set_header_properties(array('terms'=>array('width'=>1, 'wrapmode'=>'nowrap')));
		$this->display_module($this->rb);
	}

	public function applet($conf,$opts) {
		$opts['go'] = true; // enable full screen
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders','premium_warehouse_items_orders');
		$limit = null;
		$crits = array();
		$sorting = array('item_name'=>'ASC');
		$cols = array(
							array('field'=>'item', 'width'=>10, 'cut'=>18),
							array('field'=>'operation', 'width'=>10),
							array('field'=>'quantity', 'width'=>10)
										);

		$conds = array(
									$cols,
									$crits,
									$sorting,
									array('Premium_Warehouse_Items_OrdersCommon','applet_info_format'),
									$limit,
									$conf,
									& $opts
				);
		$opts['actions'][] = Utils_RecordBrowserCommon::applet_new_record_button('premium_warehouse_items_orders',array());
		$this->display_module($rb, $conds, 'mini_view');
	}
	
	public function transaction_history_addon($arg){
		// TODO: service?
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders_details');
		$order = array(array('item_name'=>$arg['id']), array('quantity_on_hand'=>false,'item_name'=>false,'item_name'=>false, ($arg['item_type']==1)?'quantity':'serial'=>false), array('transaction_id'=>'DESC'));
		$rb->set_button(false);
		$rb->set_defaults(array('item_name'=>$arg['id']));
		$this->display_module($rb,$order,'show_data');
	}

	public function order_details_addon($arg){
		// TODO: leightbox do wybierania przedmiotow do select'a (sic! ^^)
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders_details');
		$cols = array('transaction_id'=>false);
		$cols['transaction_type'] = false;			
		$cols['transaction_date'] = false;			
		$cols['warehouse'] = false;			
		$header_prop = array(
			'item_name'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'gross_total'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'tax_value'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'tax_rate'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'net_total'=>array('width'=>1, 'wrapmode'=>'nowrap'),
			'net_price'=>array('width'=>20, 'wrapmode'=>'nowrap'),
			'debit'=>array('width'=>20, 'wrapmode'=>'nowrap'),
			'credit'=>array('width'=>20, 'wrapmode'=>'nowrap'),
			'quantity'=>array('width'=>20, 'wrapmode'=>'nowrap'),
			'serial'=>array('width'=>40, 'wrapmode'=>'nowrap')
		);
		if ($arg['transaction_type']==0) {
			$header_prop['net_price'] = array('name'=>'Net Cost', 'width'=>14, 'wrapmode'=>'nowrap');
			$header_prop['gross_price'] = array('name'=>'Gross Cost', 'width'=>1, 'wrapmode'=>'nowrap');
			if ($arg['status']<3) {
				$cols['tax_rate'] = false;
				$cols['net_total'] = false;
				$cols['net_price'] = false;			
				$cols['tax_value'] = false;			
				$cols['gross_total'] = false;			
				$cols['serial'] = false;
			}			
		}
		if ($arg['transaction_type']==2) {
			$cols['tax_rate'] = false;
			$cols['net_total'] = false;
			$cols['net_price'] = false;			
			$cols['tax_value'] = false;			
			$cols['gross_total'] = false;			
			$cols['debit'] = true;			
			$cols['credit'] = true;			
		}
		if ($arg['transaction_type']==3) {
			if (!$arg['payment']) {
				$cols['tax_rate'] = false;
				$cols['net_total'] = false;
				$cols['net_price'] = false;			
				$cols['tax_value'] = false;			
				$cols['gross_total'] = false;
			}			
			$cols['quantity'] = false;
			$cols['debit'] = false;
			$cols['credit'] = false;
			$cols['return_date'] = true;
			$rb->set_defaults(array('return_date'=>$arg['return_date']));
			$rb->set_additional_actions_method($this, 'actions_for_order_details');
		}
		$order = array(array('transaction_id'=>$arg['id']), $cols, array());
		$rb->set_button(false);
		$rb->set_defaults(array('transaction_id'=>$arg['id']));
		$rb->enable_quick_new_records();
		$rb->set_cut_lengths(array('description'=>50));
		$rb->set_header_properties($header_prop);
		$this->display_module($rb,$order,'show_data');
	}
	
	public function mark_as_returned($r) {
		$order = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders', $r['transaction_id']);
		Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders_details', $r['id'], array('returned'=>1));
		Utils_RecordBrowserCommon::restore_record('premium_warehouse_location', $r['serial']);
		return false;
	}
	
	public function actions_for_order_details($r, & $gb_row) {
		if (!$r['returned']) $gb_row->add_action($this->create_callback_href(array($this,'mark_as_returned'),array($r)),'Restore', 'Mark as returned');
	}

	public function attachment_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('Premium/Warehouse/Items/Orders/'.$arg['id']));
		$a->set_view_func(array('Premium_Warehouse_Items_OrdersCommon','search_format'),array($arg['id']));
		$a->additional_header('Transaction ID: '.$arg['transaction_id']);
		$a->allow_protected($this->acl_check('view protected notes'),$this->acl_check('edit protected notes'));
		$a->allow_public($this->acl_check('view public notes'),$this->acl_check('edit public notes'));
		$this->display_module($a);
	}
	
	public function change_status_leightbox($trans, $status) {
		$lp = $this->init_module('Utils/LeightboxPrompt');
		if ($trans['transaction_type']==0) {
			switch ($status) {			
				case '':
					$po_form = $this->init_module('Libs/QuickForm');
					$po_form->addElement('select', 'payment_type', $this->lang->t('Payment Type'), array(''=>'---')+Utils_CommonDataCommon::get_array('Premium_Items_Orders_Payment_Types'));
					$po_form->addElement('text', 'payment_no', $this->lang->t('Payment No'));
					$po_form->addElement('select', 'terms', $this->lang->t('Terms'), array(''=>'---')+Utils_CommonDataCommon::get_array('Premium_Items_Orders_Terms'));
					$po_form->addElement('select', 'shipment_type', $this->lang->t('Shipment Type'), array(''=>'---')+Utils_CommonDataCommon::get_array('Premium_Items_Orders_Shipment_Types'));
					$lp->add_option('po', $this->lang->t('PO'), null, $po_form);

					$quote_form = $this->init_module('Libs/QuickForm');
					$quote_form->addElement('datepicker', 'expiry_date', $this->lang->t('Expiry Date'));
					$quote_form->setDefaults(array('expiry_date'=>date('Y-m-d', strtotime('+7 days'))));
					$lp->add_option('quote', $this->lang->t('Quote'), null, $quote_form);
					
					$this->display_module($lp, array($this->lang->t('Ready to process?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						$vals['form']['status'] = ($vals['option']=='quote')?1:2; 
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 1:
					$po_form = $this->init_module('Libs/QuickForm');
					$po_form->addElement('select', 'payment_type', $this->lang->t('Payment Type'), array(''=>'---')+Utils_CommonDataCommon::get_array('Premium_Items_Orders_Payment_Types'));
					$po_form->addElement('text', 'payment_no', $this->lang->t('Payment No'));
					$po_form->addElement('select', 'terms', $this->lang->t('Terms'), array(''=>'---')+Utils_CommonDataCommon::get_array('Premium_Items_Orders_Terms'));
					$po_form->addElement('select', 'shipment_type', $this->lang->t('Shipment Type'), array(''=>'---')+Utils_CommonDataCommon::get_array('Premium_Items_Orders_Shipment_Types'));
					$lp->add_option('po', $this->lang->t('PO'), null, $po_form);

					$lp->add_option('cancel', $this->lang->t('Cancel'), null, null);
					
					$this->display_module($lp, array($this->lang->t('Ready to process?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						$vals['form']['status'] = ($vals['option']=='po')?2:21; 
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 2:
					$item_prices = $this->init_module('Libs/QuickForm');
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					$taxes = Utils_CommonDataCommon::get_array('Premium_Warehouse_Items_Tax');
					foreach ($items as $v) {
						$elements = array();
						$elements[] = $item_prices->createElement('text', 'net_price', 'Price');
						$elements[] = $item_prices->createElement('select', 'tax_rate', 'Tax', $taxes);
						if (Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $v['item_name'], 'item_type')==1) $elements[] = $item_prices->createElement('text', 'serial', 'Serial');
						$item_prices->addGroup($elements, 'item__'.$v['id'], Premium_Warehouse_Items_OrdersCommon::display_item_name($v, true));
					}
					$lp->add_option('ship', $this->lang->t('Accepted'), null, $item_prices);
					$lp->add_option('onhold', $this->lang->t('On Hold'), null, null);
					$this->display_module($lp, array($this->lang->t('Purchase Order accepted?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						if (!isset($vals['form']) || !is_array($vals['form'])) $vals['form'] = array();
						$vals['form']['status'] = ($vals['option']=='ship')?3:5;
						if ($vals['option']=='ship')
							foreach ($items as $v)
								Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders_details', $v['id'], $vals['form']['item__'.$v['id']]);
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 3:
					$lp->add_option('received', $this->lang->t('Yes'), null, null);
					$lp->add_option('onhold', $this->lang->t('No'), null, null);
					$this->display_module($lp, array($this->lang->t('Shipment Received?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						$vals['form'] = array();
						$vals['form']['status'] = ($vals['option']=='received')?4:5; 
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 4:
					$lp->add_option('received', $this->lang->t('Yes'), null, null);
					$lp->add_option('onhold', $this->lang->t('No'), null, null);
					$this->display_module($lp, array($this->lang->t('Final Inspection. All items received?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						$vals['form'] = array();
						if ($vals['option']=='received') {
							$vals['form']['status'] = 20;
						} else {
							$vals['form']['status'] = 5;
						} 
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $vals['form']);
						location(array());
					}
					break;
				case 5:
					$lp->add_option('items_available', $this->lang->t('Items Available'), null, null);
					$split = $this->init_module('Libs/QuickForm');
					$split->addElement('static', 'header', '');
					$split->setDefaults(array('header'=>'Select quantity and items you want to place in new transaction'));
					$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details', array('transaction_id'=>$trans['id']));
					foreach ($items as $v)
						$split->addElement((Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $v['item_name'], 'item_type')==1)?'checkbox':'text', 'item__'.$v['id'], Premium_Warehouse_Items_OrdersCommon::display_item_name($v, true));
					$lp->add_option('partial_order', $this->lang->t('Partial Order'), null, $split);
					$lp->add_option('cancel', $this->lang->t('Cancel'), null, null);
					$this->display_module($lp, array($this->lang->t('Final Inspection. All items received?')));
					$this->href = $lp->get_href();
					$vals = $lp->export_values();
					if ($vals!==null) {
						$up_vals = array();
						if ($vals['option']=='items_available') {
							$up_vals['status'] = 2;
						} elseif ($vals['option']=='partial_order') {
							$id = Utils_RecordBrowserCommon::new_record('premium_warehouse_items_orders', $trans);
							foreach ($items as $v)
								if (Utils_RecordBrowserCommon::get_value('premium_warehouse_items', $v['item_name'], 'item_type')==1){
									if (isset($vals['form']['item__'.$v['id']])) Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders_details', $v['id'], array('transaction_id'=>$id));
								} else {
									if (intval($vals['form']['item__'.$v['id']])>0) {
										$vals['form']['item__'.$v['id']] = intval($vals['form']['item__'.$v['id']]);
										$old = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders_details', $v['id']);
										if ($vals['form']['item__'.$v['id']]>$old['quantity']) $vals['form']['item__'.$v['id']] = $old['quantity'];
										if ($vals['form']['item__'.$v['id']]!=$old['quantity']) Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders_details', $v['id'], array('quantity'=>$old['quantity']-$vals['form']['item__'.$v['id']]));
										else Utils_RecordBrowserCommon::delete_record('premium_warehouse_items_orders_details', $v['id']);
										$old['transaction_id'] = $id;
										$old['quantity'] = $vals['form']['item__'.$v['id']];
										Utils_RecordBrowserCommon::new_record('premium_warehouse_items_orders_details', $old);
									}
								}
							break;		
						} else {
							$up_vals['status'] = 21;
						} 
						Utils_RecordBrowserCommon::update_record('premium_warehouse_items_orders', $trans['id'], $up_vals);
						location(array());
					}
					break;
			}
		}
	}
	
	public function get_href() {
		return $this->href;
	}

	public function caption(){
		return $this->rb->caption();
	}
}

?>