<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * @author Janusz Tylek <jtylek@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse-salesreport
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_SalesReport extends Module {
	private $cats = array();
	private $format = '';
	private $columns = array();
	private $range_type = '';
	private $rbr = null;

	public function construct() {
		$this->rbr = $this->init_module('Utils/RecordBrowser/Reports');
	}

/************************************************************************************/
	public function body() {
		if (!Base_AclCommon::i_am_admin() || !Base_AclCommon::i_am_sa()) {
			print($this->t('You don\'t have permission to access this module'));
			return;
		}

		if (isset($_REQUEST['mode'])) $this->set_module_variable('mode',$_REQUEST['mode']);
		$mode = $this->get_module_variable('mode',null);

		switch ($mode) {
			case 'sales_by_warehouse': $this->sales_by_warehouse(); break;
			case 'sales_by_item': $this->sales_by_item(); break;
			default: print($this->t('Unknown mode'));
		}
	}
	
	public function sales_by_warehouse() {
		$this->cats = array('Sales Trans.','Sales Volume','Purchase Trans.','Purchase Volume','Net Profit');
		$recs = Utils_RecordBrowserCommon::get_records('premium_warehouse',array(),array(),array('warehouse'=>'ASC'));
		$this->rbr->set_reference_records($recs);
		$this->rbr->set_reference_record_display_callback(array('Premium_WarehouseCommon','display_warehouse'));
		$date_range = $this->rbr->display_date_picker();
		$this->rbr->set_categories($this->cats);
		$this->rbr->set_summary('col', array('label'=>'Total'));
		$this->rbr->set_summary('row', array('label'=>'Total'));
		$this->rbr->set_format(array(	$this->cats[0]=>'numeric', 
										$this->cats[1]=>'currency',
										$this->cats[2]=>'numeric',
										$this->cats[3]=>'currency',
										$this->cats[4]=>'currency'
									));
		$header = array('Warehouse');
		$this->columns = $date_range['dates'];
		$this->range_type = $date_range['type'];
		$this->rbr->set_currency($this->currency);
		switch ($date_range['type']) {
			case 'day': $this->format ='d M Y'; break;
			case 'week': $this->format ='W Y'; break;
			case 'month': $this->format ='M Y'; break;
			case 'year': $this->format ='Y'; break;
		} 
		foreach ($this->columns as $v)
			$header[] = date($this->format, $v);
		$this->rbr->set_table_header($header);
		$this->rbr->set_display_cell_callback(array($this, 'display_sales_by_warehouse_cells'));
		$this->rbr->set_pdf_title($this->t('Sales Report, %s',array(date('Y-m-d H:i:s'))));
		$this->rbr->set_pdf_subject($this->rbr->pdf_subject_date_range());
		$this->rbr->set_pdf_filename($this->t('Sales_Report_%s',array(date('Y_m_d__H_i_s'))));
		$this->display_module($this->rbr);
	}

	
/************************************************************************************/
	public function display_sales_by_warehouse_cells($ref_rec){
		$result = array();
		$hash = array();
		$i = 0;
		foreach ($this->columns as $v) {
		// all $cats must be initialized here individually to avoid: "Message: Undefined index: Purchase Volume" error - see private static $cats = array(... above
			$result[$i] = array(	$this->cats[0]=>0,
									$this->cats[1]=>0,
									$this->cats[2]=>0,
									$this->cats[3]=>0,
									$this->cats[4]=>0);
			$hash[date($this->format, $v)] = $i;
			$i++;
		}
		
		$records = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders', array('warehouse'=>$ref_rec['id']));
		// TODO: transaction status filter
		// TODO: warehouse transfer
		// transactions types: 0=>'Purchase',1=>'Sale',2=>'Inventory Adjustment',3=>'Rental',4=>'Warehouse Transfer
		foreach ($records as $v) {
			$d = date($this->format,strtotime($v['transaction_date']));
			if (isset($hash[$d])) {
				// count no. of Sales/Purchase Transactions
				// and sales/purchase volume
				// Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value()
				// returns array where keys are currency IDs and values are result numbers
		
				// Epesi::debug(print_r(Premium_Warehouse_Items_OrdersCommon::get_status_array($v)));
				switch ($v['transaction_type']) {
					/********************** Purchase *******************/
					case '0':
						// Include only Completed transactions - status=20
						if ($v['status']==20) {
							$result[$hash[$d]][$this->cats[2]]++;
							$purchase_amount=Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value($v,'total');
							if (!isset($purchase_amount[$this->currency])) break;
							$result[$hash[$d]][$this->cats[3]]+=$purchase_amount[$this->currency];
							// Net loss/profit - Decrease - note -=
							$result[$hash[$d]][$this->cats[4]] -= $purchase_amount[$this->currency];
							}
						break;
					/********************** Sale *******************/
					case '1':
						// Include only 7=>'Shipped', 20=>'Delivered'
						if ($v['status']==7 || $v['status']==20) {
							$result[$hash[$d]][$this->cats[0]]++;
							$sale_amount=Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value($v,'total');
							if (!isset($sale_amount[$this->currency])) break;
							$result[$hash[$d]][$this->cats[1]]+=$sale_amount[$this->currency];
							// Net loss/profit - Increase - note +=
							$result[$hash[$d]][$this->cats[4]] += $sale_amount[$this->currency];
						}
						break;
						/********************** Inventory Adjustment *******************/
					case '2':
						// 20=>'Completed'
						if ($v['status']==20) {
							$result[$hash[$d]][$this->cats[2]]++;
							$purchase_amount=Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value($v,'total');
							if (!isset($purchase_amount[$this->currency])) break;
							$result[$hash[$d]][$this->cats[3]]+=$purchase_amount[$this->currency];
							// Net loss/profit - Decrease - note -=
							$result[$hash[$d]][$this->cats[4]] -= $purchase_amount[$this->currency];
							}
						break;
						/********************** WAREHOUSE TRANSFER *******************/
						/* Ignore - Sales/Purchase Volume = 0
					case 4:
						// ''=>'New', 1=>'Transfer Quote', 2=>'Pending', 3=>'Order Fullfilment', 4=>'On Hold', 5=>'Ready to Ship', 6=>'Shipped', 20=>'Delivered', 21=>'Canceled', 22=>'Missing'
						if ($v['status']==20) {
							$result[$hash[$d]][$this->cats[2]]++;
							$purchase_amount=Premium_Warehouse_Items_OrdersCommon::calculate_tax_and_total_value($v,'total');
							Epesi::debug($v['warehouse']);
							$result[$hash[$d]][$this->cats[3]]+=$purchase_amount[1];
							// Net loss/profit - Decrease - note -=
							$result[$hash[$d]][$this->cats[4]] -= $purchase_amount[1];
							}
						break;
						*/
				} // end of switch
			}
		}
		
		$i = 0;
		foreach ($this->columns as $v) {
			switch ($this->range_type) {
				case 'day':		$start = date('Y-m-d',$v);
								$end = date('Y-m-d',$v);
								break;
				case 'week':	$m = date('N',$v)-1;
								$start = date('Y-m-d',$v-$m*86400);
								$end = date('Y-m-d',$v+(6-$m)*86400);
								break;
				case 'month':	$start = date('Y-m-01',$v);
								$end = date('Y-m-t',$v);
								break;
				case 'year':	$start = date('Y-01-01',$v);
								$end = date('Y-12-31',$v);
								break;
			}
			$end = date('Y-m-d',strtotime($end)+1);
			
			// drill-in report links
			// Sales transactions link
			if ($result[$i][$this->cats[0]]<>0) {
				$result[$i][$this->cats[0]] = '<a '.$this->create_callback_href(array($this,'display_sales'), array($ref_rec['id'], $start, $end)).'>'.$result[$i][$this->cats[0]].'</a>';
			}
			// Purchases transactions link
			if ($result[$i][$this->cats[2]]<>0) {
				$result[$i][$this->cats[2]] = '<a '.$this->create_callback_href(array($this,'display_purchases'), array($ref_rec['id'], $start, $end)).'>'.$result[$i][$this->cats[2]].'</a>';
			}
			$i++;
		}
		return $result;
	}

	public function sales_by_item() {
		$this->range_type = $this->rbr->display_date_picker();
		$items_ids = DB::GetCol('SELECT od.f_item_name FROM premium_warehouse_items_orders_details_data_1 AS od LEFT JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id WHERE od.active=1 AND o.f_transaction_type=1 AND o.f_status=20 AND o.f_transaction_date>=%D AND o.f_transaction_date<=%D GROUP BY od.f_item_name', array($this->range_type['start'], $this->range_type['end']));
		$warehouses = Utils_RecordBrowserCommon::get_records('premium_warehouse',array(),array(),array('warehouse'=>'ASC'));
		$items_amount = Utils_RecordBrowserCommon::get_records_limit('premium_warehouse_items',array('id'=>$items_ids),array(),array('item_name'=>'ASC'));
		$limit = $this->rbr->enable_paging($items_amount);
		$items = Utils_RecordBrowserCommon::get_records('premium_warehouse_items',array('id'=>$items_ids),array(),array('item_name'=>'ASC'), $limit);
		$this->rbr->set_reference_records($items);
		$this->rbr->set_reference_record_display_callback(array('Premium_Warehouse_ItemsCommon','display_item_name'));
		$this->rbr->set_summary('col', array('label'=>'Total'));
		$this->rbr->set_summary('row', array('label'=>'Total'));
		$this->rbr->set_format('currency');
		$header = array('Item Name');
		$this->columns = array();
		foreach ($warehouses as $v) {
			$header[] = $v['warehouse'];
			$this->columns[$v['id']] = $v['warehouse'];
		}
		$this->rbr->set_table_header($header);
		$this->rbr->set_display_cell_callback(array($this, 'display_sales_by_item_cells'));
		$this->rbr->set_pdf_title($this->t('Sales Report, %s',array(date('Y-m-d H:i:s'))));
		$this->rbr->set_pdf_subject($this->rbr->pdf_subject_date_range());
		$this->rbr->set_pdf_filename($this->t('Sales_Report_%s',array(date('Y_m_d__H_i_s'))));
		$this->display_module($this->rbr);
	}	
	
	public function display_sales_by_item_cells($ref_rec) {
		$ret = array();
		$quantity_sold = DB::GetAssoc('SELECT o.f_warehouse, SUM(od.f_quantity) FROM premium_warehouse_items_orders_details_data_1 AS od LEFT JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id WHERE od.active=1 AND o.f_transaction_type=1 AND od.f_item_name=%d AND o.f_status=20 GROUP BY o.f_warehouse', array($ref_rec['id']));
		foreach ($this->columns as $k=>$v) {
			if (!isset($quantity_sold[$k])) {
				$ret[] = 0;
				continue;
			}
			$purchases = DB::Execute('SELECT * FROM premium_warehouse_items_orders_details_data_1 AS od LEFT JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id WHERE od.active=1 AND o.f_transaction_type=0 AND od.f_item_name=%d AND o.f_status=20 AND o.f_warehouse=%d ORDER BY o.f_transaction_date ASC', array($ref_rec['id'], $k));
			$sales = DB::Execute('SELECT * FROM premium_warehouse_items_orders_details_data_1 AS od LEFT JOIN premium_warehouse_items_orders_data_1 AS o ON o.id=od.f_transaction_id WHERE od.active=1 AND o.f_transaction_type=1 AND od.f_item_name=%d AND o.f_status=20 AND o.f_warehouse=%d ORDER BY o.f_transaction_date ASC', array($ref_rec['id'], $k));
			$earned = array();
			$last_purchase_price = 0;
			$sale = null;
			$purchase = null;
			while (true) {
				if (!$sale || $sale['f_quantity']==0) {
					$sale = $sales->FetchRow();
					if (!$sale) break; // This is the main exit from the loop
					$sale['f_net_price'] = Utils_CurrencyFieldCommon::get_values($sale['f_net_price']);
					$sale_price = round((100+Data_TaxRatesCommon::get_tax_rate($sale['f_tax_rate']))*$sale['f_net_price'][0]/100, Utils_CurrencyFieldCommon::get_precission($sale['f_net_price'][1]));
					$sale_currency = $sale['f_net_price'][1]; 
				}
				if (!$purchase || $purchase['f_quantity']==0) {
					$purchase = $purchases->FetchRow();
					if (!$purchase) {
						if (!$last_purchase_price) break;						
					} else {
						$purchase['f_net_price'] = Utils_CurrencyFieldCommon::get_values($purchase['f_net_price']);
						$purchase_price = round((100+Data_TaxRatesCommon::get_tax_rate($purchase['f_tax_rate']))*$purchase['f_net_price'][0]/100, Utils_CurrencyFieldCommon::get_precission($purchase['f_net_price'][1]));
						$purchase_currency = $purchase['f_net_price'][1]; 
					}
				}
				if ($sale_currency!=$purchase_currency) {
					// TODO: currency conflict
					$earned = 'Currencies mixed';
					break;
				}
				if ($purchase['f_quantity']==0) $qty = $sale['f_quantity'];
				else $qty = min($purchase['f_quantity'], $sale['f_quantity']);
				
//				print('<hr>');
//				print('Amount: '.$qty.'<br>');
//				print('Purchase price: '.$purchase_price.'<br>');
//				print('Sale price: '.$sale_price.'<br>');
//				print('Purchase transaction: '.$purchase['id'].'<br>');
//				print('Sale transaction: '.$sale['id'].'<br>');
				$purchase['f_quantity'] -= $qty;
				$sale['f_quantity'] -= $qty;
				if (!isset($earned[$purchase_currency])) $earned[$purchase_currency] = 0;
				if ($sale['f_transaction_date']>=$this->range_type['start'] && $sale['f_transaction_date']<=$this->range_type['end']) {
//					print('INCLUDED!<br>');
					$earned[$purchase_currency] += ($sale_price - $purchase_price)*$qty;
				}
			}
			$ret[] = $earned;
		}
		return $ret;
	}
	
/************************************************************************************/
	public function display_sales($warehouse_id, $start, $end) {
		if ($this->is_back()) return false;
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders','orders_module');
		$order_date='transaction_date';
		$cols='';
		// sales status can be 7 or 20
		$orders = array(array('warehouse'=>$warehouse_id,'transaction_type'=>'1','status'=>array(20,7),'>='.$order_date=>$start, '<='.$order_date=>$end), $cols, array($order_date=>'DESC'));
		$rb->set_header_properties(array('terms'=>array('width'=>1, 'wrapmode'=>'nowrap'),'status'=>array('width'=>1, 'wrapmode'=>'nowrap')));
		$this->display_module($rb,$orders,'show_data');
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
		return true;
	}
	
/************************************************************************************/
	public function display_purchases($warehouse_id, $start, $end) {
		if ($this->is_back()) return false;
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders','orders_module');
		$order_date='transaction_date';
		$cols='';
		$orders = array(array('warehouse'=>$warehouse_id,'transaction_type'=>'0','status'=>'20', '>='.$order_date=>$start, '<='.$order_date=>$end), $cols, array($order_date=>'DESC'));
		$rb->set_header_properties(array('terms'=>array('width'=>1, 'wrapmode'=>'nowrap'),'status'=>array('width'=>1, 'wrapmode'=>'nowrap')));
		$this->display_module($rb,$orders,'show_data');
		Base_ActionBarCommon::add('back', 'Back', $this->create_back_href());
		return true;
	}
	
/************************************************************************************/
	public function caption() {
		return 'Sales Report';
	}
}

?>