<?php
/**
 * Warehouse - Items
 * @author abisaga@telaxus.com
 * @copyright abisaga@telaxus.com
 * @license SPL
 * @version 0.3
 * @package premium-warehouse
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Warehouse_Items extends Module {
	private $rb;

	public function body() {
		// Quickjump jak do ticketow, ale po UPC code
		// Leightbox przy dodawaniu itemu (po type) - tak jak transaction
		$lang = $this->init_module('Base/Lang');
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items','premium_warehouse_items_module');
		$this->rb->set_default_order(array('item_name'=>'ASC'));		
		$this->rb->set_cut_lengths(array('item_name'=>30));
		$this->rb->set_defaults(array('quantity'=>'0','reorder_point'=>'0'));		
		$this->display_module($this->rb);
	}

	public function applet($conf,$opts) {
		$opts['go'] = true; // enable full screen
		$rb = $this->init_module('Utils/RecordBrowser','premium_warehouse_items','premium_warehouse_items');
		$limit = null;
		$crits = array();
		
		$sorting = array('item_name'=>'ASC');
		$cols = array(
							array('field'=>'item_name', 'width'=>10, 'cut'=>18),
							array('field'=>'quantity', 'width'=>10)
										);

		$conds = array(
									$cols,
									$crits,
									$sorting,
									array('Premium_Warehouse_ItemsCommon','applet_info_format'),
									$limit,
									$conf,
									& $opts
				);
		$opts['actions'][] = Utils_RecordBrowserCommon::applet_new_record_button('premium_warehouse_items',array());
		$this->display_module($rb, $conds, 'mini_view');
	}

	public function attachment_addon($arg){
		$a = $this->init_module('Utils/Attachment',array($arg['id'],'Premium/Warehouse/Items/'.$arg['id']));
		$a->additional_header('Item: '.$arg['item_name']);
		$a->allow_protected($this->acl_check('view protected notes'),$this->acl_check('edit protected notes'));
		$a->allow_public($this->acl_check('view public notes'),$this->acl_check('edit public notes'));
		$this->display_module($a);
	}

	public function caption(){
		if (isset($this->rb)) return $this->rb->caption();
	}
}

?>