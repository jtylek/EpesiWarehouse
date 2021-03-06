<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Wholesale__Plugin_xlscsv2 implements Premium_Warehouse_Wholesale__Plugin {
	/**
	 * Returns the name of the plugin
	 * 
	 * @return string plugin name 
	 */
	public function get_name() {
		return 'XLS or CSV custom import (UPC/EAN)';
	}
	
	/**
	 * Returns parameter list for the plugin
	 * The list should be an array where key is paramter name/label and value is type [text|password]
	 * 
	 * @return array parameters list 
	 */
	public function get_parameters() {
		return array(
			__('ID')=>'text',
			__('Nazwa produktu')=>'text',
			__('Stan magazynu')=>'text',
			__('Cena netto')=>'text',
			__('UPC/EAN')=>'text',
			__('Kod producenta')=>'text'
		);
	}

	/**
	 * Returns whether plugin supports auto-download feature
	 * 
	 * @return bool support enabled
	 */
	public function is_auto_download() {
		return false;
	}

	/**
	 * This method is called when user selects auto-update from the interface
	 * It should download new file and return path and filename to downloaded file that is ready for parsing
	 * (i.e. filename that would be a valid argument for update_from_file method)
	 * 
	 * @param array array of parameters for current distributor, with format {parameter name}=>{value} 
	 * @param array distributor record
	 * @return string filename with its location
	 */
	public function download_file($parameters, $distributor) {
	}

	/**
	 * This method is called when a new file is given either via upload or auto-download method
	 * It should parse the file and manipulate `premium_warehouse_wholesale_items` table to store results of the parsing
	 * 
	 * @param string filename that should be parsed with its location 
	 * @param array distributor record
	 * @return bool true if the update was successful, false otherwise
	 */
	public function update_from_file($filename, $distributor,$params) {
            try {
			ini_set("memory_limit","1024M");
			$xls = Libs_PHPExcelCommon::load($filename);
		} catch(Exception $e) {
			Premium_Warehouse_WholesaleCommon::file_scan_message(__('Unable to parse uploaded file, invalid XLS. %s',array($e)), 2, true);
			return false;
		}
            $uploaded_data = array();    
            $map = array(array(strtolower(trim($params["ID"])),'Kod produktu'),
			array(strtolower(trim($params["Nazwa produktu"])),'Nazwa produktu'),
			array(strtolower(trim($params["Stan magazynu"])),'Stan mag'),
			array(strtolower(trim($params["Cena netto"])),'Cena netto'),
                        array(strtolower(trim($params["UPC/EAN"])),'UPC/EAN'),
                        array(strtolower(trim($params["Kod producenta"])),'Kod producenta')
		);
           
            foreach($xls->getAllSheets() as $sheet) {
			$cols = null;
			foreach($sheet->getRowIterator() as $row) {
				$cellIterator = $row->getCellIterator();
			  	$cellIterator->setIterateOnlyExistingCells(false);
				if($cols===null) {
					$cols = array();
					foreach ($cellIterator as $j=>$cell) {
						$cols[$j] = strtolower(trim($cell->getValue()));
					}
				} else {
					$tmp = array();
                                        $blad=false;
					foreach ($cellIterator as $j=>$cell) {
                                                foreach($map as $mmm) {
                                                    if($mmm[0] != $cols[$j]) continue;
                                                    $tmp[$mmm[1]] = trim($cell->getValue());
                                                    if($tmp[$mmm[1]]==='' && (($mmm[1]=='Kod produktu') //pobranie wymagania produktu
                                                            || ($mmm[1]=='Nazwa produktu') ||($mmm=='Stan mag')|| ($mmm=='Cenna netto') )) {
                                                        $blad = true;
                                                       break;
                                                    }
                                                }
					}
                                        
                                        if(!$blad)
                                                $uploaded_data[] = $tmp;
				}
			}
		}
		unset($xls);
                
               
            

		$total = null;
		$scanned = 0;
		$available = 0;
		$link_exist = 0;
		$item_exist = 0;
		$new_items = 0;
		$new_categories = 0;
		
		

		$pln_id = Utils_CurrencyFieldCommon::get_id_by_code('PLN');
		if ($pln_id===false || $pln_id===null) {
			Premium_Warehouse_WholesaleCommon::file_scan_message(__('Unable to find required currency (%s), aborting.', array('PLN')), 2, true);
			return false;
		}

		DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s WHERE distributor_id=%d', array(0, '', $distributor['id']));
//		DB::Execute('DELETE FROM premium_warehouse_wholesale_items WHERE distributor_id=%d', array($distributor['id']));
		
		Premium_Warehouse_WholesaleCommon::file_scan_message(__('Scanning...'));
		foreach($uploaded_data as $row) {
			Premium_Warehouse_WholesaleCommon::update_scan_status($total, $scanned, $available, $item_exist, $link_exist, $new_items, $new_categories);
			$scanned++;
			
			$row['Nazwa produktu'] = trim($row['Nazwa produktu']);
			if (strlen($row['Nazwa produktu'])>127) $row['Nazwa produktu'] = substr($row['Nazwa produktu'],0,127);
			
			if ($row['Stan mag']!=0) {
				$available++;
			}
				/*** determine quantity and quantity info ***/
			if (is_numeric($row['Stan mag'])) {
				$quantity = $row['Stan mag'];
				$quantity_info = '';
			} else {
				$quantity_info = $row['Stan mag'];
				$quantity = 1;
			}
                        if(!isset($row['Kod producenta'])) $row['Kod producenta']='';
                        if(!isset($row['UPC/EAN'])) $row['UPC/EAN']='';
                        if(!is_numeric($row['Cena netto'])) continue;
			
                       	
				$w_item = null;
				if($row['Kod producenta'] || $row['UPC/EAN']) {
					/*** exact match not found, looking for candidates ***/
					$matches = Utils_RecordBrowserCommon::get_records('premium_warehouse_items', array(
						'(~"item_name'=>DB::Concat(DB::qstr('%'),DB::qstr($row['Nazwa produktu']),DB::qstr('%')),
						'|product_code'=>$row['Kod producenta'],
						'|manufacturer_part_number'=>$row['Kod producenta'],
						'|upc'=>$row['UPC/EAN']
					));
					if (!empty($matches))
						if (count($matches)==1) {
							/*** one candidate found, if product code is empty or matches, it's ok ***/
							$v = array_pop($matches);
							$w_item = $v['id'];
						} else {
							/*** found more candidates, only product code is important now ***/
							foreach ($matches as $v)
								if ($v['manufacturer_part_number']==$row['Kod producenta'] || $v['upc']==$row['UPC/EAN']) {
									$w_item = $v['id'];
									break;
								}
						}
				}
				if ($w_item===null) {
					/*** no item was found matching this entry ***/
					$new_items++;
				} else {
					/*** found match ***/
					$item_exist++;
				}
                        
			/*** check for exact match ***/
			$internal_key = DB::GetOne('SELECT internal_key FROM premium_warehouse_wholesale_items WHERE internal_key=%s AND distributor_id=%d', array($row['Kod produktu'], $distributor['id']));
			if ($internal_key===false || $internal_key===null) {
				if ($w_item!==null) {
					DB::Execute('INSERT INTO premium_warehouse_wholesale_items (item_id, internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency,upc,manufacturer_part_number) VALUES (%d, %s, %s, %d, %d, %s, %f, %d, %s, %s)', array($w_item, $row['Kod produktu'], $row['Nazwa produktu'], $distributor['id'], $quantity, $quantity_info, $row['Cena netto'], $pln_id, substr($row['UPC/EAN'],0,128),substr($row['Kod producenta'],0,32)));
				} else {
					DB::Execute('INSERT INTO premium_warehouse_wholesale_items (internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency,upc,manufacturer_part_number) VALUES (%s, %s, %d, %d, %s, %f, %d, %s, %s)', array($row['Kod produktu'], $row['Nazwa produktu'], $distributor['id'], $quantity, $quantity_info, $row['Cena netto'], $pln_id,substr($row['UPC/EAN'],0,128), substr($row['Kod producenta'],0,32)));
				}
			} else {
				/*** there's an exact match in the system already ***/
				$link_exist++;
				DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s, price=%f, price_currency=%d,upc=%s,manufacturer_part_number=%s WHERE internal_key=%s AND distributor_id=%d', array($quantity, $quantity_info, $row['Cena netto'], $pln_id, substr($row['UPC/EAN'],0,128),substr($row['Kod producenta'],0,32), $row['Kod produktu'], $distributor['id']));
				if ($w_item!=null)
					DB::Execute('UPDATE premium_warehouse_wholesale_items SET item_id=%d WHERE internal_key=%s AND distributor_id=%d', array($w_item, $row['Kod produktu'], $distributor['id']));
			}
		} 
		Premium_Warehouse_WholesaleCommon::file_scan_message(__('Scan complete.'), 1);
		Premium_Warehouse_WholesaleCommon::update_scan_status($scanned, $scanned, $available, $item_exist, $link_exist, $new_items, $new_categories);
		return true;
	}
}

?>

