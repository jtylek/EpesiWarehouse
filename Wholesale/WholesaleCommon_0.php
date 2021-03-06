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
 * @subpackage warehouse-wholesale
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_WholesaleCommon extends ModuleCommon {
	public static $plugin_path = 'modules/Premium/Warehouse/Wholesale/plugins/';
	public static $current_plugin = '';
	
	public static function get_plugin($arg) {
		static $plugins = array();
		if (isset($plugins[$arg])) return $plugins[$arg];

		static $interface_included = false;
		if (!$interface_included)
			require_once('modules/Premium/Warehouse/Wholesale/interface.php');

		if (is_numeric($arg)) {
			$id = $arg;
			$filename = DB::GetOne('SELECT filename FROM premium_warehouse_wholesale_plugin WHERE id=%d', array($arg));
		} else {
			$filename = $arg;
			$id = DB::GetOne('SELECT id FROM premium_warehouse_wholesale_plugin WHERE filename=%s', array($arg));
		}
		if (is_file(self::$plugin_path.basename($filename).'.php')) {
			require_once(self::$plugin_path.basename($filename).'.php');
			$class = 'Premium_Warehouse_Wholesale__Plugin_'.$filename;
			if (!class_exists($class))
				trigger_error('Warning: invalid plugin in file '.$filename.'.php<br>', E_USER_ERROR);
			return $plugins[$id] = $plugins[$filename] = new $class();
		}
		return null;
	}
	
	public static function scan_for_plugins() {
		$dir = scandir(self::$plugin_path);
		DB::Execute('UPDATE premium_warehouse_wholesale_plugin SET active=2');
		foreach ($dir as $file) {
			if ($file=='..' || $file=='.' || !preg_match('/\.php$/i',$file)) continue;
			$filename = basename($file, '.php');
			$plugin = self::get_plugin($filename);
			if ($plugin) {
				$name = $plugin->get_name();
				$id = DB::GetOne('SELECT id FROM premium_warehouse_wholesale_plugin WHERE filename=%s', array($filename));
				if ($id===false || $id==null) {
					DB::Execute('INSERT INTO premium_warehouse_wholesale_plugin (name, filename, active) VALUES (%s, %s, 1)', array($name, $filename));
				} else {
					DB::Execute('UPDATE premium_warehouse_wholesale_plugin SET active=1, name=%s WHERE id=%d', array($name, $id));
				}
			}
		}
		DB::Execute('UPDATE premium_warehouse_wholesale_plugin SET active=0 WHERE active=2');
		return false;
	}

    public static function display_distributor($v, $nolink=false) {
		return Utils_RecordBrowserCommon::create_linked_label_r('premium_warehouse_distributor', 'Name', $v, $nolink);
	}
	
    public static function get_distributor_qty($v) {
	return DB::GetRow('SELECT SUM(quantity) AS qty, MAX(quantity_info) AS qty_info FROM premium_warehouse_wholesale_items WHERE item_id=%d', array($v));
    }
	
    public static function display_distributor_qty($v, $nolink=false) {
    	$row = self::get_distributor_qty($v['id']);
    	if (!$row['qty'] && !$row['qty_info']) return 0;
		return '<span '.Utils_TooltipCommon::ajax_open_tag_attrs(array('Premium_Warehouse_WholesaleCommon','dist_qty_tooltip'), array($v['id']),500).'>'.$row['qty'].($row['qty_info']?'*':'').'</span>';
	}
	
	public static function dist_qty_tooltip($item_id) {
    	$ret = DB::Execute('SELECT * FROM premium_warehouse_wholesale_items WHERE item_id=%d ORDER BY quantity', array($item_id));
		$theme = Base_ThemeCommon::init_smarty();
		$theme->assign('header', array(
			'distributor'=>__('Distributor'),
			'quantity'=>__('Quantity'),
			'quantity_info'=>__('Qty Info'),
			'price'=>__('Price')
			));    	
		$distros = array();
    	while ($row = $ret->FetchRow()) {
			$dist = Utils_RecordBrowserCommon::get_record('premium_warehouse_distributor', $row['distributor_id']);
    		$distros[] = array(
    			'distributor_name'=>$dist['name'],
    			'quantity'=>$row['quantity'],
    			'quantity_info'=>$row['quantity_info'],
				'price'=>Utils_CurrencyFieldCommon::format($row['price'],$row['price_currency'])
    		);
    	}
		$theme->assign('distros', $distros);
		Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_Wholesale','dist_qty_tooltip');
	}
	
    private static function get_processing_message_js($str, $type=0, $hide_details=false) {
    	$cla = 'notification';
    	if ($type==1) $cla = 'success';
    	if ($type==2) $cla = 'error';
    	$det_disp = ($hide_details?'none':'block');
    	return 'wholesale_processing_message("'.$str.'","'.$det_disp.'","'.$cla.'");';
    }
    
    /**
     * Displays a message in file scan legihtbox
     * Use this method in plugnis to inform the user or progress or errors encountered
     * Notice: use this method during downloading process, for file scan process use file_scan_message() instead 
     * 
     * @param string message text (must be already translated)
     * @param integer type of the message, 0 - notification, 1 - success announcement, 2 - error
     * @param bool true to hide progress details (numbers), false to show them
     */
    public static function file_download_message($str, $type=0, $hide_details=false) {
    	eval_js(self::get_processing_message_js($str, $type, $hide_details));
    }

    /**
     * Displays a message in file scan legihtbox
     * Use this method in plugnis to inform the user or progress or errors encountered
     * Notice: use this method during file scan process, for download process use file_download_message() instead 
     * 
     * @param string message text (must be already translated)
     * @param integer type of the message, 0 - notification, 1 - success announcement, 2 - error
     * @param bool true to hide progress details (numbers), false to show them
     */
    public static function file_scan_message($str, $type=0, $hide_details=false) {
    	print('<script>parent.'.self::get_processing_message_js($str, $type, $hide_details).'</script>');
    	flush();
    	@ob_flush();
    }
    
	public static function scan_file_processing($data) {
		eval_js('wholesale_leightbox_switch_to_info();');
	    $time = time();	    
		$dir = ModuleManager::get_data_dir('Premium_Warehouse_Wholesale');
		if(is_array($data)) {
		    $filename = array();
		    foreach($data as $k=>$f) {
		        $filename2 = $dir.'current_scan_'.$time.'_'.$k.'.tmp';
			@copy($f, $filename2);
			@unlink($f);
			$filename[] = $filename2;
		    }
		    $filename = implode(',',$filename);
		} else {
		    $filename = $dir.'current_scan_'.$time.'.tmp';
		    @copy($data, $filename);
		    @unlink($data);
		}
		eval_js('wholesale_create_iframe('.Utils_RecordBrowser::$last_record['id'].',"'.$filename.'");');
    }
    
    public static function scan_file_leightbox($rb) {
    	$form = $rb->init_module('Utils_FileUpload');
		$form->add_upload_element();
		$form->addElement('button',null,__('Upload'),$form->get_submit_form_href());
		ob_start();
		$rb->display_module($form, array(array('Premium_Warehouse_WholesaleCommon','scan_file_processing')));
    	$form_html = ob_get_clean();
    	
		$theme = Base_ThemeCommon::init_smarty();
		$fields = array(
			'total'=>__('Items in file'),
			'scanned'=>__('Items Scanned'),
			'available'=>__('Items Available'),
			'item_exist'=>__('Items found in the system'),
			'link_exist'=>__('Items scanned in the past'),
			'new_items_added'=>__('New Items'),
			'new_categories_added'=>__('New Categories'),
			'unknown'=>__('Unknown')
		);
		foreach ($fields as $k=>$v) 
			$theme->assign($k, $v);
		
		load_js('modules/Premium/Warehouse/Wholesale/scan_file_progress_reporting.js');
		load_js('modules/Premium/Warehouse/Wholesale/process_file.js');

		ob_start();
		Base_ThemeCommon::display_smarty($theme,'Premium_Warehouse_Wholesale','scan_status');
		$html = ob_get_clean();

		Libs_LeightboxCommon::display('wholesale_scan_file','<div id="wholesale_scan_file_progress" style="display:none;">'.$html.'</div><div id="wholesale_scan_file_form">'.$form_html.'</div>',__('Scan a file'));
    	
		Base_ActionBarCommon::add('folder', __('File scan'), 'class="lbOn" rel="wholesale_scan_file" onmouseup="wholesale_leightbox_switch_to_form();"');
    }
    
    public static function update_scan_status($total, $scanned, $available, $item_exist, $link_exist, $new_items_added, $new_categories_added) {
		static $time = 0;
		$new_time = microtime(true);
		if ($new_time-$time>1.5 || $total==$scanned) {
			$time = $new_time;
			if ($total===null) $total='"'.__('Unknown').'"';
			echo('<script>parent.update_wholesale_scan_status('.$total.','.$scanned.','.$available.','.$item_exist.','.$link_exist.','.$new_items_added.','.$new_categories_added.');</script>');
			flush();
			@ob_flush();
		}
    }

	public static function auto_update($dist) {
		$plugin = self::get_plugin($dist['plugin']);
		$params = $plugin->get_parameters();
		$i = 1;
		foreach ($params as $k=>$v) {
			$params[$k] = $dist['param'.$i];
			$i++;
		}
		$filename = $plugin->download_file($params, $dist);

		eval_js('leightbox_activate(\'wholesale_scan_file\');');

		if ($filename!==false) self::scan_file_processing($filename);
		return false;
	}
	
	public static function submit_distributor($values, $mode) {
		if (isset($values['plugin']) && is_numeric($values['plugin'])) {
			$plugin = self::get_plugin($values['plugin']);
			$params = $plugin->get_parameters();
		} else $params = array();

		switch ($mode) {
			case 'edit':
				$i = 1;
				foreach ($params as $k=>$v) {
					if ($values['param'.$i]=='[_password_dummy_]' && $v=='password') unset($values['param'.$i]);
					$i++;
				}
				break;
			case 'view':
				if (isset($plugin)) {
					self::$current_plugin = $plugin;
					if ($plugin->is_auto_download()) {
						if (isset($_REQUEST['wholesale_module_auto_update']) && $_REQUEST['wholesale_module_auto_update']=$values['id'])
							self::auto_update($values);
						Base_ActionBarCommon::add('search',__('Auto update'), Module::create_href(array('wholesale_module_auto_update'=>$values['id'])));
					}
				}
				break;
		}

		$i = 1;
		foreach ($params as $k=>$v) {
			if ($v=='password' && isset($values['param'.$i]) && $values['param'.$i]) switch ($mode) {
				case 'adding':
					$values['param'.$i] = '[_password_dummy_]';
				    break;
				case 'editing':
					$values['param'.$i] = '[_password_dummy_]';
				    break;
				case 'view':
					$values['param'.$i] = str_pad('', strlen($values['param'.$i]), '*');
				    break;
			}
			$i++;
		}
		return $values;
	}
	
	public static function get_change_parameters_labels_js($id) {
		$i = 1;
		$js = '';
		if (is_numeric($id)) {
			$plugin = self::get_plugin($id);
			$params = $plugin->get_parameters();
			foreach ($params as $k=>$v) {
				$js .= 	'if($("param'.$i.'"))$("param'.$i.'").type="'.$v.'";'.
						'if($("_param'.$i.'__label")){'.
							'$("_param'.$i.'__label").innerHTML="'.$k.'";'.
							'$("_param'.$i.'__label").up("tr").style.display="";'.
						'}';
				$i++;
			}
		}
		while($i<=6) {
			$js .= 'if($("_param'.$i.'__label"))$("_param'.$i.'__label").up("tr").style.display="none";';
			$i++;
		}
		return $js;
	}

	public static function change_parameters_labels($id) {
		eval_js(self::get_change_parameters_labels_js($id));
	}

	public static function QFfield_plugin(&$form, $field, $label, $mode, $default, $desc, $rb_obj) {
		self::change_parameters_labels($default);
		if ($mode!='view') {
			if (is_numeric($default)) {
				$vals = array($default);
				$where=' OR id=%d';
			} else {
				$vals = array();
				$where='';
			}
			$opts = array(''=>'---');
			$opts = $opts+DB::GetAssoc('SELECT id, name FROM premium_warehouse_wholesale_plugin WHERE active=1'.$where,$vals);
			load_js('modules/Premium/Warehouse/Wholesale/adjust_parameters.js');
			eval_js('Event.observe("plugin","change",adjust_parameters)');
			$form->addElement('select', $field, $label, $opts, array('id'=>$field));
			$form->setDefaults(array($field=>$default));
		} else {
			self::scan_file_leightbox($rb_obj);
			$form->addElement('static', $field, $label, DB::GetOne('SELECT name FROM premium_warehouse_wholesale_plugin WHERE id=%d', array($default)));
		}
	}

	public static function add_dest_qty_info($r, $str) {
		static $calculated = array();
		if (isset($calculated[$r['id']])) return $str;
		$calculated[$r['id']] = true;
		$d_qty = DB::GetAll('SELECT * FROM premium_warehouse_wholesale_items WHERE item_id=%d AND (quantity!=0 OR quantity_info!=%s)', array($r['id'], ''));
		if (empty($d_qty)) return $str;
		$tip = '<hr><table border=0 width="100%">';
		foreach ($d_qty as $v) {
			$dist_name = Utils_RecordBrowserCommon::get_value('premium_warehouse_distributor', $v['distributor_id'], 'name');
			$tip .= '<tr><td>'.$dist_name.'</td>'.
					'<td bgcolor="#FFFFFF" WIDTH=50 style="text-align:right;">'.
					$v['quantity'].($v['quantity_info']?' ('.$v['quantity_info'].')':'').
					'</td></tr>';
		}
		$str = preg_replace('/(tip=\".*?)(\")/', '$1'.htmlspecialchars($tip).'$2', $str);
		return '* '.$str;
	}
	
	public static function display_item_quantity($r, $nolink=false) {
		$res = Premium_Warehouse_Items_LocationCommon::display_item_quantity($r, $nolink);
		return self::add_dest_qty_info($r, $res);
	}
	
	public static function display_available_qty($r, $nolink=false) {
		$res = Premium_Warehouse_Items_OrdersCommon::display_available_qty($r, $nolink);
		return self::add_dest_qty_info($r, $res);
	}
	
    public static function menu() {
		if (Utils_RecordBrowserCommon::get_access('premium_warehouse_distributor','browse'))
			return array(_M('Inventory')=>array('__submenu__'=>1,_M('Distributors')=>array()));
		return array();
	}

	public static function watchdog_label($rid = null, $events = array(), $details = true) {
		return Utils_RecordBrowserCommon::watchdog_label(
				'premium_warehouse_distributor',
				__('Distributor'),
				$rid,
				$events,
				'name',
				$details
			);
	}
	
	public static function search_format($id) {
		if(!Utils_RecordBrowserCommon::get_access('premium_warehouse_distributor','browse')) return false;
		$row = Utils_RecordBrowserCommon::get_records('premium_warehouse_distributor',array('id'=>$id));
		if(!$row) return false;
		$row = array_pop($row);
		return Utils_RecordBrowserCommon::record_link_open_tag('premium_warehouse_distributor', $row['id']).__( 'Distributor (attachment) #%d, %s', array($row['id'], $row['name'])).Utils_RecordBrowserCommon::record_link_close_tag();
	}
	
	public static function QFfield_category_name(&$form, $field, $label, $mode, $default) {
		$form->addElement('text', $field, $label)->freeze();
		$form->setDefaults(array($field=>$default));
	}

	public static function QFfield_distributor_name(&$form, $field, $label, $mode, $default) {
		$rec = Utils_RecordBrowserCommon::get_record('premium_warehouse_distributor', $default);
		$form->addElement('select', $field, $label, array($default=>$rec['name']))->freeze();
		$form->setDefaults(array($field=>$default));
	}
	
	public static function display_epesi_cat_name($v, $nolink=false) {
		$ret = array();
		foreach($v['epesi_category'] as $c) {
			$cc = explode('/',$c);
			$ret2 = array();
			foreach($cc as $ccc) {
				$cat = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_categories',$ccc);
				$ret2[] = $cat['category_name'];
			}
			$ret[] = implode(' / ',$ret2);
		}
		return implode(', ',$ret);
	}
	
	public static function get_item_basic_info($v) {
		$def = array();
		foreach ($v['category'] as $d) {
			$next = Premium_Warehouse_ItemsCommon::automulti_format($d);
			if($next)
				$def[] = $next;
		}
		$cat = implode('<br/>',$def);
		$ret = array(
			'e_item_name'=>$v['item_name'],
			'e_category'=>$cat.'&nbsp;',
			'e_price'=>Utils_CurrencyFieldCommon::format($v['last_sale_price']),
			'e_manufacturer'=>CRM_ContactsCommon::company_format_default($v['manufacturer'],true).'&nbsp;',
			'e_mpn'=>$v['manufacturer_part_number'].'&nbsp;',
			'e_upc'=>$v['upc'].'&nbsp;'
		);
		return $ret;
	}
	
	public static function item_suggestbox($str) {
		$ss = explode(' ',$str);
		$crits = array();
		foreach($ss as $word) {
		    $crit = DB::Concat(DB::qstr('%'), DB::qstr($word), DB::qstr('%'));
		    $crits = Utils_RecordBrowserCommon::merge_crits($crits,array('(~"item_name'=>$crit, '|~"sku'=>$crit));
		}
		$rec = Utils_RecordBrowserCommon::get_records('premium_warehouse_items', $crits);
		$result = '<ul>';
    	if (empty($rec)) {
			$result .= '<li><span style="text-align:center;font-weight:bold;" class="informal">'.__('No records founds').'</span></li>';
    	} else {
			foreach ($rec as $k=>$v) {
				$data = array(
					$v['id']
				)+self::get_item_basic_info($v);
				$label = $v['sku'].': '.$v['item_name'];
				$result .= '<li><span style="display:none;">'.implode('__',$data).'</span><span class="informal">'.str_replace(' ','&nbsp;',$label).'</span></li>';
			}
		}
		$result .= '</ul>';
		return $result;
	}
	
	public static function cron() {
        return array('cron2'=>30);
    }

    public static function cron2() {
		$dists = Utils_RecordBrowserCommon::get_records('premium_warehouse_distributor',array('<last_update'=>date('Y-m-d 8:00:00',time()-3600*23)));
		$ret = '';
		foreach($dists as $dist) {
			if($dist['id']!=7) continue;
			$plugin = self::get_plugin($dist['plugin']);
			if(!$plugin->is_auto_download()) continue;
			$params = $plugin->get_parameters();
			$i = 1;
			foreach ($params as $k=>$v) {
				$params[$k] = $dist['param'.$i];
				$i++;
			}
			ob_start();
			$filename = @$plugin->download_file($params, $dist);
			ob_end_clean();
			if(!$filename) {
				$ret .= 'failed file download: '.$dist['name'].'<br>';
				continue;
			}
			ob_start();
			$res = @$plugin->update_from_file($filename, $dist, $params);
			if(is_array($filename))
			    foreach($filename as $filename2)
				@unlink($filename2);
			else
				@unlink($filename);
			ob_end_clean();
			if ($res===true) { 
				$ret .= 'updated: '.$dist['name'].'<br>';
				$time = time();
				Utils_RecordBrowserCommon::update_record('premium_warehouse_distributor', $dist['id'], array('last_update'=>$time));
			} else {
				$ret .= 'failed update: '.$dist['name'].'<br>';
			}
        	
        	//check fetched items
        	$r2 = DB::Execute('SELECT i.id,i.upc,c.f_company_name as manufacturer,i.manufacturer_part_number FROM premium_warehouse_wholesale_items i INNER JOIN company_data_1 c ON i.manufacturer=c.id WHERE thirdp is null OR thirdp=\'\' LIMIT 200');
        	while($row = $r2->FetchRow()) {
    	        $r3 = Premium_Warehouse_eCommerceCommon::check_3rd_party_item_data(isset($row['upc'])?$row['upc']:null,isset($row['manufacturer'])?$row['manufacturer']:null,isset($row['manufacturer_part_number'])?$row['manufacturer_part_number']:null);
    	        $val = array();
                if(!$r3)
                    $val[] = '<i>no data available</i>';
                foreach($r3 as $name=>$langs) {
                    $val[] = '<b>'.$name.'</b> - <i>'.implode(', ',$langs).'</i>';
                }
                DB::Execute('UPDATE premium_warehouse_wholesale_items SET thirdp=%s WHERE id=%d',array(implode('<br/>',$val),$row['id']));
    	    }
		}
		return $ret;
	}
}
?>
