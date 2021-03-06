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

class Premium_Warehouse_Wholesale__Plugin_foto7 implements Premium_Warehouse_Wholesale__Plugin {
	/**
	 * Returns the name of the plugin
	 * 
	 * @return string plugin name 
	 */
	public function get_name() {
		return 'Foto 7';
	}
	
	/**
	 * Returns parameter list for the plugin
	 * The list should be an array where key is paramter name/label and value is type [text|password]
	 * 
	 * @return array parameters list 
	 */
	public function get_parameters() {
		return array();
	}

	/**
	 * Returns whether plugin supports auto-download feature
	 * 
	 * @return bool support enabled
	 */
	public function is_auto_download() {
		return true;
	}
	
	public function download_file($parameters, $distributor) {
		$dir = ModuleManager::get_data_dir('Premium_Warehouse_Wholesale');
		@unlink($dir.'cookiefile.cf');

	    $c = curl_init();
	    $url = 'http://www.foto7.com.pl/strefa/dystrybutora/xml/';

	    curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_HEADER, false);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.01; Windows XP)");
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($c, CURLOPT_COOKIEFILE, $dir.'cookiefile.cf');
		curl_setopt($c, CURLOPT_COOKIEJAR, $dir.'cookiefile.cf'); 

		$output = curl_exec($c);

		if (!$output) {
			Premium_Warehouse_WholesaleCommon::file_download_message(__('Invalid file, aborting.'), 2, true);
			return false;
		}

	    curl_close($c);

	    $time = time();
	    $filename = array();
	    $filename[] = $dir.'foto7a_'.$time.'.tmp';
		file_put_contents($filename[0], $output);

	    $c = curl_init();
	    $url = 'http://www.foto7.com.pl/strefa/dystrybutora/xml/MANFROTTO-EAN.csv';

	    curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_HEADER, false);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.01; Windows XP)");
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($c, CURLOPT_COOKIEFILE, $dir.'cookiefile.cf');
		curl_setopt($c, CURLOPT_COOKIEJAR, $dir.'cookiefile.cf'); 

		$output = curl_exec($c);

		if (!$output) {
			Premium_Warehouse_WholesaleCommon::file_download_message(__('Invalid file, aborting.'), 2, true);
			return false;
		}

	    curl_close($c);
	    $filename[] = $dir.'foto7b_'.$time.'.tmp';
		file_put_contents($filename[1], $output);

		Premium_Warehouse_WholesaleCommon::file_download_message(__('File downloaded.'), 1, true);
	    
	    return $filename;
	}

	public function update_from_file($filename, $distributor, $params) {
		ini_set("memory_limit","1024M");
		$xls = @simplexml_load_file($filename[0]);
		if(!$xls) {
			Premium_Warehouse_WholesaleCommon::file_scan_message(__('Unable to parse uploaded file, invalid XML.'), 2, true);
			return false;
		}
		
		$csv = fopen($filename[1],'r');
		if(!$csv) {
			Premium_Warehouse_WholesaleCommon::file_scan_message(__('Unable to parse uploaded file, no CSV.'), 2, true);
			return false;
		}
		$upc = array();
		while(($data = fgetcsv($csv, 1000, ";")) !== FALSE) {
			$upc[$data[0]] = $data[1];
		}
		fclose($csv);
		
		$uploaded_data = array();
		$map = array(
//			    'GROUP_NAME'=>'Grupa towarowa',
//		            'VENDOR_NAME'=>'Producent',
		            'description'=>'Nazwa produktu',
		            'msrp'=>'Cena netto',
		            'availability'=>'Stan mag',
		            'name'=>'Kod produktu');
//		            'name'=>'Kod producenta',
//		            'EAN'=>'upc',
//		            'CURRENCY_CODE'=>'Waluta');
		
		foreach($xls->product as $row) {
		    $tmp = array();
		    foreach($map as $k=>$v) {
		        if(!isset($row->$k)) break;
       		        $tmp[$v] = (string)$row->$k;
		    }
		    if(count($tmp)<4) continue;
		    if(isset($upc[$tmp['Kod produktu']])) {
			$tmp['upc'] = $upc[$tmp['Kod produktu']];
			$tmp['Producent'] = 'Manfrotto';
		    } else {
			$tmp['upc'] = '';
			$tmp['Producent'] = '';
		    }
			$uploaded_data[] = $tmp;
		}
		unset($xls);
		unset($upc);
		
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
		
//		$categories = DB::GetAssoc('SELECT f_foreign_category_name,id FROM premium_warehouse_distr_categories_data_1 WHERE active=1 AND f_distributor=%d',array($distributor['id']));
//		$categories_to_del = $categories;

		Premium_Warehouse_WholesaleCommon::file_scan_message(__('Scanning...'));
		foreach ($uploaded_data as $row) {
			Premium_Warehouse_WholesaleCommon::update_scan_status($total, $scanned, $available, $item_exist, $link_exist, $new_items, $new_categories);
			$scanned++;
			
			if (strlen($row['Nazwa produktu'])>127) $row['Nazwa produktu'] = substr($row['Nazwa produktu'],0,127);
			
			if ($row['Stan mag']=='true') {
				$available++;
				$row['Stan mag'] = 1;
			} else {
				$row['Stan mag'] = 0;
			}

			if (is_numeric($row['Cena netto'])) $row['Cena netto'] /= 1.23;
				
/*			if($row['Grupa towarowa']) {
				if(!isset($categories[$row['Grupa towarowa']])) {
					$categories[$row['Grupa towarowa']] = Utils_RecordBrowserCommon::new_record('premium_warehouse_distr_categories',array('foreign_category_name'=>$row['Grupa towarowa'],'distributor'=>$distributor['id']));
					$new_categories++;
				} elseif(isset($categories_to_del[$row['Grupa towarowa']]))
					unset($categories_to_del[$row['Grupa towarowa']]);
				$category = $categories[$row['Grupa towarowa']];
			} else */$category = null;

			$manufacturer = null;
			if($row['Producent']) {
				$cc = CRM_ContactsCommon::get_companies(array('company_name'=>$row['Producent']),array('group'));
				$producent = explode(' ',$row['Producent']);
				if(!$cc && count($producent)>1) 
					$cc = CRM_ContactsCommon::get_companies(array('company_name'=>$producent[0]),array('group'));
				if($cc) {
					$cc2 = array_shift($cc);
					$manufacturer = $cc2['id'];
			    		if(!in_array('manufacturer', $cc2['group'])) {
			    			$cc2['group']['manufacturer'] = 'manufacturer';
				    		Utils_RecordBrowserCommon::update_record('company',$cc2['id'],array('group'=>$cc2['group']));
			    		}
				}
			}

				$w_item = null;
				$matches = array();
				if(strlen($row['upc'])>0)
					$matches = Utils_RecordBrowserCommon::get_records('premium_warehouse_items', array('upc'=>$row['upc']));
			    if(empty($matches))
    				$matches = Utils_RecordBrowserCommon::get_records('premium_warehouse_items', array(
	    				'(~"item_name'=>DB::Concat(DB::qstr('%'),DB::qstr($row['Nazwa produktu']),DB::qstr('%'))
				));
				if (!empty($matches))
					if (count($matches)==1) {
						/*** one candidate found, if product code is empty or matches, it's ok ***/
						$v = array_pop($matches);
						$w_item = $v['id'];
					} else {
						/*** found more candidates, only product code is important now ***/
						foreach ($matches as $v)
							if ($v['upc']==$row['upc']) {
								$w_item = $v['id'];
								break;
							}
					}
				if ($w_item===null) {
					/*** no item was found matching this entry ***/
					$new_items++;
				} else {
					/*** found match ***/
					$item_exist++;
				}
				if (!is_numeric($row['Cena netto'])) $row['Cena netto'] = 0;

			/*** check for exact match ***/
			$internal_key = DB::GetOne('SELECT internal_key FROM premium_warehouse_wholesale_items WHERE internal_key=%s AND distributor_id=%d', array($row['Kod produktu'], $distributor['id']));
			if ($internal_key===false || $internal_key===null) {
				if ($w_item!==null) {
					DB::Execute('INSERT INTO premium_warehouse_wholesale_items (item_id, internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency,distributor_category,manufacturer,manufacturer_part_number,upc) VALUES (%d, %s, %s, %d, %d, %s, %f, %d,%d,%d, %s,%s)', array($w_item, $row['Kod produktu'], $row['Nazwa produktu'], $distributor['id'], $row['Stan mag'], '', $row['Cena netto'], $pln_id,$category,$manufacturer, '',substr($row['upc'],0,128)));
				} else {
					DB::Execute('INSERT INTO premium_warehouse_wholesale_items (internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency,distributor_category,manufacturer,manufacturer_part_number,upc) VALUES (%s, %s, %d, %d, %s, %f, %d,%d,%d, %s,%s)', array($row['Kod produktu'], $row['Nazwa produktu'], $distributor['id'], $row['Stan mag'], '', $row['Cena netto'], $pln_id,$category,$manufacturer, '',substr($row['upc'],0,128)));
				}
			} else {
				/*** there's an exact match in the system already ***/
				$link_exist++;
				DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s, price=%f, price_currency=%d,distributor_category=%d,manufacturer=%d,manufacturer_part_number=%s,upc=%s WHERE internal_key=%s AND distributor_id=%d', array($row['Stan mag'], '', $row['Cena netto'], $pln_id, $category,$manufacturer, '',substr($row['upc'],0,128), $row['Kod produktu'], $distributor['id']));
				if ($w_item!=null)
					DB::Execute('UPDATE premium_warehouse_wholesale_items SET item_id=%d WHERE internal_key=%s AND distributor_id=%d', array($w_item, $row['Kod produktu'], $distributor['id']));
			}
		} 
/*		foreach($categories_to_del as $name=>$id) {
			Utils_RecordBrowserCommon::delete_record('premium_warehouse_distr_categories',$id);
		}*/
		Premium_Warehouse_WholesaleCommon::file_scan_message(__('Scan complete.'), 1);
		Premium_Warehouse_WholesaleCommon::update_scan_status($scanned, $scanned, $available, $item_exist, $link_exist, $new_items, $new_categories);
		return true;
	}
}

?>
