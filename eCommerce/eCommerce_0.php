<?php
/**
 * WARNING: This is a commercial software
 * Please see the included license.html file for more information
 *
 * eCommerce
 *
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-premium
 * @subpackage warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_eCommerce extends Module {
	private $rb;
	private $recordset;
	private $caption;
	
	public function admin() {
		if($this->is_back()) {
			if($this->parent->get_type()=='Base_Admin')
				$this->parent->reset();
			else
				location(array());
			return;
		}
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());

		$buttons = array();
//		$icon = Base_ThemeCommon::get_template_file($name,'icon.png');
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'setup_3rd_party_plugins')).'>'.__('3rd party info plugins').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'prices')).'>'.__('Automatic prices').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'availability')).'>'.__('Availability').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'banners')).'>'.__('Banners').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'boxes')).'>'.__('Boxes').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'contactus_page')).'>'.__('Contact us').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'features')).'>'.__('Features').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'home_page')).'>'.__('Home Page').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'compare_services')).'>'.__('Links for compare services').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'order_status_change_email_page')).'>'.__('Order status change e-mails').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'pages')).'>'.__('Pages').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'parameters')).'>'.__('Parameters').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'parameter_groups')).'>'.__('Parameter Groups').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'payments_carriers')).'>'.__('Payments & Carriers').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'polls')).'>'.__('Polls').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'promotion_codes')).'>'.__('Promotion Codes').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'QC_dirs')).'>'.__('Quickcart settings').'</a>',
						'icon'=>null);
		$buttons[]= array('link'=>'<a '.$this->create_callback_href(array($this,'rules_page')).'>'.__('Rules & Policies').'</a>',
						'icon'=>null);
		$theme = $this->pack_module('Base/Theme');
		$theme->assign('header', __('eCommerce settings'));
		$theme->assign('buttons', $buttons);
		$theme->display();
	}
	

	public function body() {
		$this->recordset = 'products';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_products');
		$this->rb->set_defaults(array('publish'=>1,'status'=>1));
		$this->rb->set_additional_actions_method(array($this, 'actions_for_position'));
		$this->rb->force_order(array('position'=>'ASC'));

		$opts = Premium_Warehouse_eCommerceCommon::get_categories();
		$this->rb->set_custom_filter('item_name',array('type'=>'select','label'=>__('Category'),'args'=>$opts,'trans_callback'=>array('Premium_Warehouse_eCommerceCommon', 'category_filter')));

//		$cols = array('item_name'=>array('name'=>'Item name')
//		        );
		$this->display_module($this->rb);//,array(array('position'=>'ASC'),array(),$cols));
	}

	public function compare_services() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
	
 		$m = $this->init_module('Utils/GenericBrowser',null,'t1');
 		$m->set_table_columns(array(array('name'=>__('Site'),'width'=>30),
							  array('name'=>'Link','width'=>70)));
		$site = __('http://replace.with.quickcart.url/');
		$a = array('Old Ceneo.pl XML'=>'ceneo',
		        'Ceneo.pl'=>'ceneo2',
			'Nokaut.pl'=>'nokaut',
			'Skapiec.pl'=>'skapiec',
			'Handelo.pl'=>'handelo',
			'Szoker.pl'=>'szoker',
			'Cenus.pl'=>'cenus',
			'Zakupy.Onet.pl'=>'onet');
		foreach($a as $k=>$url) {
			$m->add_row($k,	$site.'?sLang=pl&p=compare-'.$url);
			$m->add_row($k.' ('.__('includes out of stock items').')',	$site.'?sLang=pl&p=compare-'.$url.'&outOfStock=1');
		}
		$a = array('Froogle.com'=>'froogle',
			'Shopping.com'=>'shopping');
		foreach($a as $k=>$url) {
			$m->add_row($k,	$site.'?sLang=en&p=compare-'.$url);
			$m->add_row($k.' ('.__('includes out of stock items').')',	$site.'?sLang=en&p=compare-'.$url.'&outOfStock=1');
		}
 		$this->display_module($m);

		return true;
	}

	public function parameters() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
	
		$this->recordset = 'parameters';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_parameters');
		$this->rb->set_additional_actions_method(array($this, 'actions_for_position'));
		$this->rb->force_order(array('position'=>'ASC'));
		$this->display_module($this->rb);

		return true;
	}

	public function parameter_groups() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());

		$this->recordset = 'parameter_groups';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_parameter_groups');
		$this->rb->set_additional_actions_method(array($this, 'actions_for_position'));
		$this->rb->force_order(array('position'=>'ASC'));
		$this->display_module($this->rb);

		return true;
	}

	public function boxes() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
	
		$this->recordset = 'boxes';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_boxes');
		$this->rb->set_defaults(array('publish'=>1,'language'=>Base_LangCommon::get_lang_code()));
		$this->rb->set_additional_actions_method(array($this, 'actions_for_position'));
		$this->rb->force_order(array('position'=>'ASC'));
		$this->display_module($this->rb);

		return true;
	}
	
	public function banners() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
	
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_banners');
		$this->rb->set_defaults(array('publish'=>1,'views_limit'=>0,'views'=>0,'clicks'=>0,'width'=>480,'height'=>80,'color'=>'#000000','language'=>Base_LangCommon::get_lang_code()));
		$this->display_module($this->rb);

    		return true;
	}
	
	public function polls() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
		
		print('<h2>'.__('Last active poll is displayed.').'</h2>');
	
		$this->recordset = 'polls';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_polls');
		$this->rb->set_defaults(array('publish'=>1,'language'=>Base_LangCommon::get_lang_code()));
		$this->rb->set_additional_actions_method(array($this, 'actions_for_position'));
		$this->rb->force_order(array('position'=>'ASC'));
		$this->display_module($this->rb);

		return true;
	}
	
	public function promotion_codes() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
		
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_promotion_codes');
		$this->rb->force_order(array('expiration'=>'DESC'));
		$this->display_module($this->rb);

		return true;
	}
	
	public function clear_votes($poll) {
		DB::Execute('UPDATE premium_ecommerce_poll_answers_data_1 SET f_votes=0 WHERE f_poll=%d',array($poll));
	}
	
	public function poll_answers_addon($arg) {
		Base_ActionBarCommon::add('delete', __('Clear votes'), $this->create_callback_href(array($this,'clear_votes'),array($arg['id'])));
		
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_poll_answers');
		$order = array(array('poll'=>$arg['id']), array('poll'=>false,'answer'=>true,'votes'=>true), array('answer'=>'ASC'));
		$rb->set_defaults(array('poll'=>$arg['id'],'votes'=>0));
		$rb->set_header_properties(array(
			'answer'=>array('width'=>50, 'wrapmode'=>'nowrap'),
			'votes'=>array('width'=>10, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function availability() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
	
		$this->recordset = 'availability';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_availability');
		$this->display_module($this->rb);

		return true;
	}

	public function pages() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
	
		$this->recordset = 'pages';
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_pages');
		$this->rb->set_defaults(array('publish'=>1,'type'=>2));
		$this->rb->set_additional_actions_method(array($this, 'actions_for_position'));
		$this->rb->force_order(array('position'=>'ASC'));
		$this->display_module($this->rb);

		return true;
	}

	public function payments_carriers() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
	
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_payments_carriers');
		$this->rb->set_defaults(array('percentage_of_amount'=>0));
		$this->display_module($this->rb);
		
		return true;
	}

	public function actions_for_position($r, $gb_row) {
		$tab = 'premium_ecommerce_'.$this->recordset;
		if(isset($_REQUEST['pos_action']) && $r['id']==$_REQUEST['pos_action'] && is_numeric($_REQUEST['old']) && is_numeric($_REQUEST['new'])) {
		    $crits = $this->rb->get_module_variable('crits_stuff',array());
		    if($_REQUEST['new']>0) {
			    $pos = Utils_RecordBrowserCommon::get_records($tab,array_merge($crits,array('>position'=>$_REQUEST['old'])),array('position'), array('position'=>'ASC'),1);
		    } else {
			    $pos = Utils_RecordBrowserCommon::get_records($tab,array_merge($crits,array('<position'=>$_REQUEST['old'])),array('position'), array('position'=>'DESC'),1);		    
		    }
		    if($pos) {
		    	$pos = array_shift($pos);
		    	$pos = $pos['position'];
		    	$recs = Utils_RecordBrowserCommon::get_records($tab,array('position'=>$pos), array('id'));
		    	foreach($recs as $rr)
				Utils_RecordBrowserCommon::update_record($tab,$rr['id'],array('position'=>$_REQUEST['old']));
    		    	Utils_RecordBrowserCommon::update_record($tab,$r['id'],array('position'=>$pos));
		    	location(array());
		    } else {
		    	Epesi::alert(__('This item is already on top/bottom'));
		    }
		}
		if($r['position']>0)
		    $gb_row->add_action(Module::create_href(array('pos_action'=>$r['id'],'old'=>$r['position'],'new'=>0)),'move-up');
		static $max;
		if(!isset($max))
		    $max = Utils_RecordBrowserCommon::get_records_count($tab);
		if($r['position']<$max-1)
    		    $gb_row->add_action(Module::create_href(array('pos_action'=>$r['id'],'old'=>$r['position'],'new'=>1)),'move-down');
	}

	public function parameter_labels_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_parameter_labels');
		$order = array(array('parameter'=>$arg['id']), array('parameter'=>false,'language'=>true,'label'=>true), array('language'=>'ASC'));
		$rb->set_defaults(array('parameter'=>$arg['id'],'language'=>Base_LangCommon::get_lang_code()));
		$rb->set_header_properties(array(
			'language'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'label'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function parameter_group_labels_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_param_group_labels');
		$order = array(array('group'=>$arg['id']), array('group'=>false,'language'=>true,'label'=>true), array('language'=>'ASC'));
		$rb->set_defaults(array('group'=>$arg['id'],'language'=>Base_LangCommon::get_lang_code()));
		$rb->set_header_properties(array(
			'language'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'label'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function availability_labels_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_availability_labels');
		$order = array(array('availability'=>$arg['id']), array('availability'=>false,'language'=>true,'label'=>true), array('language'=>'ASC'));
		$rb->set_defaults(array('availability'=>$arg['id'],'language'=>Base_LangCommon::get_lang_code()));
		$rb->set_header_properties(array(
			'language'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'label'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}
	
	public function descriptions_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_descriptions');
		$order = array(array('item_name'=>$arg['item_name']), array('item_name'=>false), array('language'=>'ASC'));
		$rb->set_defaults(array('item_name'=>$arg['item_name'],'language'=>Base_LangCommon::get_lang_code()));
		$rb->set_header_properties(array(
			'language'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'description'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function item_cat_labels_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_item_cat_labels');
		$order = array(array('item_name'=>$arg['item_name']), array('item_name'=>false), array('language'=>'ASC'));
		$rb->set_defaults(array('item_name'=>$arg['item_name'],'language'=>Base_LangCommon::get_lang_code()));
		$rb->set_header_properties(array(
			'language'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'description'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function cat_descriptions_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_cat_descriptions');
		$order = array(array('category'=>$arg['id']), array('category'=>false), array('language'=>'ASC'));
		$rb->set_defaults(array('category'=>$arg['id'],'language'=>Base_LangCommon::get_lang_code()));
		$rb->set_header_properties(array(
			'language'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'description'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function parameters_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_products_parameters');
		$order = array(array('item_name'=>$arg['item_name']), array('item_name'=>false), array('language'=>'ASC','parameter'=>'ASC'));
		$rb->set_defaults(array('item_name'=>$arg['item_name'],'language'=>Base_LangCommon::get_lang_code()));
		$rb->set_header_properties(array(
			'parameter'=>array('wrapmode'=>'nowrap'),
			'value'=>array('wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}
	
	public function subpages_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_pages');
		$order = array(array('parent_page'=>$arg['id']), array(), array('page_name'=>'ASC'));
		$rb->set_defaults(array('parent_page'=>$arg['id'],'publish'=>1,'type'=>2));
//		$rb->set_header_properties(array(
//			'language'=>array('width'=>10, 'wrapmode'=>'nowrap'),
//			'description'=>array('width'=>50, 'wrapmode'=>'nowrap')
//									));
		$this->display_module($rb,$order,'show_data');
	}

	public function pages_info_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_pages_data');
		$order = array(array('page'=>$arg['id']), array('page'=>false), array('language'=>'ASC'));
		$rb->set_defaults(array('page'=>$arg['id'],'language'=>Base_LangCommon::get_lang_code()));
		$rb->set_header_properties(array(
			'language'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'name'=>array('wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function prices_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_prices');
		$order = array(array('item_name'=>$arg['item_name']), array('item_name'=>false), array('currency'=>'ASC'));
		$rb->set_defaults(array('item_name'=>$arg['item_name']));
		$rb->set_header_properties(array(
			'currency'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'price'=>array('wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}

	public function orders_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_orders');
		//$order = array(array('transaction_id'=>$arg['id']), array('transaction_id'=>false));
		$ord_id = Premium_Warehouse_eCommerceCommon::orders_get_record();
		$this->display_module($rb,array('view',$ord_id,null,false),'view_entry');
		if(Base_AclCommon::i_am_admin())
    		Base_ActionBarCommon::add('edit', __('Edit ecommerce'), $this->create_callback_href(array($this,'edit_ecommerce_order'),$ord_id));		
	}
	
	public function edit_ecommerce_order($id) {
        $x = ModuleManager::get_instance('/Base_Box|0');
        if (!$x) trigger_error('There is no base box module instance',E_USER_ERROR);
	    $x->push_main('Utils/RecordBrowser','view_entry',array('edit', $id, array(), true),array('premium_ecommerce_orders'));
	}
	
	public function contactus_page() {
		return $this->edit_variable_with_lang(__('Contact us'),'ecommerce_contactus');
	}
	
	public function order_status_change_email_page() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
		
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_emails');
		$this->display_module($this->rb);		

        return true;	    
	}
	
	public function rules_page() {
		return $this->edit_variable_with_lang(__('Rules and policies'),'ecommerce_rules');
	}

	public function home_page() {
		return $this->edit_variable_with_lang(__('Home'),'ecommerce_home');
	}

	private function edit_variable_with_lang($header,$v) {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
		
		print('<h1>'.$header.'</h1>'.__('Choose language to edit:').'<ul>');

		$langs = Utils_CommonDataCommon::get_array('Premium/Warehouse/eCommerce/Languages');
		print('<li><a '.$this->create_callback_href(array($this,'edit_variable'),array($header,$v)).'>default (if translation is available)</a></li>');
		foreach($langs as $k=>$name) {
			print('<li><a '.$this->create_callback_href(array($this,'edit_variable'),array($header,$v.'_'.$k)).'>'.$name.'</a></li>');
		}
		print('</ul>');
		return true;
	}

	public function edit_variable($header, $v) {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
	
		$f = $this->init_module('Libs/QuickForm');
		
		$f->addElement('header',null,$header);
		
		$fck = & $f->addElement('ckeditor', 'content', __('Content'));
		$fck->setFCKProps('800','300',true);
		
		$f->setDefaults(array('content'=>Variable::get($v,false)));

		Base_ActionBarCommon::add('save',__('Save'),$f->get_submit_form_href());
		
		if($f->validate()) {
			$ret = $f->exportValues();
			$content = str_replace("\n",'',$ret['content']);
			Variable::set($v,$content);
			Base_StatusBarCommon::message(__('Page saved'));
			return false;
		}
		$f->display();	
		return true;
	}

	public function edit_variable_mail($header, $v) {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
	
		$f = $this->init_module('Libs/QuickForm');
		
		$f->addElement('header',null,$header);

		$f->addElement('text', 'subject', __('Subject'),array('maxlength'=>64));
		
		$fck = & $f->addElement('ckeditor', 'content', __('Content'));
		$fck->setFCKProps('800','300',true);
		
		$f->setDefaults(array('content'=>Variable::get($v,false),'subject'=>Variable::get($v.'S',false)));

		Base_ActionBarCommon::add('save',__('Save'),$f->get_submit_form_href());
		
		if($f->validate()) {
			$ret = $f->exportValues();
			$content = str_replace("\n",'',$ret['content']);
			Variable::set($v,$content);
			Variable::set($v.'S',strip_tags($ret['subject']));
			Base_StatusBarCommon::message(__('Page saved'));
			return false;
		}
		$f->display();	
		return true;
	}

	public function QC_dirs() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
		Base_ActionBarCommon::add('add',__('Add'), $this->create_callback_href(array($this,'add_quickcart')));
	
		$gb = $this->init_module('Utils/GenericBrowser',null,'qc_list');

		$gb->set_table_columns(array(array('name'=>__('Path'), 'order'=>'path')));

		$query = 'SELECT path FROM premium_ecommerce_quickcart';
		$query_qty = 'SELECT count(*) FROM premium_ecommerce_quickcart';

		$ret = $gb->query_order_limit($query, $query_qty);
		
		if($ret)
			while(($row=$ret->FetchRow())) {
			    $r = $gb->get_new_row();
			    $r->add_data($row['path']);
			    $r->add_action($this->create_confirm_callback_href(__('Are you sure you want to delete this record?'),array($this,'delete_quickcart'),$row['path']),'delete');
			    $r->add_action($this->create_callback_href(array($this,'quickcart_settings'),$row['path']),'edit','Settings');
			}

		$this->display_module($gb);

		return true;
	}
	
	public function quickcart_settings($path) {
		if($this->is_back()) return false;

		if(!is_writable($path.'/config/general.php')) {
			Epesi::alert('Config file not writable: '.$path.'/config/general.php');
			return false;
		}			

		$form = $this->init_module('Libs/QuickForm');

		$form->addElement('header', null, __('QuickCart settings: %s',array($path)));
		
		$files = scandir($path.'/config');
		$langs = Utils_CommonDataCommon::get_array('Premium/Warehouse/eCommerce/Languages');
		foreach($files as $f) {
			if(!preg_match('/^(.{2,3})\.php$/i',$f,$reqs))
				continue;
            $code = $reqs[1];
			if(in_array($code.'.gif',$files) && in_array('epesi_'.$code.'.php',$files)) {
				if(!is_writable($path.'/config/epesi_'.$code.'.php')) {
					Epesi::alert('Config file not writable: '.$path.'/config/epesi_'.$code.'.php');
					unset($langs[$code]);
					continue;
				}
				global $config;
				$config = array();
				require_once($path.'/config/'.$code.'.php');
				if(isset($config['language']) && $config['language']!=$code)
					$langs[$code] = $code;
			} else {
				unset($langs[$code]);
			}
		}

		$form->addElement('select', 'default_lang', __('Default language'),$langs);
		$form->addRule('default_lang', __('Field required'), 'required');
		$form->addElement('multiselect', 'available_lang', __('Available languages'),$langs);
		$form->addRule('available_lang', __('At least one language must be available'), 'required');
		$form->addRule(array('default_lang','available_lang'), __('Default language must be one of quickcart available languages'), 'callback',array($this,'quickcart_check_default_lang'));

		$form->addElement('text', 'email', __('Shop e-mail'));
		$form->addRule('email', __('Invalid e-mail address'), 'email');

		$form->addElement('text', 'products_list', __('Number of products displayed on page'));
		$form->addRule('products_list', __('This field should be numeric'), 'numeric');
		$form->addRule('products_list', __('Field required'), 'required');

		$form->addElement('text', 'news_list', __('Number of news (subpages) displayed on page'));
		$form->addRule('news_list', __('This field should be numeric'), 'numeric');
		$form->addRule('news_list', __('Field required'), 'required');

		$form->addElement('text', 'time_diff', __('Difference between your local time and server time in hours'));
		$form->addRule('time_diff', __('This field should be numeric'), 'numeric');
		$form->addRule('time_diff', __('Field required'), 'required');

		$form->addElement('select','default_image_size',__('Thumbnails size'),array(0=>__('100 x 100'),1=>__('200 x 200')));

		$form->addElement('checkbox', 'text_size', __('Text resize buttons'));
		$form->addElement('checkbox', 'site_map_products', __('Display products on sitemap page'));

		$form->addElement('header',null,__('External services settings'));

		$form->addElement('text', 'skapiec_shop_id', __('Skąpiec shop ID'));
		$form->addRule('skapiec_shop_id', __('This field should be numeric'), 'numeric');

		$form->addElement('text', 'allpay_id', __('Allpay ID'));
		$form->addRule('allpay_id', __('This field should be numeric'), 'numeric');

		$form->addElement('text', 'przelewy24_id', __('Przelewy24 ID'));
		$form->addRule('przelewy24_id', __('This field should be numeric'), 'numeric');

		$form->addElement('text', 'platnosci_id', __('Platnosci ID'));
		$form->addRule('platnosci_id', __('This field should be numeric'), 'numeric');
		$form->addElement('text', 'platnosci_pos_auth_key', __('Platnosci pos auth key'));
		$form->addRule('platnosci_pos_auth_key', __('This field should be numeric'), 'numeric');
		$form->addElement('text', 'platnosci_key1', __('Platnosci key 1'));
		$form->addElement('text', 'platnosci_key2', __('Platnosci key 2'));
		$form->addElement('text', 'epesi_payments_url', __('Epesi Payments module URL'));

		$form->addElement('text', 'zagiel_id', __('Zagiel ID'));
		$form->addRule('zagiel_id', __('This field should be numeric'), 'numeric');
		$form->addElement('text', 'zagiel_min_price', __('Zagiel minimal price'));
		$form->addRule('zagiel_min_price', __('This field should be numeric'), 'numeric');

		$form->addElement('text', 'paypal_email', __('Paypal email'));
		$form->addRule('paypal_email', __('Invalid e-mail address'), 'email');

        $form->addElement('header', 'ups', __('UPS rates fetching (please fill all fields to enable this feature)'));
		$form->addElement('text', 'ups_accesskey', __('UPS Access Key'));
		$form->addElement('text', 'ups_username', __('UPS Username'));
		$form->addElement('password', 'ups_password', __('UPS Password'));
		$form->addElement('text', 'ups_shipper_number', __('UPS Shipper Number'));
		$form->addElement('commondata', 'ups_src_country', __('Your Country'), 'Countries', array('empty_option'=>true));
		$form->addElement('text', 'ups_src_zip', __('Your ZIP'));
		$form->addElement('select', 'ups_weight_unit', __('Weight Unit'), array('KGS'=>'KGS','LBS'=>'LBS'));

		$config = array();
		@include_once($path.'/config/epesi.php');
		$form->setDefaults($config);
		
		$currencies = DB::GetAssoc('SELECT code, code FROM utils_currency WHERE active=1');
		foreach($langs as $code=>$l) {
			$form->addElement('header',null,__('Language: %s',array($l)));
			$form->addElement('select',$code.'-currency_symbol',__('Currency'),$currencies);
			$form->addRule($code.'-currency_symbol',__('Field required'),'required');
			
			$form->addElement('text', $code.'-delivery_free', __('Price, after which the order gets sent for free to the customer'));
			$form->addRule($code.'-delivery_free', __('This field should be numeric'), 'numeric');
			$form->addRule($code.'-delivery_free',__('Field required'),'required');
			
			$form->addElement('text', $code.'-title', __('Title'));
			$form->addRule($code.'-title',__('Field required'),'required');

			$form->addElement('text', $code.'-slogan', __('Slogan'));
			$form->addElement('textarea', $code.'-description', __('Description'));
			$form->addElement('textarea', $code.'-keywords', __('Keywords'));
			$form->addElement('textarea', $code.'-foot_info', __('Foot'));
			
			$config = array();
			$config2 = array();
			@include_once($path.'/config/epesi_'.$code.'.php');
			foreach($config as $k=>$v) {
				$config2[$code.'-'.$k] = $v;
			}
			$form->setDefaults($config2);
		}

		if($form->validate()) {
			$data_dir = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/'.DATA_DIR;
			$vals = $form->exportValues();
			Premium_Warehouse_eCommerceCommon::write_configs($path, $vals);
			return false;
		} else $form->display();
	
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
		Base_ActionBarCommon::add('save',__('Save'), $form->get_submit_form_href());
		
    		return true;
	}
	
	public function quickcart_check_default_lang($x) {
		return strpos($x[1],$x[0])!==false;
	}
	
	public function add_quickcart() {
		if($this->is_back()) return false;
	
		$form = $this->init_module('Libs/QuickForm');

		$form->addElement('header', null, __('Add quickcart(epesi version) binding'));

		$form->addElement('text', 'path', __('Path'));
		$form->addRule('path', __('A path must be between 3 and 255 chars'), 'rangelength', array(3,255));
		$form->registerRule('check_path','callback','check_path','Premium_Warehouse_eCommerce');
		$form->addRule('path', __('Invalid path or files directory not writable'), 'check_path');
		$form->addRule('path', __('Field required'), 'required');

		if($form->validate()) {
		    Premium_Warehouse_eCommerceCommon::register_qc($form->exportValue('path'));
		    return false;
		} else $form->display();

		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
		Base_ActionBarCommon::add('save',__('Save'), $form->get_submit_form_href(true,__('creating thumbnails, please wait')));
		
    		return true;
	}
	
	private $manufacturers;
	public function fast_fill() {
		$qf = $this->init_module('Libs/QuickForm');
		$qf->addElement('hidden','id',null,array('id'=>'icecat_prod_id'));
		$qf->addElement('hidden','item_name',null,array('id'=>'icecat_prod_nameh'));
		$qf->addElement('static',null,__('Item Name'),'<div id="icecat_prod_name" />');
		$qf->addElement('text','upc',__('UPC'),array('id'=>'icecat_prod_upc'));
		$qf->addElement('text','product_code',__('Product Code'),array('id'=>'icecat_prod_code'));
		$qf->addElement('text','manufacturer_part_number',__('Part Number'),array('id'=>'icecat_prod_part_num'));

		$companies = CRM_ContactsCommon::get_companies(array('group'=>array('manufacturer')),array('company_name'),array('company_name'=>'ASC'));
		$this->manufacturers = array(''=>'---');
		foreach($companies as $c) {
			$this->manufacturers[$c['id']] = $c['company_name'];
		}
		$qf->addElement('select','manufacturer',__('Manufacturer'),$this->manufacturers,array('id'=>'icecat_prod_manuf'));

		$qf->addElement('checkbox','skip',__('Publish without getting information data'),'',array('id'=>'icecat_prod_skip'));
        $qf->addElement('static', '3rd party', __('Available data'),'<iframe id="3rdp_info_frame" style="width:300px; height:100px;border:0px"></iframe>');
		
		$qf->addElement('submit',null,__('Zapisz'));
		$qf->addFormRule(array($this,'check_fast_fill'));
		
		if($qf->validate()) {
			eval_js('leightbox_deactivate(\'fast_fill_lb\');');
			$vals = $qf->exportValues();
			Utils_RecordBrowserCommon::update_record('premium_warehouse_items',$vals['id'],array('upc'=>$vals['upc'],'product_code'=>$vals['product_code'],'manufacturer_part_number'=>$vals['manufacturer_part_number'],'manufacturer'=>$vals['manufacturer']));
		   	Premium_Warehouse_eCommerceCommon::publish_warehouse_item($vals['id'],!(isset($vals['skip']) && $vals['skip']));
		}

		Libs_LeightboxCommon::display('fast_fill_lb',$this->get_html_of_module($qf),'Express fill');

		$this->rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items');
		$this->rb->set_default_order(array('item_name'=>'ASC'));
		
		$this->rb->set_button(false);
		$this->rb->disable_watchdog();
		$this->rb->disable_actions(array('delete'));
					
		$cols = array('quantity_on_hand'=>false,'quantity_en_route'=>false,'available_qty'=>false,'reserved_qty'=>false,'dist_qty'=>false,
				'quantity_sold'=>false,'vendor'=>false,'manufacturer'=>true,'product_code'=>true,'upc'=>true,'gross_price'=>false,'manufacturer_part_number'=>true);
			
		$this->rb->set_header_properties(array(
						'manufacturer'=>array('width'=>25, 'wrapmode'=>'nowrap'),
						'manufacturer_part_number'=>array('name'=>__('Part Number'), 'width'=>15, 'wrapmode'=>'nowrap'),
						'product_code'=>array('width'=>15, 'wrapmode'=>'nowrap'),
						'upc'=>array('width'=>20, 'wrapmode'=>'nowrap'),
						'item_type'=>array('width'=>5, 'wrapmode'=>'nowrap'),
//						'gross_price'=>array('name'=>'Price','width'=>10, 'wrapmode'=>'nowrap'),
						'item_name'=>array('wrapmode'=>'nowrap'),
						'sku'=>array('width'=>10, 'wrapmode'=>'nowrap')
						));

  		$this->rb->set_additional_actions_method(array($this,'fast_fill_actions'));
		
		$crits = array('!id'=>Utils_RecordBrowserCommon::get_possible_values('premium_ecommerce_products','item_name'));
		$this->display_module($this->rb, array(array(),$crits,$cols));
//		Utils_RecordBrowserCommon::merge_crits(array('upc'=>'','(manufacturer_part_number'=>'', '|manufacturer'=>''),array('(product_code'=>'', '|manufacturer'=>''))
	}

	public function check_fast_fill($arg) {
		if(isset($arg['skip']) && $arg['skip']) return array();
		if(!isset($arg['upc'])) $arg['upc'] = '';
		if(!isset($arg['manufacturer'])) $arg['manufacturer'] = '';
		if(!isset($arg['product_code'])) $arg['product_code'] = '';
		if(!isset($arg['item_name'])) $arg['item_name'] = '';
		if(!isset($arg['manufacturer_part_number'])) $arg['manufacturer_part_number'] = '';
		if(!isset($arg['id']) || !is_numeric($arg['id'])) return array('upc'=>__('Invalid request without ID. Hacker?'));
		if(empty($arg['upc']) && 
		    (empty($arg['manufacturer']) || empty($arg['product_code'])) && 
		    (empty($arg['manufacturer']) || empty($arg['manufacturer_part_number']))
		    ) {
		    	eval_js('$(\'icecat_prod_id\').value=\''.$arg['id'].'\';'.
					'$(\'icecat_prod_name\').innerHTML=\''.addcslashes($arg['item_name'],'\'\\').'\';'.
					'$(\'icecat_prod_nameh\').value=\''.addcslashes($arg['item_name'],'\'\\').'\';'.
					'$(\'icecat_prod_upc\').value=\''.addcslashes($arg['upc'],'\'\\').'\';'.
					'$(\'icecat_prod_code\').value=\''.addcslashes($arg['product_code'],'\'\\').'\';'.
					'$(\'icecat_prod_part_num\').value=\''.addcslashes($arg['manufacturer_part_number'],'\'\\').'\';'.
					'$(\'icecat_prod_manuf\').value=\''.addcslashes($arg['manufacturer'],'\'\\').'\';');

			return array('upc'=>'<span id="icecat_prod_err">'.__('Please fill manufacturer and product code, or manufacturer and part number, or UPC, or skip gettin information data.').'</span>');
		}
		return array();
	}
	
	public function fast_fill_actions($r, $gb_row) {
		$gb_row->add_action(Libs_LeightboxCommon::get_open_href('fast_fill_lb').' id="icecat_button_'.$r['id'].'"','edit',__('Click here to fill required data'));
		$gb_row->add_js('Event.observe(\'icecat_button_'.$r['id'].'\',\'click\',function() {'.
					'$(\'icecat_prod_id\').value=\''.$r['id'].'\';'.
					'$(\'icecat_prod_name\').innerHTML=\''.addcslashes($r['item_name'],'\'\\').'\';'.
					'$(\'icecat_prod_nameh\').value=\''.addcslashes($r['item_name'],'\'\\').'\';'.
					'$(\'icecat_prod_upc\').value=\''.addcslashes($r['upc'],'\'\\').'\';'.
					'$(\'icecat_prod_code\').value=\''.addcslashes($r['product_code'],'\'\\').'\';'.
					'$(\'icecat_prod_part_num\').value=\''.addcslashes($r['manufacturer_part_number'],'\'\\').'\';'.
					'$(\'icecat_prod_manuf\').value=\''.addcslashes($r['manufacturer'],'\'\\').'\';'.
					'$(\'icecat_prod_skip\').checked=false;'.
					'$(\'3rdp_info_frame\').src=\'modules/Premium/Warehouse/eCommerce/3rdp.php?'.http_build_query(array('upc'=>$r['upc'],'mpn'=>$r['manufacturer_part_number'],'man'=>isset($this->manufacturers[$r['manufacturer']])?$this->manufacturers[$r['manufacturer']]:'')).'\';'.
					'var err=$(\'icecat_prod_err\');if(err!=null)err.parentNode.parentNode.removeChild(err.parentNode);'.
					'})');
	}
	
	public function features() {
		if($this->is_back()) return false;
	
		$form = $this->init_module('Libs/QuickForm');

		$form->addElement('header', null, __('eCommerce item tabs'));
		
		$form->setDefaults(array('prices'=>Variable::get('ecommerce_item_prices'),
		            'parameters'=>Variable::get('ecommerce_item_parameters')
				    ,'descriptions'=>Variable::get('ecommerce_item_descriptions')));

		$form->addElement('checkbox', 'prices', __('Prices'),'');
		$form->addElement('checkbox', 'parameters', __('Parameters'),'');
		$form->addElement('checkbox', 'descriptions', __('Descriptions'),'');

		if($form->validate()) {
			$vals = $form->exportValues();
			Variable::set('ecommerce_item_prices',(isset($vals['prices']) && $vals['prices'])?true:false);
			Variable::set('ecommerce_item_descriptions',(isset($vals['descriptions']) && $vals['descriptions'])?true:false);
			Variable::set('ecommerce_item_parameters',(isset($vals['parameters']) && $vals['parameters'])?true:false);
			DB::Execute('UPDATE premium_ecommerce_products_field SET type=%s WHERE field=%s OR field=%s', array(Variable::get('ecommerce_item_descriptions')?'calculated':'hidden', 'Product Name', 'Description'));
			return false;
		} else $form->display();

		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
		Base_ActionBarCommon::add('save', __('Save'), $form->get_submit_form_href(true,__('creating thumbnails, please wait')));
		
    		return true;
	}
	
	public function prices() {
		if($this->is_back()) return false;
	
		$form = $this->init_module('Libs/QuickForm');

		$form->addElement('header', null, __('Automatic prices'));
		
		eval_js_once("ecommerce_autoprices = function(val) {
			if(val) {
				$('ecommerce_minimal').enable();
				$('ecommerce_margin').enable();
			} else {
				$('ecommerce_minimal').disable();
				$('ecommerce_margin').disable();
			}
		}");

		$form->setDefaults(array('enabled'=>Variable::get('ecommerce_autoprice'),'minimal'=>Variable::get('ecommerce_minimal_profit')
				    ,'margin'=>Variable::get('ecommerce_percentage_profit')));

		$form->addElement('checkbox', 'enabled', __('Enabled'),'',array('onChange'=>'ecommerce_autoprices(this.checked)'));
		$enabled = $form->exportValue('enabled');
		eval_js('ecommerce_autoprices('.$enabled.')');

		$form->addElement('text', 'minimal', __('Minimal profit margin'),array('id'=>'ecommerce_minimal'));
		$form->addElement('text', 'margin', __('Percentage profit margin'),array('id'=>'ecommerce_margin'));
		
		if($enabled) {
			$form->addRule('minimal', __('This should be numeric value'),'numeric');
			$form->addRule('margin', __('This should be numeric value'),'numeric');
		}

		if($form->validate()) {
			$vals = $form->exportValues();
			Variable::set('ecommerce_autoprice',(isset($vals['enabled']) && $vals['enabled'])?true:false);
			Variable::set('ecommerce_minimal_profit',$vals['minimal']);
			Variable::set('ecommerce_percentage_profit',$vals['margin']);
			return false;
		} else $form->display();

		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
		Base_ActionBarCommon::add('save', __('Save'), $form->get_submit_form_href(true,__('creating thumbnails, please wait')));
		
    		return true;
	}
	
	public function check_path($p) {
	    if(!is_dir($p) || !is_dir(rtrim($p,'/').'/files') || !is_writable(rtrim($p,'/').'/files')
		|| (file_exists(rtrim($p,'/').'/files/epesi') && !is_writable(rtrim($p,'/').'/files/epesi'))
		|| (file_exists(rtrim($p,'/').'/files/100/epesi') && !is_writable(rtrim($p,'/').'/files/100/epesi'))
		|| (file_exists(rtrim($p,'/').'/files/200/epesi') && !is_writable(rtrim($p,'/').'/files/200/epesi'))
		|| !is_writable(rtrim($p,'/').'/config')
		|| (file_exists(rtrim($p,'/').'/config/epesi.php') && !is_writable(rtrim($p,'/').'/config/epesi.php'))) return false;
	    return true;
	}
	
	public function delete_quickcart($path) {
	    DB::Execute('DELETE FROM premium_ecommerce_quickcart WHERE path=%s',array($path));
	    @recursive_rmdir($path.'/files/epesi/');
	    @recursive_rmdir($path.'/files/100/epesi/');
	    @recursive_rmdir($path.'/files/200/epesi/');
	}
	
	public function get_3rd_party_info_addon($arg){
	}
	
	public function warehouse_item_addon($arg) {
		$recs = Utils_RecordBrowserCommon::get_records('premium_ecommerce_products',array('item_name'=>$arg['id']));
		if(empty($recs)) {
		    print('<h1><a '.$this->create_callback_href(array('Premium_Warehouse_eCommerceCommon','publish_warehouse_item'),$arg['id']).'>'.__('Publish').'</a></h1>');
		    
		    $plugins = Utils_RecordBrowserCommon::get_records('premium_ecommerce_3rdp_info',array(),array(),array('position'=>'ASC'));
		    if($plugins) print('<h1><a '.$this->create_callback_href(array('Premium_Warehouse_eCommerceCommon','publish_warehouse_item'),array($arg['id'],false)).'>'.__('Publish without getting information data').'</a></h1>');
		    return;
		}
		$rec = array_pop($recs);

		$on = '<span class="checkbox_on" />';
		$off = '<span class="checkbox_off" />';
		
		print('<h1>'.Utils_RecordBrowserCommon::record_link_open_tag('premium_ecommerce_products',$rec['id']).__('Go to item').Utils_RecordBrowserCommon::record_link_close_tag().'</h1>');

		//opts
 		$m = $this->init_module('Utils/GenericBrowser',null,'t0');
 		$m->set_table_columns(array(
				array('name'=>__('Option')),
				array('name'=>__('Value')),
				array('name'=>__('Actions'))
					    ));
 		$m->add_row(__('Published'),($rec['publish']?$on:$off),'<a '.$this->create_callback_href(array('Premium_Warehouse_eCommerceCommon','toggle_publish'),array($rec['id'],!$rec['publish'])).'>'.__('Toggle').'</a>');
 		$m->add_row(__('Recommended'),($rec['recommended']?$on:$off),'<a '.$this->create_callback_href(array('Premium_Warehouse_eCommerceCommon','toggle_recommended'),array($rec['id'],!$rec['recommended'])).'>'.__('Toggle').'</a>');
 		$m->add_row(__('Exclude compare services'),($rec['exclude_compare_services']?$on:$off),'<a '.$this->create_callback_href(array('Premium_Warehouse_eCommerceCommon','toggle_exclude_compare_services'),array($rec['id'],!$rec['exclude_compare_services'])).'>'.__('Toggle').'</a>');
 		$m->add_row(__('Always on stock'),($rec['always_on_stock']?$on:$off),'<a '.$this->create_callback_href(array('Premium_Warehouse_eCommerceCommon','toggle_always_on_stock'),array($rec['id'],!$rec['always_on_stock'])).'>'.__('Toggle').'</a>');
 		$m->add_row(__('Assigned category'),($arg['category']?$on:$off),'');
		$quantity = Utils_RecordBrowserCommon::get_records('premium_warehouse_location',array('item_sku'=>$arg['id'],'>quantity'=>0));
 		$m->add_row(__('Available in warehouse'),(empty($quantity)?$off:$on),'');
 		$m->add_row(__('Common attachments'),Utils_AttachmentCommon::count('premium_ecommerce_products/'.$arg['id']),'');
//		$m->add_row('Related,recommended',Utils_RecordBrowserCommon::record_link_open_tag('premium_ecommerce_products',$rec['id'],false,'edit').__('Edit item').Utils_RecordBrowserCommon::record_link_close_tag());
		
 		$this->display_module($m);

		//langs
        if(Variable::get('ecommerce_item_descriptions')) {
     		$m = $this->init_module('Utils/GenericBrowser',null,'t1');
 	    	$m->set_table_columns(array(
				array('name'=>__('Language')),
				array('name'=>__('Name')),
				array('name'=>__('Description')),
				array('name'=>__('Parameters')),
				array('name'=>__('Attachments')),
				array('name'=>__('Actions'))
					    ));
    		$langs = Utils_CommonDataCommon::get_array('Premium/Warehouse/eCommerce/Languages');
	    	foreach($langs as $code=>$name) {
		        $descs = Utils_RecordBrowserCommon::get_records('premium_ecommerce_descriptions',array('item_name'=>$rec['item_name'],'language'=>$code),array('display_name','short_description'));
		        $descs = array_pop($descs);
    		    $params = Utils_RecordBrowserCommon::get_records('premium_ecommerce_products_parameters',array('item_name'=>$rec['item_name'],'language'=>$code));
	    	    $attachments = Utils_AttachmentCommon::count('premium_ecommercedescriptions/'.$code.'/'.$arg['id']);
 		        $m->add_row($name,($descs && isset($descs['display_name']) && $descs['display_name'])?$on:$off,($descs && isset($descs['short_description']) && $descs['short_description'])?$on:$off,empty($params)?$off:$on,$attachments,
 		        		$descs?Utils_RecordBrowserCommon::record_link_open_tag('premium_ecommerce_descriptions',$descs['id'],false,'edit').__('Edit').Utils_RecordBrowserCommon::record_link_close_tag():'<a '.Utils_RecordBrowserCommon::create_new_record_href('premium_ecommerce_descriptions',array('language'=>$code,'item_name'=>$arg['id'])).'>'.__('Add').'</a>');
    		}
 	    	$this->display_module($m);
        }
        
		//currencies
        if(Variable::get('ecommerce_item_prices')) {
     		$m = $this->init_module('Utils/GenericBrowser',null,'t2');
 	    	$m->set_table_columns(array(
				array('name'=>__('Currency')),
				array('name'=>__('Gross Price')),
				array('name'=>__('Tax Rate')),
				array('name'=>__('Actions'))
					    ));
    		$curr_opts = Premium_Warehouse_eCommerceCommon::get_currencies();
	    	foreach($curr_opts as $id=>$code) {
		        $prices = Utils_RecordBrowserCommon::get_records('premium_ecommerce_prices',array('item_name'=>$rec['item_name'],'currency'=>$id),array('gross_price','tax_rate'));
		        $prices = array_pop($prices);
    		    if($prices && isset($prices['gross_price'])) {
    			    $tax = Utils_RecordBrowserCommon::get_record('data_tax_rates',$prices['tax_rate']);
    			    $m->add_row($code,$prices['gross_price'],$tax['name'],
 		    		Utils_RecordBrowserCommon::record_link_open_tag('premium_ecommerce_prices',$prices['id'],false,'edit').__('Edit').Utils_RecordBrowserCommon::record_link_close_tag());
	    	    } else {
         		    $m->add_row($code,$off,$off,
         		    	'<a '.Utils_RecordBrowserCommon::create_new_record_href('premium_ecommerce_prices',array('currency'=>$id,'item_name'=>$arg['id'])).'>'.__('Add').'</a>');
    		    }
	    	}
 		    $this->display_module($m);
 		}
	}
	
	public function stats() {
		$this->caption = 'eCommerce stats';

		$t = time();
		$start = & $this->get_module_variable('stats_start',date('Y-m-d', $t - (30 * 24 * 60 * 60))); //last 30 days
		$end = & $this->get_module_variable('stats_end',date('Y-m-d',$t));

		$form = $this->init_module('Libs/QuickForm',null,'reports_frm');

		$form->addElement('datepicker', 'start', __('From'));
		$form->addElement('datepicker', 'end', __('To'));
		$form->addElement('submit', 'submit_button', __('Show'));
		$form->addRule('start', 'Field required', 'required');
		$form->addRule('end', 'Field required', 'required');
		$form->setDefaults(array('start'=>$start,'end'=>$end));

		if($form->validate()) {
			$data = $form->exportValues();
			$start = $data['start'];
			$end = $data['end'];
			$end = date('Y-m-d',strtotime($end)+86400);
		}
		$form->display();

		$tb = $this->init_module('Utils/TabbedBrowser');
		$tb->set_tab(__('Products'), array($this,'stats_tab'),array('products',$start,$end));
		$tb->set_tab(__('Pages'), array($this,'stats_tab'),array('pages',$start,$end));
		$tb->set_tab(__('Categories'), array($this,'stats_tab'),array('categories',$start,$end));
		$tb->set_tab(__('Searched Words'), array($this,'stats_tab'),array('searched',$start,$end));
		$this->display_module($tb);
		$this->tag();
	}
	
	public function stats_tab($tab,$start,$end) {
		$start_reg = Base_RegionalSettingsCommon::reg2time($start,false);
		$end_reg = Base_RegionalSettingsCommon::reg2time($end,false);
		
		if($tab=='searched') {
			$label = __('Searched');
			$ret = DB::Execute('SELECT obj,count(visited_on) as num, obj as name FROM premium_ecommerce_'.$tab.'_stats WHERE visited_on>=%T AND visited_on<%T GROUP BY obj ORDER BY num DESC LIMIT 10',array($start_reg,$end_reg+3600*24));
		} else {
			$aj = '';
			switch($tab) {
			    case 'categories':
				$label = __('Categories');
				$jf = 'j.f_category_name';
				$j = 'premium_warehouse_items_categories_data_1 j';
				break;
			    case 'pages':
				$label = __('Pages');
				$jf = 'j.f_page_name';
				$j = 'premium_ecommerce_pages_data_1 j';
				break;
			    case 'products':
				$label = __('Products');
				$jf = 'j.f_item_name';
				$j = 'premium_warehouse_items_data_1 j';
				break;
			}
			$ret = DB::Execute('SELECT obj,count(visited_on) as num, '.$jf.' as name FROM premium_ecommerce_'.$tab.'_stats INNER JOIN '.$j.' ON (obj=j.id) WHERE visited_on>=%T AND visited_on<%T GROUP BY obj ORDER BY num DESC LIMIT 10',array($start_reg,$end_reg+3600*24));
		}

		$f = $this->init_module('Libs/OpenFlashChart');
		$title = new OFC_Elements_Title( $label );
		$f->set_title( $title );

		$av_colors = array('#339933','#999933', '#993333', '#336699', '#808080','#339999','#993399');
		$max = -1;
		$i = 0;
		while($row = $ret->FetchRow()) {
			$bar = new OFC_Charts_Bar();
			$bar->set_colour($av_colors[$i%count($av_colors)]);
			$bar->set_key($row['name'],10);
			$bar->set_values( array((int)$row['num']) );
			if($max<$row['num']) $max = $row['num'];
			$f->add_element( $bar );
			$i++;
		}
		if($max==-1) {
		    print(__('No stats available'));
		    return;
		}
		$y_ax = new OFC_Elements_Axis_Y();
		$y_ax->set_range(0,$max);
		$y_ax->set_steps($max/10);
		$f->set_y_axis($y_ax);

		$f->set_width(950);
		$f->set_height(400);
		$this->display_module($f);
	}
	
	public function pages_stats_addon($arg) {
		$this->stats_addon('pages',$arg['id']);
	}
	
	public function categories_stats_addon($arg) {
		$this->stats_addon('categories',$arg['id']);
	}
	
	public function products_stats_addon($arg) {
		$this->stats_addon('products',$arg['item_name']);
	}
	
	private function stats_addon($tab,$id) {
		$gb = $this->init_module('Utils/GenericBrowser',null,'stats');

		$gb->set_table_columns(array(
			array('name'=>__('Time'), 'order'=>'visited_on')));

		$query = 'SELECT visited_on FROM premium_ecommerce_'.$tab.'_stats WHERE obj='.$id;
		$query_qty = 'SELECT count(*) FROM premium_ecommerce_'.$tab.'_stats WHERE obj='.$id;

		$gb->set_default_order(array(__('Time')=>'DESC'));
		$ret = $gb->query_order_limit($query, $query_qty);
		
		while(($row=$ret->FetchRow())) {
			$gb->add_row($row['visited_on']);
		}

		$this->display_module($gb);
	}
	
	public function product_comments_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_product_comments');
		$order = array(array('item_name'=>$arg['item_name']), array('item_name'=>false), array('time'=>'DESC'));
		$rb->set_defaults(array('item_name'=>$arg['item_name'],'language'=>Base_LangCommon::get_lang_code()));
		$rb->set_header_properties(array(
			'language'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'content'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->display_module($rb,$order,'show_data');
	}
	
	public function users_addon($arg) {
		$rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_users');
		$order = array(array('contact'=>$arg['id']), array('contact'=>false), array());
		$rb->set_defaults(array('contact'=>$arg['id']));
		$ret = Utils_RecordBrowserCommon::get_records('premium_ecommerce_users',array('contact'=>$arg['id']));
		if(count($ret)) $rb->set_button(false);
		$this->display_module($rb,$order,'show_data');
	}
	
	public function newsletter() {
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_newsletter');
		$args = array(array(), array(), array('email'=>'ASC'));
		$this->display_module($this->rb,$args,'show_data');
	}

	public function comments() {
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_product_comments');
		$args = array(array('publish'=>0), array('product'=>true,'publish'=>false), array('time'=>'DESC'));
		$this->rb->set_header_properties(array(
			'language'=>array('width'=>10, 'wrapmode'=>'nowrap'),
			'content'=>array('width'=>50, 'wrapmode'=>'nowrap')
									));
		$this->rb->set_additional_actions_method(array($this, 'comments_publish_action'));
		$this->display_module($this->rb,$args,'show_data');
	
	}
	
	public function comments_publish_action($r, & $gb_row) {
		if(isset($_REQUEST['publish_action']) && $r['id']==$_REQUEST['publish_action']) {
    		    Utils_RecordBrowserCommon::update_record('premium_ecommerce_product_comments',$r['id'],array('publish'=>1));
		    location(array());
		}
		$gb_row->add_action(Module::create_href(array('publish_action'=>$r['id'])),'Publish',null,'restore');
	}
	
	public function caption(){
		if (isset($this->caption)) return $this->caption;
		if (isset($this->rb)) return $this->rb->caption();
		return __('eCommerce administration');
	}
	
	public function applet($conf, & $opts) {
		//available applet options: toggle,href,title,go,go_function,go_arguments,go_contruct_arguments
		$opts['go'] = false; // enable/disable full screen
		$xxx = Premium_Warehouse_eCommerceCommon::$order_statuses;
		$xxx['active'] = __('Active');
		$opts['title'] = __('eCommerce - %s',array($xxx[$conf['status']]));
		
		$crits = array('online_order'=>1);
		if($conf['status']=='active')
			$crits['status'] = array(2,3,4,5,6);
		else
			$crits['status'] = $conf['status'];
		if($conf['my']) {
			$my_rec = CRM_ContactsCommon::get_my_record();
			$crits['employee'] = array('',$my_rec['id']);
		}
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items_orders','premium_warehouse_items_orders');
		$conds = array(
									array(	array('field'=>'transaction_id', 'width'=>10),
										array('field'=>'transaction_date', 'width'=>10),
										array('field'=>'warehouse', 'width'=>10)
									),
									$crits,
									array('transaction_date'=>'DESC','transaction_id'=>'DESC'),
									array('Premium_Warehouse_eCommerceCommon','applet_info_format'),
									15,
									$conf,
									& $opts
				);
		$this->display_module($rb, $conds, 'mini_view');

	}
	
	public function setup_3rd_party_plugins() {
		if($this->is_back()) return false;
		Base_ActionBarCommon::add('back',__('Back'),$this->create_back_href());
	
	    Base_ActionBarCommon::add('search',__('Scan plugins'), $this->create_callback_href(array('Premium_Warehouse_eCommerceCommon','scan_for_3rdp_info_plugins')));
        $this->recordset = '3rdp_info';
        $this->rb = $this->init_module('Utils/RecordBrowser','premium_ecommerce_3rdp_info','premium_ecommerce_3rdp_info');
		$this->rb->set_additional_actions_method(array($this, 'actions_for_position'));
		$this->rb->force_order(array('position'=>'ASC'));
        $this->display_module($this->rb);
        
        return true;
	}

	public function attachment_product_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('premium_ecommerce_products/'.$arg['item_name']));
		$a->set_add_func(array('Premium_Warehouse_eCommerceCommon','copy_attachment'));
		$this->display_module($a);
	}

	public function attachment_product_desc_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('premium_ecommerce_descriptions/'.$arg['language'].'/'.$arg['item_name']));
		$a->set_add_func(array('Premium_Warehouse_eCommerceCommon','copy_attachment'));
		$this->display_module($a);
	}

	public function attachment_page_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('premium_ecommerce_pages/'.$arg['id']));
		$this->display_module($a);
	}
	
	public function attachment_page_desc_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('premium_ecommerce_pages_data/'.$arg['language'].'/'.$arg['page']));
		$this->display_module($a);
	}

}

?>