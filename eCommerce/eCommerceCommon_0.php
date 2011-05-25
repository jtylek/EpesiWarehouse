<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * eCommerce
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_eCommerceCommon extends ModuleCommon {
	public static $plugin_path = 'modules/Premium/Warehouse/eCommerce/3rdp_plugins/';
    private static $curr_opts;

    public static function access_products($action, $param=null){
        $i = self::Instance();
        switch ($action) {
            case 'browse_crits':    return $i->acl_check('browse ecommerce');
            case 'browse':  if(!$i->acl_check('browse ecommerce')) return false;
                            $ret = array('position'=>false);
                            if(!Variable::get('ecommerce_item_descriptions')) {
                                $ret['product_name'] = false;
                                $ret['description'] = false;
                            }
                            return $ret;                            
            case 'view':    if (!$i->acl_check('view ecommerce')) return false;
                            $ret = array('position'=>false);
                            if(!Variable::get('ecommerce_item_descriptions')) {
                                $ret['product_name'] = false;
                                $ret['description'] = false;
                            }
                            return $ret;
            case 'clone':
            case 'add':
            case 'edit':    return $i->acl_check('edit ecommerce');
            case 'delete':  return $i->acl_check('delete ecommerce');
        }
        return false;
    }

    public static function access_parameters($action, $param=null){
        $i = self::Instance();
        switch ($action) {
            case 'browse_crits':    return $i->acl_check('browse ecommerce');
            case 'browse':  return $i->acl_check('browse ecommerce');
            case 'view':    if (!$i->acl_check('view ecommerce')) return false;
                            return array('position'=>false);
            case 'clone':
            case 'add':
            case 'edit':    return $i->acl_check('edit ecommerce');
            case 'delete':  return $i->acl_check('delete ecommerce');
        }
        return false;
    }

    public static function access_3rdp_info($action, $param=null){
        $i = self::Instance();
        switch ($action) {
            case 'browse_crits':    return $i->acl_check('browse ecommerce');
            case 'browse':  return true;
            case 'view':    if (!$i->acl_check('view ecommerce')) return false;
                            return array('position'=>false);
            case 'clone':
            case 'add':
            case 'edit':    return $i->acl_check('edit ecommerce');
            case 'delete':  return $i->acl_check('delete ecommerce');
        }
        return false;
    }

    public static function display_item_name($r, $nolink, $desc) {
        return Utils_RecordBrowserCommon::create_linked_label('premium_warehouse_items','item_name',$r['item_name'],$nolink);
    }

    public static function QFfield_description_language(&$form, $field, $label, $mode, $default, $desc, $rb_obj) {
        $opts = array(''=>'---')+Utils_CommonDataCommon::get_translated_array('Premium/Warehouse/eCommerce/Languages');
        if ($mode!='view') {
            $form->addElement('select', $field, $label, $opts, array('id'=>$field));
            $form->setDefaults(array($field=>$default));
        } else {
            $form->addElement('static', $field, $label, $opts[$default]);
        }
    }

    public static function display_parameter_label($r, $nolink, $desc) {
        $lang_code = Base_User_SettingsCommon::get('Base_Lang_Administrator','language');
        $id = Utils_RecordBrowserCommon::get_id('premium_ecommerce_parameter_labels', array('parameter', 'language'), array($r['id'], $lang_code));
        if (!is_numeric($id)) {
            $lan = Utils_CommonDataCommon::get_value('Premium/Warehouse/eCommerce/Languages/'.$lang_code);
            return Base_LangCommon::ts('Premium_eCommerce','Description in <b>%s</b> missing', array($lan?$lan:$lang_code));
        }
        return Utils_RecordBrowserCommon::get_value('premium_ecommerce_parameter_labels',$id,'label');
    }

    public static function display_parameter_group_label($r, $nolink, $desc) {
        $lang_code = Base_User_SettingsCommon::get('Base_Lang_Administrator','language');
        $id = Utils_RecordBrowserCommon::get_id('premium_ecommerce_param_group_labels', array('group', 'language'), array($r['id'], $lang_code));
        if (!is_numeric($id)) {
            $lan = Utils_CommonDataCommon::get_value('Premium/Warehouse/eCommerce/Languages/'.$lang_code);
            return Base_LangCommon::ts('Premium_eCommerce','Description in <b>%s</b> missing', array($lan?$lan:$lang_code));
        }
        return Utils_RecordBrowserCommon::get_value('premium_ecommerce_param_group_labels',$id,'label');
    }

    public static function display_description($r, $nolink, $desc) {
        $lang_code = Base_User_SettingsCommon::get('Base_Lang_Administrator','language');
        $id = Utils_RecordBrowserCommon::get_id('premium_ecommerce_descriptions', array('item_name', 'language'), array($r['item_name'], $lang_code));
        if (!is_numeric($id)) {
            $lan = Utils_CommonDataCommon::get_value('Premium/Warehouse/eCommerce/Languages/'.$lang_code);
            return Base_LangCommon::ts('Premium_eCommerce','Description in <b>%s</b> missing', array($lan?$lan:$lang_code));
        }
        return Utils_RecordBrowserCommon::get_value('premium_ecommerce_descriptions',$id,'short_description');
    }

    public static function display_product_name($r, $nolink, $desc) {
        $lang_code = Base_User_SettingsCommon::get('Base_Lang_Administrator','language');
        $id = Utils_RecordBrowserCommon::get_id('premium_ecommerce_descriptions', array('item_name', 'language'), array($r['item_name'], $lang_code));
        if (!is_numeric($id)) {
            $lan = Utils_CommonDataCommon::get_value('Premium/Warehouse/eCommerce/Languages/'.$lang_code);
            return Base_LangCommon::ts('Premium_eCommerce','Product name in <b>%s</b> missing', array($lan?$lan:$lang_code));
        }
        return  Utils_RecordBrowserCommon::record_link_open_tag('premium_ecommerce_products',$r['id'],$nolink).
                Utils_RecordBrowserCommon::get_value('premium_ecommerce_descriptions',$id,'display_name').
                Utils_RecordBrowserCommon::record_link_close_tag();
    }

    public static function display_sku($r, $nolink, $desc) {
        return Utils_RecordBrowserCommon::record_link_open_tag('premium_warehouse_items',$r['item_name'],$nolink).
                Utils_RecordBrowserCommon::get_value('premium_warehouse_items',$r['item_name'],'sku').
                Utils_RecordBrowserCommon::record_link_close_tag();
    }

    public static function items_crits() {
        return array();
    }

    public static function customer_crits() {
        return array('group'=>'custm');
    }

    public static function QFfield_password(&$form, $field, $label, $mode, $default) {
        if ($mode=='add' || $mode=='edit') {
            $form->addElement('password', $field, $label);
            $form->addRule($field, Base_LangCommon::ts('Premium_Warehouse_eCommerce','Field required'), 'required');
        } else {
            $form->addElement('static', $field, $label);
            $form->setDefaults(array($field=>'*****'));
        }
    }

    public static function display_password($r, $nolink=false) {
        return '*****';
    }

    public static function users_addon_parameters($r) {
//      $ret = Utils_RecordBrowserCommon::get_records('premium_ecommerce_users',array('contact'=>$r['id']));
        if(!in_array('custm',$r['group']))
            return array('show'=>false);
        return array('show'=>true, 'label'=>'eCommerce user');
    }

    public static function prices_addon_parameters($r) {
        if(!Variable::get('ecommerce_item_prices'))
            return array('show'=>false);
        return array('show'=>true, 'label'=>'Prices');
    }
    public static function parameters_addon_parameters($r) {
        if(!Variable::get('ecommerce_item_parameters'))
            return array('show'=>false);
        return array('show'=>true, 'label'=>'Parameters');
    }
    public static function descriptions_addon_parameters($r) {
        if(!Variable::get('ecommerce_item_descriptions'))
            return array('show'=>false);
        return array('show'=>true, 'label'=>'Descriptions');
    }

    public static function access_users($action, $param=null){
        return self::access_parameters($action,$param);
        }

    public static function submit_user($values, $mode) {
        switch ($mode) {
            case 'add':
            case 'edit':
                $values['password'] = md5($values['password']);
                break;
        }
        return $values;
    }

    private static $page_opts = array(''=>'---','1'=>'Top menu above logo','2'=>'Top menu under logo','5'=>'Hidden');

    public static function QFfield_page_type(&$form, $field, $label, $mode, $default) {
        if ($mode=='add' || $mode=='edit') {
            $form->addElement('select', $field, $label, self::$page_opts, array('id'=>$field));
            if ($mode=='edit') $form->setDefaults(array($field=>$default));
        } else {
            $form->addElement('static', $field, $label);
            $form->setDefaults(array($field=>self::$page_opts[$default]));
        }
    }

    public static function display_page_type($r, $nolink=false) {
        return self::$page_opts[$r['type']];
    }

    private static $subpage_as_opts = array(1=>"List (name, description)",2=>"List (name, description, photo)",3=>'News (name, description, photo)',4=>'Gallery (name, picture)');

    public static function QFfield_subpages(&$form, $field, $label, $mode, $default) {
        if ($mode=='add' || $mode=='edit') {
            $form->addElement('select', $field, $label, self::$subpage_as_opts, array('id'=>$field));
            $form->addRule($field,'Field required','required');
            if ($mode=='edit') $form->setDefaults(array($field=>$default));
        } else {
            $form->addElement('static', $field, $label);
            $form->setDefaults(array($field=>self::$subpage_as_opts[$default]));
        }
    }

    public static function display_subpages($r, $nolink=false) {
        return self::$subpage_as_opts[$r['show_subpages_as']];
    }

    private static $products_as_opts = array(0=>"List (name, description, photo)",1=>"List with subcategories (name, description, photo)",4=>'Gallery (name, picture)');

    public static function QFfield_products_as(&$form, $field, $label, $mode, $default) {
        if($default!=1 && $default!=4)
            $default=1;
        if ($mode=='add' || $mode=='edit') {
            $form->addElement('select', $field, $label, self::$products_as_opts, array('id'=>$field));
            $form->addRule($field,'Field required','required');
            if ($mode=='edit') $form->setDefaults(array($field=>$default));
        } else {
            $form->addElement('static', $field, $label);
            $form->setDefaults(array($field=>self::$products_as_opts[$default]));
        }
    }

    public static function display_products_as($r, $nolink=false) {
        if($r['show_as']!=1 && $r['show_as']!=4 && $r['show_as']!==0)
            $r['show_as'] = 0;
        return self::$products_as_opts[$r['show_as']];
    }

    public static function parent_page_crits($v, $rec) {
        if(!$rec || !isset($rec['id']))
            return array();
        return array('!id'=>$rec['id']);
    }

    public static function QFfield_currency(&$form, $field, $label, $mode, $default) {
        self::init_currency();
        if ($mode=='add' || $mode=='edit') {
            $form->addElement('select', $field, $label, self::$curr_opts, array('id'=>$field));
            if ($mode=='edit') $form->setDefaults(array($field=>$default));
        } else {
            $form->addElement('static', $field, $label);
            $form->setDefaults(array($field=>self::$curr_opts[$default]));
        }
    }

    public static function display_currency($r, $nolink=false) {
        self::init_currency();
        return self::$curr_opts[$r['currency']];
    }

    public static function init_currency() {
        if(!isset(self::$curr_opts))
            self::$curr_opts = DB::GetAssoc('SELECT id, code FROM utils_currency');
    }

    public static function get_currencies() {
        self::init_currency();
        return self::$curr_opts;
    }

    public static function menu() {
		if (self::access_parameters('browse'))
			return array('Warehouse'=>array(
				'__submenu__'=>1,
				'eCommerce'=>array('__submenu__'=>1,
					'Express publish'=>array('__function__'=>'fast_fill'),
					'Comments queue'=>array('__function__'=>'comments'),
					'Newsletter'=>array('__function__'=>'newsletter'),
					'Products'=>array(),
					'Stats'=>array('__function__'=>'stats'))));
		return array();
    }

    public static function get_quickcarts() {
        static $qcs;
        if(!isset($qcs))
                $qcs = DB::GetCol('SELECT path FROM premium_ecommerce_quickcart');
        return $qcs;
    }

    public static function copy_attachment($id,$rev,$file,$original) {
        $qcs = self::get_quickcarts();
        $ext = strrchr($original,'.');
        if(preg_match('/^\.(jpg|jpeg|gif|png|bmp)$/i',$ext)) {
                $th1 = Utils_ImageCommon::create_thumb($file,100,100);
            $th2 = Utils_ImageCommon::create_thumb($file,200,200);
            $file = Utils_ImageCommon::create_thumb($file,800,600);
            $file = $file['thumb'];
        }
        foreach($qcs as $q) {
            copy($file,$q.'/files/epesi/'.$id.'_'.$rev.$ext);
            if(isset($th1)) {
                copy($th1['thumb'],$q.'/files/100/epesi/'.$id.'_'.$rev.$ext);
                copy($th2['thumb'],$q.'/files/200/epesi/'.$id.'_'.$rev.$ext);
            }
        }
        if(isset($th1)) {
            @unlink($th1['thumb']);
            @unlink($th2['thumb']);
            @unlink($file);
        }
    }

    public static function copy_banner($file) {
        $qcs = self::get_quickcarts();
        $b = basename($file);
        foreach($qcs as $q) {
            @copy($file,$q.'/files/epesi/banners/'.$b);
        }
    }

	public static function get_plugin($arg) {
		static $plugins = array();
		if (isset($plugins[$arg])) return $plugins[$arg];

		static $interface_included = false;
		if (!$interface_included)
			require_once('modules/Premium/Warehouse/eCommerce/interface.php');

		if (is_numeric($arg)) {
			$id = $arg;
			$filename = DB::GetOne('SELECT filename FROM premium_ecommerce_3rdp_plugin WHERE id=%d', array($arg));
		} else {
			$filename = $arg;
			$id = DB::GetOne('SELECT id FROM premium_ecommerce_3rdp_plugin WHERE filename=%s', array($arg));
		}
		if (is_file(self::$plugin_path.basename($filename).'.php')) {
			require_once(self::$plugin_path.basename($filename).'.php');
			$class = 'Premium_Warehouse_eCommerce_3rdp__Plugin_'.$filename;
			if (!class_exists($class))
				trigger_error('Warning: invalid plugin in file '.$filename.'.php<br>', E_USER_ERROR);
			return $plugins[$id] = $plugins[$filename] = new $class();
		}
		return null;
	}

	public static function submit_3rdp_info($values, $mode) {
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
        return self::submit_position($values, $mode, 'premium_ecommerce_3rdp_info');
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
							'$("_param'.$i.'__label").innerHTML="'.Base_LangCommon::ts('Premium_Warehouse_eCommerce',$k).'";'.
							'$("_param'.$i.'__label").parentNode.parentNode.style.display="";'.
						'}';
				$i++;
			}
		}
		while($i<=6) {
			$js .= 'if($("_param'.$i.'__label"))$("_param'.$i.'__label").parentNode.parentNode.style.display="none";';
			$i++;
		}
		return $js;
	}

	public static function change_parameters_labels($id) {
		eval_js(self::get_change_parameters_labels_js($id));
	}

	public static function QFfield_3rdp_plugin(&$form, $field, $label, $mode, $default, $desc, $rb_obj) {
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
			$opts = $opts+DB::GetAssoc('SELECT id, name FROM premium_ecommerce_3rdp_plugin WHERE active=1'.$where,$vals);
			load_js('modules/Premium/Warehouse/eCommerce/adjust_parameters.js');
			eval_js('Event.observe("plugin","change",adjust_parameters)');
			$form->addElement('select', $field, $label, $opts, array('id'=>$field));
			$form->setDefaults(array($field=>$default));
		} else {
			$form->addElement('static', $field, $label, DB::GetOne('SELECT name FROM premium_ecommerce_3rdp_plugin WHERE id=%d', array($default)));
		}
	}
    public static function display_3rdp_name($v, $nolink=false) {
		return Utils_RecordBrowserCommon::create_linked_label_r('premium_ecommerce_3rdp_info', 'Name', $v, $nolink);
	}

	public static function scan_for_3rdp_info_plugins() {
		$dir = scandir(self::$plugin_path);
		DB::Execute('UPDATE premium_ecommerce_3rdp_plugin SET active=2');
		foreach ($dir as $file) {
			if ($file=='..' || $file=='.' || !preg_match('/\.php$/i',$file)) continue;
			$filename = basename($file, '.php');
			$plugin = self::get_plugin($filename);
			if ($plugin) {
				$name = $plugin->get_name();
				$id = DB::GetOne('SELECT id FROM premium_ecommerce_3rdp_plugin WHERE filename=%s', array($filename));
				if ($id===false || $id==null) {
					DB::Execute('INSERT INTO premium_ecommerce_3rdp_plugin (name, filename, active) VALUES (%s, %s, 1)', array($name, $filename));
				} else {
					DB::Execute('UPDATE premium_ecommerce_3rdp_plugin SET active=1, name=%s WHERE id=%d', array($name, $id));
				}
			}
		}
		DB::Execute('UPDATE premium_ecommerce_3rdp_plugin SET active=0 WHERE active=2');
		return false;
	}

    public static function get_3rd_party_item_data($item_id,$verbose=true) {
        $item = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$item_id);
        $plugins = Utils_RecordBrowserCommon::get_records('premium_ecommerce_3rdp_info',array(),array(),array('position'=>'ASC'));
        $langs = array_keys(Utils_CommonDataCommon::get_array('Premium/Warehouse/eCommerce/Languages'));
        $langs_ok = array();
        foreach($plugins as $plugin) {
            if(!$langs) break;
            $pl = self::get_plugin($plugin['plugin']);
    		$params = $pl->get_parameters();
	    	$i = 1;
		    foreach ($params as $k=>$v) {
			    $params[$k] = $plugin['param'.$i];
    			$i++;
	    	}
            $ret = $pl->download($params,$item,$langs,$verbose); //TODO wprowadzic pozycje pluginow (priorytet)
            if(is_array($ret)) {
                $langs_ok = array_merge($langs_ok,$ret);
                $langs = array_diff($langs,$ret);
            } elseif($ret) {
                $langs_ok = array_merge($langs_ok,$langs);
                break;
            }
        }
        if(!empty($langs_ok)) {
            return Epesi::alert("Successfully downloaded product data for languages: ".implode(', ',$langs_ok).".");
        }
        Epesi::alert("There is no data about this item on 3rd party servers.");
    }

    public static function check_3rd_party_item_data($upc,$man,$mpn) {
        $plugins = Utils_RecordBrowserCommon::get_records('premium_ecommerce_3rdp_info',array(),array(),array('position'=>'ASC'));
        $langs = array_keys(Utils_CommonDataCommon::get_array('Premium/Warehouse/eCommerce/Languages'));
        $ret = array();
        foreach($plugins as $plugin) {
            if(!$langs) break;
            $pl = self::get_plugin($plugin['plugin']);
    		$params = $pl->get_parameters();
	    	$i = 1;
		    foreach ($params as $k=>$v) {
			    $params[$k] = $plugin['param'.$i];
    			$i++;
	    	}
            $ret_check = $pl->check($params,$upc,$man,$mpn,$langs); //TODO wprowadzic pozycje pluginow (priorytet)
            if(is_array($ret_check)) {
                $ret[$plugin['name']] = $ret_check;
                $langs = array_diff($langs,$ret_check);
            } elseif($ret) {
                $ret[$plugin['name']] = $langs;
                break;
            }
        }
        return $ret;
    }

    public static function get_3rd_party_info_addon_parameters($r) {
        if(DB::GetOne('SELECT 1 FROM premium_ecommerce_3rdp_plugin WHERE active=1')) {
            Base_ActionBarCommon::add('add','3rd party',Module::create_href(array('get_3rd_party_item_data'=>1),'Getting data from 3rd party servers - please wait.'));
            if(isset($_REQUEST['get_3rd_party_item_data'])) {
                self::get_3rd_party_item_data($r['item_name']);
                unset($_REQUEST['get_3rd_party_item_data']);
            }
        }
        return array('show'=>false);
    }
    
    private static $orders_rec;

    public static function orders_get_record() {
        return self::$orders_rec['id'];
    }

    public static function orders_addon_parameters($r) {
        if(!isset(self::$orders_rec)) {
            $ret = Utils_RecordBrowserCommon::get_records('premium_ecommerce_orders',array('transaction_id'=>$r['id']));
            if(!$ret)
                return array('show'=>false);
            self::$orders_rec = array_pop($ret);
        }
        return array('show'=>true, 'label'=>'eCommerce');
    }

    public static function submit_products_position($values, $mode) {
        return self::submit_position($values, $mode, 'premium_ecommerce_products');
    }
    public static function submit_boxes_position($values, $mode) {
        return self::submit_position($values, $mode, 'premium_ecommerce_boxes');
    }
    public static function submit_pages_position($values, $mode) {
        return self::submit_position($values, $mode, 'premium_ecommerce_pages');
    }
    public static function submit_polls_position($values, $mode) {
        return self::submit_position($values, $mode, 'premium_ecommerce_polls');
    }
    public static function submit_parameters_position($values, $mode) {
        return self::submit_position($values, $mode, 'premium_ecommerce_parameters');
    }
    public static function submit_parameter_groups_position($values, $mode) {
        return self::submit_position($values, $mode, 'premium_ecommerce_parameter_groups');
    }

    public static function submit_position($values, $mode, $recordset) {
        switch ($mode) {
            case 'add':
            case 'restore':
                $values['position'] = Utils_RecordBrowserCommon::get_records_count($recordset);
                break;
            case 'delete':
                DB::Execute('UPDATE '.$recordset.'_data_1 SET f_position=f_position-1 WHERE f_position>%d',array($values['position']));
                break;
        }
        return $values;
    }

    public static function toggle_publish($id,$v) {
        Utils_RecordBrowserCommon::update_record('premium_ecommerce_products',$id,array('publish'=>$v?1:0));
    }

    public static function toggle_recommended($id,$v) {
        Utils_RecordBrowserCommon::update_record('premium_ecommerce_products',$id,array('recommended'=>$v?1:0));
    }

    public static function toggle_always_on_stock($id,$v) {
        Utils_RecordBrowserCommon::update_record('premium_ecommerce_products',$id,array('always_on_stock'=>$v?1:0));
    }

    public static function toggle_exclude_compare_services($id,$v) {
        Utils_RecordBrowserCommon::update_record('premium_ecommerce_products',$id,array('exclude_compare_services'=>$v?1:0));
    }

    public static function publish_warehouse_item($id,$icecat=true) {
        Utils_RecordBrowserCommon::new_record('premium_ecommerce_products',array('item_name'=>$id,'publish'=>1,'available'=>1));
        if($icecat)
                Premium_Warehouse_eCommerceCommon::get_3rd_party_item_data($id,false);
    }


    public static function warehouse_item_actions($r, $gb_row) {
        if(isset($_REQUEST['publish_warehouse_item']) && $r['id']==$_REQUEST['publish_warehouse_item']) {
            self::publish_warehouse_item($r['id']);
            unset($_REQUEST['publish_warehouse_item']);
        }

        $tip = '<table>';
        $icon = 'available.png';
        $action = '';

        $on = '<span class="checkbox_on" />';
        $off = '<span class="checkbox_off" />';

        $recs = Utils_RecordBrowserCommon::get_records('premium_ecommerce_products',array('item_name'=>$r['id']));
        $quantity = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$r['id'],'>quantity'=>0));
        if(empty($recs)) {
            $icon = 'notavailable.png';
                $tip .= '<tr><td colspan=2>'.Base_LangCommon::ts('Premium_Warehouse_eCommerce','eCommerce item doesn\'t exist.').'</td></tr>';
            $action = Module::create_href(array('publish_warehouse_item'=>$r['id']));
        } else {
            $rec = array_pop($recs);

            if(isset($_REQUEST['toggle_publish']) && $rec['id']==$_REQUEST['toggle_publish'] && ($_REQUEST['publish_value']==0 || $_REQUEST['publish_value']==1)) {
            $rec['publish'] = $_REQUEST['publish_value'];
            self::toggle_publish($rec['id'],$rec['publish']);
            unset($_REQUEST['toggle_publish']);
            }

            if(!$rec['publish']) {
                $icon = 'notpublished.png';
            } elseif(empty($quantity) || !$r['category'])
            $icon = 'published.png';
            $action = Module::create_href(array('toggle_publish'=>$rec['id'],'publish_value'=>$rec['publish']?0:1));
                $tip .= '<tr><td>'.Base_LangCommon::ts('Premium_Warehouse_eCommerce','Published').'</td><td>'.($rec['publish']?$on:$off).'</td></tr>';
        }

        $tip .= '<tr><td>'.Base_LangCommon::ts('Premium_Warehouse_eCommerce','Assigned category').'</td><td>'.($r['category']?$on:$off).'</td></tr>';
        $tip .= '<tr><td>'.Base_LangCommon::ts('Premium_Warehouse_eCommerce','Available in warehouse').'</td><td>'.(empty($quantity)?$off:$on).'</td></tr>';
        $tip .= '</table>';


        $gb_row->add_action($action,'',$tip,Base_ThemeCommon::get_template_file('Premium_Warehouse_eCommerce',$icon));
    }

    public static function QFfield_poll_votes(&$form, $field, $label, $mode, $default) {
        $form->addElement('static', $field, $label, $default);
    }

    private static function get_payment_channel($sys,$chn) {
        static $aPay;
        if(!isset($aPay)) {
        $aPay = array();
        $aPay[1] = array();
        $aPay[1][0] = 'Credit card';
        $aPay[1][1] = 'mTransfer (mBank)';
        $aPay[1][2] = 'Płacę z Inteligo (PKO BP Inteligo)';
        $aPay[1][3] = 'Multitransfer (MultiBank)';
        $aPay[1][4] = 'DotPay Transfer (DotPay.pl)';
        $aPay[1][6] = 'Przelew24 (Bank Zachodni WBK)';
        $aPay[1][7] = 'ING OnLine (ING Bank Śląski)';
        $aPay[1][8] = 'Sez@m (Bank Przemysłowo-Handlowy S.A.)';
        $aPay[1][9] = 'Pekao24 (Bank Pekao S.A.)';
        $aPay[1][10] = 'MilleNet (Millennium Bank)';
        $aPay[1][12] = 'PayPal';
        $aPay[1][13] = 'Deutsche Bank PBC S.A.';
        $aPay[1][14] = 'Kredyt Bank S.A. - KB24 Bankowość Elektroniczna';
        $aPay[1][15] = 'PKO BP (konto Inteligo)';
        $aPay[1][16] = 'Lukas Bank';
        $aPay[1][17] = 'Nordea Bank Polska';
        $aPay[1][18] = 'Bank BPH (usługa Przelew z BPH)';
        $aPay[1][19] = 'Citibank Handlowy';
        $aPay[4] = array();
        $aPay[4]['m'] = 'mTransfer - mBank';
        $aPay[4]['n'] = 'MultiTransfer - MultiBank';
        $aPay[4]['w'] = 'BZWBK - Przelew24';
        $aPay[4]['o'] = 'Pekao24Przelew - BankPekao';
        $aPay[4]['i'] = 'Płace z Inteligo';
        $aPay[4]['d'] = 'Płac z Nordea';
        $aPay[4]['p'] = 'Płac z PKO BP';
        $aPay[4]['h'] = 'Płac z BPH';
        $aPay[4]['g'] = 'Płac z ING';
        $aPay[4]['c'] = 'Credit card';
        }
        if(!isset($aPay[$sys][$chn])) return '---';
        return $aPay[$sys][$chn];
    }

    public static function display_payment_channel($r) {
        $r2 = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders',$r['transaction_id']);
        return self::get_payment_channel($r2['payment_type'],$r['payment_channel']);
    }

    public static function QFfield_payment_channel(&$form, $field, $label, $mode, $default,$dupa,$parent_rb) {
        $ord = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders',$parent_rb->record['transaction_id']);
        $form->addElement('static', $field, $label, self::get_payment_channel($ord['payment_type'],$default));
    }

    public static function display_promotion_shipment_discount($r) {
        if(!$r['promotion_shipment_discount']) return '---';
        $r2 = Utils_RecordBrowserCommon::get_record('premium_warehouse_items_orders',$r['transaction_id']);
        list($cc,$curr) = Utils_CurrencyFieldCommon::get_values($r2['shipment_cost']);
        return Utils_CurrencyFieldCommon::format($r['promotion_shipment_discount'],$curr).' ('.Base_LangCommon::ts('Premium_Warehouse_eCommerce','"Shipment Cost" is already discounted').')';
    }

    public static function QFfield_promotion_shipment_discount(&$form, $field, $label, $mode, $default,$dupa,$parent_rb) {
        $form->addElement('static', $field, $label, self::display_promotion_shipment_discount($parent_rb->record));
    }

    public static function display_payment_realized($r) {
        return Base_LangCommon::ts('Premium_Warehouse_eCommerce',$r['payment_realized']?'Yes':'No');
    }

    public static function QFfield_payment_realized(&$form, $field, $label, $mode, $default,$args) {
        if(isset($_REQUEST['payment_realized'])) {
            $id = self::orders_get_record();
            if($_REQUEST['payment_realized']) $val=1;
            else $val=0;
            Utils_RecordBrowserCommon::update_record('premium_ecommerce_orders',$id,array('payment_realized'=>$val));
            unset($_REQUEST['payment_realized']);
            $default = $val;
        }
        $form->addElement('static', $field, $label, $default?'<a '.Module::create_confirm_href(Base_LangCommon::ts('Premium_Warehouse_eCommerce','Mark this record as not paid?'),array('payment_realized'=>0)).'><span class="checkbox_on" /></a>':'<a '.Module::create_href(array('payment_realized'=>1)).'><span '.Utils_TooltipCommon::open_tag_attrs('Click to mark as paid').' class="checkbox_off" /></a>');
    }

    private static $banner_opts = array(0=>'Top', 1=>'Menu left');

    public static function QFfield_banner_type(&$form, $field, $label, $mode, $default) {
        if ($mode=='add' || $mode=='edit') {
            $form->addElement('select', $field, $label, self::$banner_opts, array('id'=>$field));
            if ($mode=='edit') $form->setDefaults(array($field=>$default));
        } else {
            $form->addElement('static', $field, $label);
            $form->setDefaults(array($field=>self::$banner_opts[$default]));
        }
    }

    public static function display_banner_type($r, $nolink=false) {
        return self::$banner_opts[$r['type']];
    }

    public static function QFfield_freeze_int(&$form, $field, $label, $mode, $default,$args) {
        $form->addElement('text', $field, $label)->freeze();
        $form->addRule($field, Base_LangCommon::ts('Premium_Warehouse_eCommerce','Only numbers are allowed.'), 'numeric');
        $form->setDefaults(array($args['id']=>$default));
    }

    public static function display_banner_file($r) {
        if(preg_match('/\.swf$/i',$r['file']))
            $ret = '<object type="application/x-shockwave-flash" data="'.$r['file'].'" width="'.$r['width'].'" height="'.$r['height'].'"><param name="bgcolor" value="'.$r['color'].'" /><param name="movie" value="'.$r['file'].'" /></object>';
        else
            $ret = '<img src="'.$r['file'].'" style="width:'.$r['width'].'px;height:'.$r['height'].'px;" alt="" />';
        return Utils_TooltipCommon::create($ret,$r['link']);
    }

    public static function QFfield_banner_file(&$form, $field, $label, $mode, $default,$args) {
        if($mode=='add' || $mode=='edit') {
            print('<iframe name="banner_upload_iframe" src="" style="display:none"></iframe>');
            $fu = new HTML_QuickForm('banner_upload', 'post', 'modules/Premium/Warehouse/eCommerce/bannerUpload.php', 'banner_upload_iframe');
            $fu->addElement('file', 'file', '',array('id'=>'banner_upload_field','style'=>'position: absolute; z-index: 3','onChange'=>'form.submit()'));
            $fu->display();

            $st = $form->createElement('static','info','','<div id="banner_upload_info">&nbsp;</div>');
            $bt = $form->createElement('static','uploader','','<div id="banner_upload_slot" style="height: 24px"></div>');
            $h = $form->createElement('text',null,'',array('id'=>'banner_upload_file','style'=>'display: none'));

            $form->addGroup(array($bt,$st,$h),$field,$label);
            if($mode=='edit' && $form->exportValue($field)=='')
                $h->setValue($default);
            if($mode=='add')
                $form->addRule($field,'Field required','required');
        load_js('modules/Premium/Warehouse/eCommerce/banner.js');
        } else {
        if(preg_match('/\.swf$/i',$default))
        $r = '<object type="application/x-shockwave-flash" data="'.$default.'" width="300" height="120"><param name="movie" value="'.$default.'" /></object>';
        else
        $r = '<img src="'.$default.'" style="max-width:300px;max-height:120px">';
            $form->addElement('static',$field,$label,$r);
        }
    }

    public static function banners_processing($v,$mode) {
        if($mode=='view' || $mode=='editing' || $mode=='adding') return $v;
        $f = DATA_DIR.'/Premium_Warehouse_eCommerce/banners/'.basename($v['file']);
        if($f!=$v['file']) {
            rename($v['file'],$f);
            $v['file'] = $f;
        }
    Premium_Warehouse_eCommerceCommon::copy_banner($f);
        //cleanup old files
        $ls = scandir(DATA_DIR.'/Premium_Warehouse_eCommerce/banners/tmp');
        $rt=microtime(true);
        foreach($ls as $file) {
            $reqs = array();
            if(!preg_match('/^([0-9]+)\.([0-9]+)\.([a-z0-9]+)$/i',$file, $reqs)) continue;
            $rtc = $reqs[1].'.'.$reqs[2];
            if(floatval($rt)-floatval($rtc)>86400) //files older then 24h
                @unlink(DATA_DIR.'/Premium_Warehouse_eCommerce/banners/tmp/'.$file);
        }

        return $v;
    }

    public static function display_product_name_short($r) {
        $rec = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$r['item_name']);
        return $rec['item_name'];
    }

    public static function adv_related_products_params() {
        return array('cols'=>array(),
            'format_callback'=>array('Premium_Warehouse_eCommerceCommon','display_product_name_short'));
    }

    public static function related_products_crits($arg, $r){
        if (isset($r['id']))
            return array('!id'=>$r['id']);
        return array();
    }

    public static function display_related_product_name($r, $nolink=true) {
        $ret = array();
        if(isset($r['related_products']))
        foreach($r['related_products'] as $p) {
            $rr = Utils_RecordBrowserCommon::get_record('premium_ecommerce_products',$p);
            $name = self::display_product_name_short($rr);
            if($nolink)
                $ret[] = $name;
            else
                $ret[] = Utils_RecordBrowserCommon::record_link_open_tag('premium_ecommerce_products',$p).$name.Utils_RecordBrowserCommon::record_link_close_tag();
        }
        return implode($ret,', ');
    }

    public static function display_category_available_languages($r, $nolink) {
        $rr = Utils_RecordBrowserCommon::get_records('premium_ecommerce_cat_descriptions',array('category'=>$r['id']),array('language'));
        $ret = array();
        foreach($rr as $r) {
            $ret[] = $r['language'];
        }
        sort($ret);
        return implode(', ',$ret);
    }

    public static function QFfield_online_order(&$form, $field, $label, $mode, $default) {
        $form->addElement('checkbox', $field, $label)->freeze();
        $form->setDefaults(array($field=>$default));
    }

    public static function admin_caption() {
        return 'eCommerce';
    }

    public static function applet_caption() {
        return "eCommerce orders";
    }

    public static function applet_info() {
        $html="Displays eCommerce orders.";
        return $html;
    }

    public static function applet_settings() {
        $opts = array(-1=>'New Online Order', 2=>'Order Received', 3=>'Payment Confirmed', 4=>'Order Confirmed', 5=>'On Hold', 6=>'Order Ready to Ship', 7=>'Shipped', 20=>'Delivered', 21=>'Canceled', 22=>'Missing','active'=>'[Active]');
        return array_merge(Utils_RecordBrowserCommon::applet_settings(),
            array(
                array('name'=>'settings_header','label'=>'Settings','type'=>'header'),
                array('name'=>'status','label'=>'Transaction status','type'=>'select','default'=>-1,'rule'=>array(array('message'=>'Field required', 'type'=>'required')),'values'=>$opts),
                array('name'=>'my','label'=>'Only my and not assigned','type'=>'checkbox','default'=>0)
                ));
    }

    public static function applet_info_format($r){
        return Utils_TooltipCommon::format_info_tooltip(array('Contact'=>$r['first_name'].' '.$r['last_name'],
                    'Company'=>$r['company_name'],'Phone'=>$r['phone']),'Premium_Warehouse_eCommerce');
    }

    public static function submit_warehouse_order($values, $mode) {
        if ($mode=='edit' && $values['transaction_type']==1 && $values['online_order']) {
            $txt = '';
            switch($values['status']) {
                case 2:
                    $erec = Utils_RecordBrowserCommon::get_records('premium_ecommerce_orders',array('transaction_id'=>$values['id']));
                    if($erec && is_array($erec) && count($erec)==1) {
                        $erec = array_shift($erec);
                        $txt = Variable::get('ecommerce_order_rec_email_'.$erec['language'],false);
                        if(!$txt)
                            $txt = Variable::get('ecommerce_order_rec_email');
                        $title = Variable::get('ecommerce_order_rec_email_'.$erec['language'].'S',false);
                        if(!$title)
                            $title = Variable::get('ecommerce_order_rec_emailS');
                    } else {
                        $txt = Variable::get('ecommerce_order_rec_email');
                        $title = Variable::get('ecommerce_order_rec_emailS');
                    }
                    break;
                case 7:
                    $erec = Utils_RecordBrowserCommon::get_records('premium_ecommerce_orders',array('transaction_id'=>$values['id']));
                    if($erec && is_array($erec) && count($erec)==1) {
                        $erec = array_shift($erec);
                        $txt = Variable::get('ecommerce_order_shi_email_'.$erec['language'],false);
                        if(!$txt)
                            $txt = Variable::get('ecommerce_order_shi_email');
                        $title = Variable::get('ecommerce_order_shi_email_'.$erec['language'].'S',false);
                        if(!$title)
                            $title = Variable::get('ecommerce_order_shi_emailS');
                    } else {
                        $txt = Variable::get('ecommerce_order_shi_email');
                        $title = Variable::get('ecommerce_order_shi_emailS');
                    }
                    $txt = str_replace('__TRACKING_INFO__',$values['tracking_info'],$txt);
                    $txt = str_replace('__SHIPMENT_NO__',$values['shipment_no'],$txt);
                    break;
            }
            if($txt) {
                $it_tmp = Utils_RecordBrowserCommon::get_records('premium_warehouse_items_orders_details',array('transaction_id'=>$values['id']),array('item_name'));
                $items = '<ul>';
                foreach($it_tmp as $it) {
                    $itt = Utils_RecordBrowserCommon::get_record('premium_warehouse_items',$it['item_name']);
                    $items .= '<li>'.$itt['item_name'].'</li>';
                }
                $items .= '</ul>';
                $txt = str_replace('__ITEMS__',$items,$txt);

                $sm = Base_ThemeCommon::init_smarty();
                $sm->assign('txt',$txt);
                $sm->assign('contact_us_title',Base_LangCommon::ts('Premium_Warehouse_eCommerce','Contact us'));
                if($erec) {
                    $contactus = Variable::get('ecommerce_contactus_'.$erec['language'],false);
                    if(!$contactus)
                        $contactus = Variable::get('ecommerce_contactus');
                    $email = $erec['email'];
                } else {
                    $contactus = Variable::get('ecommerce_contactus');
                    if(is_numeric($values['contact'])) {
                        $contact = CRM_ContactsCommon::get_contact($values['contact']);
                        if(isset($contact['email']) && $contact['email'])
                            $email = $contact['email'];
                        elseif(is_numeric($values['company'])) {
                            $company = CRM_ContactsCommon::get_company($values['company']);
                            if(isset($company['email']) && $company['email'])
                                $email = $company['email'];
                        }
                    }
                }
                if(!isset($email)) return null;

                $sm->assign('contact_us',$contactus);
                ob_start();
                Base_ThemeCommon::display_smarty($sm, 'Premium_Warehouse_eCommerce','mail');
                $mail = ob_get_clean();

                $title .= ' - id '.$values['id'];

                Base_MailCommon::send($email,$title,$mail,null,null,true);
            }
        }
        return null;//don't modify values
    }

    public static function QFfield_related_products(&$form, $field, $label, $mode, $default,$y,$x) {
        if ($mode=='edit' || $mode=='add') {
            $form->addElement('automulti', $field, $label, array('Premium_Warehouse_eCommerceCommon', 'automulti_search'), array($x->record), array('Premium_Warehouse_eCommerceCommon','automulti_format'));
            $form->setDefaults(array($field=>$default));
        } else {
            $form->addElement('static', $field, $label, self::display_related_product_name(array('related_products'=>$default),false));
        }
    }

    public static function automulti_search($arg,$r) {
        $ret = DB::GetAssoc('SELECT ep.id, wp.f_item_name FROM premium_ecommerce_products_data_1 ep INNER JOIN premium_warehouse_items_data_1 wp ON ep.f_item_name=wp.id WHERE ep.active=1 AND wp.active=1 AND (wp.f_item_name LIKE CONCAT("%%",%s,"%%") OR wp.f_sku LIKE CONCAT("%%",%s,"%%"))'.(isset($r['id'])?' AND ep.id!='.$r['id']:'').' ORDER BY wp.f_item_name LIMIT 10',array($arg,$arg));
        return $ret;
    }

    public static function automulti_format($id) {
        return DB::GetOne('SELECT wp.f_item_name FROM premium_ecommerce_products_data_1 ep INNER JOIN premium_warehouse_items_data_1 wp ON ep.f_item_name=wp.id WHERE ep.id=%d',array($id));
    }
}

?>
