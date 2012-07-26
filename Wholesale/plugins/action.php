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

class Premium_Warehouse_Wholesale__Plugin_action implements Premium_Warehouse_Wholesale__Plugin {
	/**
	 * Returns the name of the plugin
	 * 
	 * @return string plugin name 
	 */
	public function get_name() {
		return 'Action';
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
			__('Login')=>'text',
			__('Password')=>'password'
		);
	}

	/**
	 * Returns whether plugin supports auto-download feature
	 * 
	 * @return bool support enabled
	 */
	public function is_auto_download() {
		return true;
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
		$dir = ModuleManager::get_data_dir('Premium_Warehouse_Wholesale');
		@unlink($dir.'cookiefile.cf');

	    $c = curl_init();
		$url = 'https://i-serwis2.action.pl/Login.aspx';

	    curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2);
		curl_setopt($c, CURLOPT_HEADER, false);
		curl_setopt($c, CURLOPT_POST, false);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)");
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($c, CURLOPT_COOKIEFILE, $dir.'cookiefile.cf');
		curl_setopt($c, CURLOPT_COOKIEJAR, $dir.'cookiefile.cf');

		$output = curl_exec($c);
	    
	    preg_match('/id=\"\_\_VIEWSTATE\" value=\"(.*?)\"/', $output, $viewstate);	    

	    if (empty($viewstate)) {
			$output = curl_exec($c);
		    preg_match('/id=\"\_\_VIEWSTATE\" value=\"(.*?)\"/', $output, $viewstate);	    
	    }
		preg_match('/id=\"\_\_EVENTVALIDATION\" value=\"(.*?)\"/', $output, $eventvalidation);	    

		if (!isset($eventvalidation[1]) || !isset($viewstate[1])) {
			Premium_Warehouse_WholesaleCommon::file_download_message(__('Authentication failure, aborting.'), 2, true);
			return false;
		}

	    curl_setopt($c, CURLOPT_POST, true);
	    curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query(array(
			'txtCustomerID'=>$parameters['ID'], 
			'txtLogin'=>$parameters['Login'], 
			'txtPassword'=>$parameters['Password'],
			'__EVENTVALIDATION'=>$eventvalidation[1],
			'__VIEWSTATE'=>$viewstate[1],
			'ButtonLogIn'=>'Zaloguj'
		)));
		$output = curl_exec($c);

		curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);

		$output = curl_exec($c);

		$url = 'http://i-serwis2.action.pl/ExportProxy.aspx?type=csv';
	    curl_setopt($c, CURLOPT_URL, $url);

		$output = curl_exec($c);
		
		if (!$output || strlen($output)<20000) {
			Premium_Warehouse_WholesaleCommon::file_download_message(__('Authentication failure, aborting.'), 2, true);
			return false;
		}
	    $time = time();

		$filename = $dir.'action_'.$time.'.tmp';
		file_put_contents($filename, iconv("cp1250","UTF-8",$output));

	    curl_close($c);

		Premium_Warehouse_WholesaleCommon::file_download_message(__('File downloaded.'), 1, true);
	    
	    return $filename;
	}

	/**
	 * This method is called when a new file is given either via upload or auto-download method
	 * It should parse the file and manipulate `premium_warehouse_wholesale_items` table to store results of the parsing
	 * 
	 * @param string filename that should be parsed with its location 
	 * @param array distributor record
	 * @return bool true if the update was successful, false otherwise
	 */
	public function update_from_file($filename, $distributor, $params) {

		$f = fopen($filename,'r');
		$delimiter = ',';

		$total = null;
		$scanned = 0;
		$available = 0;
		$link_exist = 0;
		$item_exist = 0;
		$new_items = 0;
		$new_categories = 0;
		
		$keys = array(
			'Grupa towarowa',
			'Podgrupa towarowa',
			'Producent',
			'Nazwa produktu',
			'Cena netto',
			'Cena brutto',
			'Kod produktu',
			'Gwarancja',
			'Stan mag',
			'Kod producenta',
			'Cena sugerowana',
			'Oplata wielkogabarytowa'
		);

		$pln_id = Utils_CurrencyFieldCommon::get_id_by_code('PLN');
		if ($pln_id===false || $pln_id===null) {
			Premium_Warehouse_WholesaleCommon::file_scan_message(__('Unable to find required currency (%s), aborting.', array('PLN')), 2, true);
			return false;
		}

		DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s WHERE distributor_id=%d', array(0, '', $distributor['id']));
		
		$categories = DB::GetAssoc('SELECT f_foreign_category_name,id FROM premium_warehouse_distr_categories_data_1 WHERE active=1 AND f_distributor=%d',array($distributor['id']));
		$categories_to_del = $categories;

		Premium_Warehouse_WholesaleCommon::file_scan_message(__('Scanning...'));
		while (!feof($f)) {
			$row = fgetcsv($f,0,$delimiter);
			if ($row===false) break;
			Premium_Warehouse_WholesaleCommon::update_scan_status($total, $scanned, $available, $item_exist, $link_exist, $new_items, $new_categories);
			$scanned++;
			
			foreach ($row as $k=>$v) $row[$keys[$k]] = $v;
			$row['Nazwa produktu'] = $row['Nazwa produktu'];
			if (strlen($row['Nazwa produktu'])>127) $row['Nazwa produktu'] = substr($row['Nazwa produktu'],0,127);
			
			if ($row['Stan mag']!=0) {
				$available++;
			}
				/*** determine quantity and quantity info ***/
			if (is_numeric($row['Stan mag'])) {
				$quantity = $row['Stan mag'];
				$quantity_info = '';
			} elseif($row['Stan mag'] == 'powyżej 30') {
				$quantity_info = $row['Stan mag'];
				$quantity = 30;
			} else {
				$quantity_info = $row['Stan mag'];
				$quantity = 0;
			}
				
			if($row['Grupa towarowa'] || $row['Podgrupa towarowa']) {
				if(!isset($categories[$row['Grupa towarowa'].' : '.$row['Podgrupa towarowa']])) {
					$categories[$row['Grupa towarowa'].' : '.$row['Podgrupa towarowa']] = Utils_RecordBrowserCommon::new_record('premium_warehouse_distr_categories',array('foreign_category_name'=>$row['Grupa towarowa'].' : '.$row['Podgrupa towarowa'],'distributor'=>$distributor['id']));
					$new_categories++;
				} elseif(isset($categories_to_del[$row['Grupa towarowa'].' : '.$row['Podgrupa towarowa']]))
					unset($categories_to_del[$row['Grupa towarowa'].' : '.$row['Podgrupa towarowa']]);
				$category = $categories[$row['Grupa towarowa'].' : '.$row['Podgrupa towarowa']];
			} else $category = null;

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
				if($row['Kod producenta']) {
					/*** exact match not found, looking for candidates ***/
					$matches = Utils_RecordBrowserCommon::get_records('premium_warehouse_items', array(
						'(~"item_name'=>DB::Concat(DB::qstr('%'),DB::qstr($row['Nazwa produktu']),DB::qstr('%')),
						'|manufacturer_part_number'=>$row['Kod producenta'],
						'|product_code'=>$row['Kod producenta']
					));
					if (!empty($matches))
						if (count($matches)==1) {
							/*** one candidate found, if product code is empty or matches, it's ok ***/
							$v = array_pop($matches);
							if ($v['manufacturer_part_number']==$row['Kod producenta'] || $v['manufacturer_part_number']=='')
								$w_item = $v['id'];
						} else {
							/*** found more candidates, only product code is important now ***/
							foreach ($matches as $v)
								if ($v['manufacturer_part_number']==$row['Kod producenta']) {
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
				if (!is_numeric($row['Cena netto'])) $row['Cena netto'] = 0;

			/*** check for exact match ***/
			$internal_key = DB::GetOne('SELECT internal_key FROM premium_warehouse_wholesale_items WHERE internal_key=%s AND distributor_id=%d', array($row['Kod produktu'], $distributor['id']));
			if ($internal_key===false || $internal_key===null) {
				if ($w_item!==null) {
					DB::Execute('INSERT INTO premium_warehouse_wholesale_items (item_id, internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency,distributor_category,manufacturer,manufacturer_part_number) VALUES (%d, %s, %s, %d, %d, %s, %f, %d,%d,%d, %s)', array($w_item, $row['Kod produktu'], $row['Nazwa produktu'], $distributor['id'], $quantity, $quantity_info, $row['Cena netto'], $pln_id,$category,$manufacturer, substr($row['Kod producenta'],0,32)));
				} else {
					DB::Execute('INSERT INTO premium_warehouse_wholesale_items (internal_key, distributor_item_name, distributor_id, quantity, quantity_info, price, price_currency,distributor_category,manufacturer,manufacturer_part_number) VALUES (%s, %s, %d, %d, %s, %f, %d,%d,%d, %s)', array($row['Kod produktu'], $row['Nazwa produktu'], $distributor['id'], $quantity, $quantity_info, $row['Cena netto'], $pln_id,$category,$manufacturer, substr($row['Kod producenta'],0,32)));
				}
			} else {
				/*** there's an exact match in the system already ***/
				$link_exist++;
				DB::Execute('UPDATE premium_warehouse_wholesale_items SET quantity=%d, quantity_info=%s, price=%f, price_currency=%d,distributor_category=%d,manufacturer=%d,manufacturer_part_number=%s WHERE internal_key=%s AND distributor_id=%d', array($quantity, $quantity_info, $row['Cena netto'], $pln_id, $category,$manufacturer, substr($row['Kod producenta'],0,32), $row['Kod produktu'], $distributor['id']));
				if ($w_item!=null)
					DB::Execute('UPDATE premium_warehouse_wholesale_items SET item_id=%d WHERE internal_key=%s AND distributor_id=%d', array($w_item, $row['Kod produktu'], $distributor['id']));
			}
		} 
		foreach($categories_to_del as $name=>$id) {
			Utils_RecordBrowserCommon::delete_record('premium_warehouse_distr_categories',$id);
		}
		Premium_Warehouse_WholesaleCommon::file_scan_message(__('Scan complete.'), 1);
		Premium_Warehouse_WholesaleCommon::update_scan_status($scanned, $scanned, $available, $item_exist, $link_exist, $new_items, $new_categories);
		fclose($f);
		return true;
	}
}

?>
