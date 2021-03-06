<?php
class Products
{

  //var $aProducts = null;
  //var $aProductsPages = null;
  var $mData = null;
  var $searchedWords = null;

  function &getInstance( ){
    static $oInstance = null;
    if( !isset( $oInstance ) ){
      $oInstance = new Products( );
    }
    return $oInstance;
  } // end function getInstance

  /**
  * Constructor
  * @return void
  */
  function Products( ){
//    $this->generateCache( );
	$uncategorized = DB::GetOne('SELECT 1 FROM premium_ecommerce_products_data_1 pr
					INNER JOIN (premium_warehouse_items_data_1 it,premium_ecommerce_availability_data_1 av) ON (pr.f_item_name=it.id AND av.id=pr.f_available)
					 WHERE pr.f_publish=1 AND pr.active=1 AND it.f_category is NULL');

	if(!$uncategorized) {//remove uncategorized category
	    unset(Pages::getInstance()->aPages[23]);
	}
  } // end function Pages
/*
  function generateCache() {
	$this->aProductsPages = array();
	$this->aProducts = $this->getProducts();
	foreach($this->aProducts as &$p) {
		$this->aProductsPages[$p['iProduct']] = $p['aCategories'];
	}
  }
  */
  
  function getProduct($id,$nav = false) {
	$arr = $this->getProducts('it.id='.(int)$id,null,null,$nav);
	return array_shift($arr);
  }

  /**
  * Generate cache variables
  * @return void
  */
  function getProducts($where = '',$limit=null,$offset=null, $navigation = false){
	global $config;
    $products = array();
    $oPage  =& Pages::getInstance( );

	$currency = DB::GetOne('SELECT id FROM utils_currency WHERE code=%s',array($config['currency_symbol']));
	if($currency===false) 
		die('Currency not defined in Epesi: '.$config['currency_symbol']);
		
	static $availability_labels,$availability_codes;
	if(!isset($availability_labels)) {
		$availability_codes = DB::GetAssoc('SELECT id, f_availability_code FROM premium_ecommerce_availability_data_1 WHERE active=1');
		$availability_labels = DB::GetAssoc('SELECT f_availability, f_label FROM premium_ecommerce_availability_labels_data_1 WHERE f_language="'.LANGUAGE.'" AND active=1');
	}
	
	$ret = DB::GetAll('SELECT 	it.id as iProduct, 
								it.f_item_name as sName2, 
								it.f_description as sEpesiDescription, 
								pri.f_gross_price as fPrice, 
								pri.f_tax_rate as tax,
								pr.f_position as iPosition, 
								pr.f_recommended as sRecommended,
								it.f_category,
								it.f_last_purchase_price,
								pr.f_available as iAvailable, 
								d.f_display_name as sName,
								d.f_short_description as sDescriptionShort,
								d.f_long_description as sDescriptionFull,
								d.f_page_title as sNameTitle, 
								d.f_meta_description as sMetaDescription, 
								d.f_keywords as sMetaKeywords,
								d_en.f_display_name as sNameEn,
								d_en.f_short_description as sDescriptionShortEn,
								d_en.f_long_description as sDescriptionFullEn,
								d_en.f_page_title as sNameTitleEn, 
								d_en.f_meta_description as sMetaDescriptionEn, 
								d_en.f_keywords as sMetaKeywordsEn,
								it.f_weight as sWeight,
								it.f_manufacturer as iProducer,
								it.f_sku as sSku,
								(SELECT SUM(loc.f_quantity) FROM premium_warehouse_location_data_1 loc WHERE loc.f_item_sku=it.id AND loc.active=1) as f_quantity,
								it.f_net_price fPrice2,
								it.f_tax_rate tax2,
								pr.f_exclude_compare_services,
								pr.f_always_on_stock,
								it.f_upc,
								it.f_manufacturer_part_number as sPartNumber
					FROM premium_ecommerce_products_data_1 pr
					INNER JOIN (premium_warehouse_items_data_1 it) ON (pr.f_item_name=it.id)
					LEFT JOIN premium_ecommerce_prices_data_1 pri ON (pri.f_item_name=it.id AND pri.active=1 AND pri.f_currency='.$currency.')
					LEFT JOIN premium_ecommerce_descriptions_data_1 d ON (d.f_item_name=it.id AND d.f_language="'.LANGUAGE.'" AND d.active=1)
					LEFT JOIN premium_ecommerce_descriptions_data_1 d_en ON (d_en.f_item_name=it.id AND d_en.f_language="en" AND d_en.active=1)
					LEFT JOIN premium_warehouse_location_data_1 loc ON (loc.f_item_sku=it.id AND loc.f_quantity>0 AND loc.active=1)
					 WHERE pr.f_publish=1 AND pr.active=1 AND it.active=1 '.($where?' AND ('.$where.')':'').' GROUP BY it.id ORDER BY'.($navigation?'':'
                    IF((SUM(loc.f_quantity)>0 OR pr.f_always_on_stock=1),1,IF((SELECT 1 FROM premium_warehouse_wholesale_items dist_item
					WHERE dist_item.item_id=it.id AND dist_item.quantity>0 AND dist_item.price_currency='.$currency.' LIMIT 1)=1,1,0)) DESC,').' pr.f_position'.($limit!==null?' LIMIT '.(int)$limit.($offset!==null?' OFFSET '.(int)$offset:''):''));
	$pids = array();
	foreach($ret as $aExp)
		$pids[] = $aExp['iProduct'];
	if($pids)
		$reserved = DB::GetAssoc('SELECT d.f_item_name, SUM(d.f_quantity) FROM premium_warehouse_items_orders_details_data_1 d INNER JOIN premium_warehouse_items_orders_data_1 o ON (o.id=d.f_transaction_id) WHERE ((o.f_transaction_type=1 AND o.f_status in (-1,2,3,4,5)) OR (o.f_transaction_type=4 AND o.f_status in (2,3))) AND d.active=1 AND o.active=1 AND d.f_item_name IN ('.implode(',',$pids).') GROUP BY d.f_item_name');

        $taxes = DB::GetAssoc('SELECT id, f_percentage FROM data_tax_rates_data_1 WHERE active=1');
	$autoprice = getVariable('ecommerce_autoprice');
	$minimal = getVariable('ecommerce_minimal_profit');
	$percentage = getVariable('ecommerce_percentage_profit');
	$compare_service = null;
        if(preg_match('#ceneo.pl#is',$_SERVER['HTTP_REFERER']) || $_REQUEST['p']=='compare-ceneo' || $_REQUEST['p']=='compare-ceneo2')
                $compare_service = 'ceneo';
        elseif(preg_match('#skapiec.pl#is',$_SERVER['HTTP_REFERER']) || $_REQUEST['p']=='compare-skapiec')
                $compare_service = 'skapiec';
        elseif(isset($_COOKIE['compare_service']))
                $compare_service = $_COOKIE['compare_service'];
        if($compare_service)
                setcookie('compare_service',$compare_service,time()+3600*24);
	foreach($ret as $aExp) {
		if($aExp['sName']=='') 
			$aExp['sName'] = $aExp['sName2'];
		if($compare_service) {
			$compare_price = @DB::GetRow('SELECT f_gross_price,f_tax_rate FROM premium_ecommerce_compare_prices_data_1 WHERE active=1 AND f_currency=%d AND f_item_name=%d AND f_plugin=%s',array($currency,$aExp['iProduct'],$compare_service));
    			if($compare_price) {
		        	$aExp['fPrice']=$compare_price['f_gross_price'];
		        	$aExp['tax']=$compare_price['f_tax_rate'];
		        	$aExp['fPriceNet'] = round(((float)$aExp['fPrice'])*100/(100+$taxes[$aExp['tax']]),2);
		        }
		}
		if(!$aExp['fPrice']) {
			$rr = explode('__',$aExp['fPrice2']);
			if($rr && $rr[0] && $rr[1]==$currency) {
				$netto = $rr[0];
				$aExp['fPrice'] = round(((float)$netto)*(100+$taxes[$aExp['tax2']])/100,2);
				$aExp['fPriceNet'] = round((float)$netto,2);
				$aExp['tax'] = $aExp['tax2'];
			} 
		}
		if($aExp['f_always_on_stock']) {
			if($aExp['f_quantity']<10) $aExp['f_quantity'] = 10;
		} else {
			unset($aExp['f_always_on_stock']);
			if($aExp['f_quantity']>=$reserved[$aExp['iProduct']]) {
				$aExp['f_quantity'] -= $reserved[$aExp['iProduct']];
				$reserved[$aExp['iProduct']] = 0;
			} else {
				$reserved[$aExp['iProduct']] -= $aExp['f_quantity'];
				$aExp['f_quantity'] = 0;
			}
		}

	
		if($aExp['f_quantity']==0) {
			$distributors = DB::GetAll('SELECT dist_item.quantity,
					dist_item.quantity_info,
					dist_item.price,
					dist.f_items_availability as iAvailable,
					dist.f_minimal_profit,
					dist.f_percentage_profit,
					dist_item.price_currency
					FROM premium_warehouse_wholesale_items dist_item
					INNER JOIN premium_warehouse_distributor_data_1 dist ON dist.id=dist_item.distributor_id
					WHERE dist_item.item_id=%d AND dist_item.quantity>0 AND dist.active=1',array($aExp['iProduct']));
			$minimal_aExp = null;
			foreach($distributors as $kkk=>$dist) {
				if($dist['quantity']>$reserved[$aExp['iProduct']]) {
					$dist['quantity'] -= $reserved[$aExp['iProduct']];

                                        $aExp2 = array();
					$aExp2['distributorQuantity'] = $dist['quantity'];
					$aExp2['iAvailable'] = $dist['iAvailable'];
					$aExp2['sAvailableInfo'] = $dist['quantity_info'];

					if($autoprice && $dist['price_currency']==$currency) {
					    $user_price = $aExp['fPrice'];
						$dist_price = round((float)$dist['price']*(100+$taxes[$aExp['tax2']])/100,2);
						if($user_price>=$dist_price) {
							$aExp2['fPrice'] = $user_price;
							$aExp2['fPrice'] = $aExp['fPriceNet'];
						} else {
							$netto = $dist['price'];
							$profit = $netto*(is_numeric($dist['f_percentage_profit'])?$dist['f_percentage_profit']:$percentage)/100;
							$minimal2 = (is_numeric($dist['f_minimal_profit'])?$dist['f_minimal_profit']:$minimal);
							if($profit<$minimal2) $profit = $minimal2;
							$aExp2['fPrice'] = round((float)($netto+$profit)*(100+$taxes[$aExp['tax2']])/100,2);
							$aExp2['fPriceNet'] = round((float)($netto+$profit),2);
							$aExp2['tax'] = $aExp['tax2'];		
						}
					}
					if($minimal_aExp===null || (!isset($minimal_aExp['fPrice']) && isset($aExp2['fPrice'])) || $minimal_aExp['fPrice']>$aExp2['fPrice'])
                                                $minimal_aExp = $aExp2;
				}
			}
			if($minimal_aExp!==null) {
			        $aExp = array_merge($aExp,$minimal_aExp);
				$reserved[$aExp['iProduct']] = 0;
			}
			unset($distributors);
		} elseif($autoprice && !$aExp['fPrice'] && (!isset($aExp['f_always_on_stock']) || !$aExp['f_always_on_stock'])) {
			        $lpp = ($aExp['f_last_purchase_price']?explode('__',$aExp['f_last_purchase_price']):null);
                                if($lpp && $lpp[1]==$currency) {
                                    $netto = $lpp[0];
                                } else {
        				$netto = DB::GetOne('SELECT MIN(dist_item.price)
					    FROM premium_warehouse_wholesale_items dist_item
					    INNER JOIN premium_warehouse_distributor_data_1 dist ON dist.id=dist_item.distributor_id
					    WHERE dist_item.item_id=%d AND dist_item.quantity>0 AND dist_item.price_currency=%d ORDER BY dist_item.price',array($aExp['iProduct'],$currency));
				}
				if($netto) {
					$profit = $netto*$percentage/100;
					if($profit<$minimal) $profit = $minimal;
					$aExp['fPrice'] = round((float)($netto+$profit)*(100+$taxes[$aExp['tax2']])/100,2);
					$aExp['fPriceNet'] = round((float)($netto+$profit),2);
					$aExp['tax'] = $aExp['tax2'];		
				}
		}
		if(isset($availability_codes[$aExp['iAvailable']]))
			$aExp['sAvailableCode'] = $availability_codes[$aExp['iAvailable']];
		if(isset($availability_labels[$aExp['iAvailable']]))
			$aExp['sAvailable'] = $availability_labels[$aExp['iAvailable']];
		elseif(isset($aExp['sAvailableCode']))
			$aExp['sAvailable'] = $aExp['sAvailableCode'];
		if($aExp['sAvailableInfo'])
			$aExp['sAvailable'] .= '<br>'.$aExp['sAvailableInfo'];
		unset($aExp['iAvailable']);
		unset($aExp['iAvailable2']);
		if(!$aExp['tax']) $aExp['tax'] = 0;
		$aExp['iQuantity'] = 0+$aExp['f_quantity']+$aExp['distributorQuantity'];
		if(!$config['display_all_published_products'] && $aExp['iQuantity']==0) continue;
		if($aExp['fPrice']) {
			$aExp['fPrice'] = number_format($aExp['fPrice'],2,'.','');
			$aExp['fPriceNet'] = number_format($aExp['fPriceNet'],2,'.','');
		}
		$aExp['iComments'] = 1;
		unset($aExp['sName2']);
		$cats = array_filter(explode('__',$aExp['f_category']));
		unset($aExp['f_category']);
		$pages = array();
		$meta_cats = array();
		if(!empty($cats)) {
    		    foreach($cats as $c) {
    		    	foreach(explode('/',$c) as $iPage) {
	    		    	$meta_cats[] = $oPage->aPages[$iPage*4]['sName'];
	    		}
			$pos = strrpos($c,'/');
			if($pos!==false)
				$last_cat = substr($c,$pos+1);
			else
				$last_cat = $c;
			$last_cat *= 4;
			$pages[$last_cat] = $last_cat;
		    }
		}
		if(empty($pages))
			$pages[23] = 23; //uncategorized
		if($aExp['iProducer']!==null && $aExp['iProducer']!=='') {
			$aExp['iProducer'] = $aExp['iProducer']*4+1;
			$pages[$aExp['iProducer']] = $aExp['iProducer'];
		}

		foreach(array('sName','sDescriptionShort','sDescriptionFull','sNameTitle','sMetaDescription','sMetaKeywords') as $kkk) {
			if(!$aExp[$kkk])
				$aExp[$kkk] = $aExp[$kkk.'En'];
			unset($aExp[$kkk.'En']);
		} 
		if(!$aExp['sDescriptionFull'])
		    $aExp['sDescriptionFull'] = $aExp['sEpesiDescription'];
		unset($aExp['sEpesiDescription']);
		
		$meta_cats = ($meta_cats?implode(',',array_unique($meta_cats)):'');
		if(!$aExp['sMetaDescription']) $aExp['sMetaDescription'] = $aExp['sName'].'. '.strip_tags($aExp['sDescriptionShort'],'').'. '.$meta_cats;
		if(!$aExp['sMetaKeywords']) $aExp['sMetaKeywords'] = str_replace(array(' ','	'),',', $aExp['sName']).($aExp['f_upc']?','.$aExp['f_upc']:'').($meta_cats?','.$meta_cats:'');
		while(1) {
			$keywords = str_replace(',,',',',$aExp['sMetaKeywords']);
			if($keywords == $aExp['sMetaKeywords']) break;
			$aExp['sMetaKeywords'] = $keywords;
		}
//		unset($aExp['f_upc']);

    		$products[$aExp['iProduct']] = $aExp;
	        $products[$aExp['iProduct']]['sLinkName'] = '?'.$aExp['iProduct'].','.change2Url( $products[$aExp['iProduct']]['sName'] );
		$products[$aExp['iProduct']]['aCategories'] = $pages;
	}
	
	if(count($products)==1 && $navigation && isset($_SESSION['last_products_query']) && count($_SESSION['last_products_query'])==3) {
		$p = array_shift(array_keys($products));
		while(1) {
			$pp = $this->getProducts($_SESSION['last_products_query'][0],$_SESSION['last_products_query'][1],$_SESSION['last_products_query'][2]);
			$pos = array_search($p,array_keys($pp));
			if(($pos!==false && $pos<$_SESSION['last_products_query'][1]-1 && $pos>0) || empty($pp)) break;
			if($pos!==false && $pos>=$_SESSION['last_products_query'][1]-1) {
				$_SESSION['last_products_query'][2] += 1;
			} elseif($pos!==false && $pos==0) {
				if($_SESSION['last_products_query'][2]==0)
					break;
				$_SESSION['last_products_query'][2] -= 1;
 			} else {
				$_SESSION['last_products_query'][2] += $_SESSION['last_products_query'][1];
			}
		}
		if(!empty($pp)) {
			$ppkeys = array_keys($pp);
			$pos = array_search($p,$ppkeys);
			if(isset($ppkeys[$pos+1]))
				$products[$p]['sNextLinkName'] = $pp[$ppkeys[$pos+1]]['sLinkName'];
			if(isset($ppkeys[$pos-1]))
				$products[$p]['sPrevLinkName'] = $pp[$ppkeys[$pos-1]]['sLinkName'];
		}
	}
	
	if(count($products)>1 && $this->searchedWords) {
    	$query = '1';
	    foreach($this->searchedWords as $w) {
    		$query .= ' AND (((d.f_display_name is null OR d.f_display_name="") AND it.f_item_name '.DB::like().' \'%%'.DB::addq($w).'%%\') OR d.f_display_name '.DB::like().' \'%%'.DB::addq($w).'%%\')';
	    }
    	$in_title_ids = DB::GetCol('SELECT it.id 
					FROM premium_ecommerce_products_data_1 pr
					INNER JOIN (premium_warehouse_items_data_1 it) ON (pr.f_item_name=it.id)
					LEFT JOIN premium_ecommerce_descriptions_data_1 d ON (d.f_item_name=it.id AND d.f_language="'.LANGUAGE.'" AND d.active=1)
					 WHERE pr.f_publish=1 AND pr.active=1 AND it.active=1 AND '.$query.' GROUP BY it.id');
	    $products_up=array();
	    foreach($in_title_ids as $i) {
	        if($products[$i]['iQuantity']>0) {
	            $products_up[$i] = $products[$i];
	            unset($products[$i]);
	        }
	    }
	    $products = $products_up+$products;
	}
	
	return $products;
  } // end function generateCache

  function listProductsQuery($iContent,$aProducts,&$sUrlExt,& $manufacturers) {
    $oPage  =& Pages::getInstance( );
    $query = '';
    $manufacturers = null;
    if( !isset( $aProducts ) ){
      if( isset( $GLOBALS['sPhrase'] ) && !empty( $GLOBALS['sPhrase'] ) ){
          $aExp   = explode( ' ', $GLOBALS['sPhrase'] );
	  $iCount = count( $aExp );
	  $aWords = array();
          for( $i = 0; $i < $iCount; $i++ ){
	    $aExp[$i] = trim( $aExp[$i] );
    	    if( !empty( $aExp[$i] ) )
        	  $aWords[] = $aExp[$i];
	  } // end for
    	  saveSearchedWords( $aWords );
    	  $this->searchedWords = $aWords;
    	$query = '1';
    	foreach($aWords as $w) {
	    	$query .= ' AND (it.f_sku '.DB::like().' \'%%'.DB::addq($w).'\' OR it.f_product_code '.DB::like().' \''.DB::addq($w).'\' OR it.f_upc '.DB::like().' \''.DB::addq($w).'\' OR it.f_item_name '.DB::like().' \'%%'.DB::addq($w).'%%\' OR d.f_display_name '.DB::like().' \'%%'.DB::addq($w).'%%\' OR d_en.f_display_name '.DB::like().' \'%%'.DB::addq($w).'%%\' OR d.f_short_description '.DB::like().' \'%%'.DB::addq($w).'%%\' OR d_en.f_short_description '.DB::like().' \'%%'.DB::addq($w).'%%\')';
    	}
    $sUrlExt .= ((defined( 'FRIENDLY_LINKS' ) && FRIENDLY_LINKS == true)?null:'&amp;').'sPhrase='.urlencode($GLOBALS['sPhrase']);
  }else{
	if($iContent==23) {
    	    $query .= 'it.f_category is null OR it.f_category=\'\'';
	} elseif($iContent==11) {
    	    $query .= 'pr.f_recommended=1';
	} elseif($iContent%4==0) {
            if( DISPLAY_SUBCATEGORY_PRODUCTS === true ){
	      // return all pages and subpages
    	      $aData = $oPage->throwAllChildrens( $iContent );
              if( isset( $aData ) ){
	        foreach( $aData as $iValue ){
	          $query .= 'it.f_category '.DB::like().' \'%\\_\\_'.($iValue/4).'\\_\\_%\' OR it.f_category '.DB::like().' \'%/'.($iValue/4).'\\_\\_%\' OR ';
        	}
              }
	    }
    	    $query .= 'it.f_category '.DB::like().' \'%\\_\\_'.($iContent/4).'\\_\\_%\' OR it.f_category '.DB::like().' \'%/'.($iContent/4).'\\_\\_%\'';

	} else {
    	    $query .= 'it.f_manufacturer='.(($iContent-1)/4);	
	}
      }

      if((isset( $GLOBALS['sPhrase'] ) && !empty( $GLOBALS['sPhrase'] )) || (($iContent-1)%4!==0)) {//search or not manufacturer category
	    $manuf = 'SELECT c.id, c.f_company_name
			FROM premium_warehouse_items_data_1 it INNER JOIN (company_data_1 c,premium_ecommerce_products_data_1 pr) 
			ON (c.id=it.f_manufacturer AND pr.f_item_name=it.id AND pr.active=1 AND pr.f_publish=1)
			LEFT JOIN premium_ecommerce_descriptions_data_1 d ON (d.f_item_name=it.id AND d.f_language="'.LANGUAGE.'" AND d.active=1)
			LEFT JOIN premium_ecommerce_descriptions_data_1 d_en ON (d_en.f_item_name=it.id AND d_en.f_language="en" AND d_en.active=1)
			WHERE it.active=1 AND pr.active=1 AND ('.$query.') GROUP BY c.id ORDER BY c.f_company_name';

	    $manufs = DB::GetAssoc($manuf);
	    if($manufs && count($manufs)>1) {
	      $manufs = array(''=>'---')+$manufs;
              foreach($manufs as $k=>$v) {
		$manufacturers .= '<option value="'.$k.'" '.($k==$_GET['iManufacturer']?'selected="1"':'').'>'.$v.'</option>';
                if( $k==$_GET['iManufacturer'] && $k!=='' ) {
		        $sUrlExt .= ((defined( 'FRIENDLY_LINKS' ) && FRIENDLY_LINKS == true)?null:'&amp;').'iManufacturer='.$_GET['iManufacturer'];
		        $query = '('.$query.') AND it.f_manufacturer='.$k;
	        }
	      }
	    }    
	}
     } elseif(!empty($aProducts)) {
	$query = '0';
	foreach($aProducts as $p)
		$query .= ' OR it.id='.(int)$p;
    }

    return $query;
  }

  /**
  * List products
  * @return string
  * @param string $sFile
  * @param int    $iContent
  * @param int    $iList
  * @param array  $aProducts
  */
  function listProducts( $sFile, $iContent, $iList = null, $aProducts = null ){
    $oTpl   =& TplParser::getInstance( );
    $oFile  =& Files::getInstance( );
    $oPage  =& Pages::getInstance( );
    $content= null;
    $sUrlExt= null;
    $manufacturers = null;

    $query = $this->listProductsQuery($iContent,$aProducts,$sUrlExt,$manufacturers);
    
    if( $query ){
      $sBasketPage = ( isset( $GLOBALS['config']['basket_page'] ) && isset( $oPage->aPages[$GLOBALS['config']['basket_page']] ) ) ? $oPage->aPages[$GLOBALS['config']['basket_page']]['sLinkName'].((defined( 'FRIENDLY_LINKS' ) && FRIENDLY_LINKS == true)?'?':'&amp;') : null;

      $iCount = DB::GetOne('SELECT 	count(DISTINCT it.id) 
					FROM premium_ecommerce_products_data_1 pr
					INNER JOIN (premium_warehouse_items_data_1 it) ON (pr.f_item_name=it.id)
					LEFT JOIN premium_ecommerce_descriptions_data_1 d ON (d.f_item_name=it.id AND d.f_language="'.LANGUAGE.'" AND d.active=1)
					LEFT JOIN premium_ecommerce_descriptions_data_1 d_en ON (d_en.f_item_name=it.id AND d_en.f_language="en" AND d_en.active=1)
					 WHERE pr.f_publish=1 AND pr.active=1 AND it.active=1 AND ('.$query.')');

      if( !isset( $iList ) ){
        $iList = $GLOBALS['config']['products_list'];
      }

      $iProducts = ceil( $iCount / $iList );
      $iPageNumber = isset( $GLOBALS['aActions']['o2'] ) ? $GLOBALS['aActions']['o2'] : 1;
      if( !isset( $iPageNumber ) || !is_numeric( $iPageNumber ) || $iPageNumber < 1 )
        $iPageNumber = 1;
      if( $iPageNumber > $iProducts && $iProducts>0)
        $iPageNumber = $iProducts;

      $iStart = ($iPageNumber-1) * $iList;

      $this->mData = null;

      $_SESSION['last_products_query'] = array($query,$iList,$iStart);
      $products = $this->getProducts($query,$iList,$iStart);
      $i=0;
      foreach($products as $aData){

        $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
        $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
        $aData['sPrice'] = is_numeric( $aData['fPrice'] ) ? displayPrice( $aData['fPrice'] ) : $aData['fPrice'];
        $aData['sPriceNet'] = is_numeric( $aData['fPriceNet'] ) ? displayPrice( $aData['fPriceNet'] ) : $aData['fPriceNet'];
        $aData['sPages'] = $this->throwProductsPagesTree( $aData['aCategories'] );
        $aData['sBasket']= null;
        $aData['sRecommended'] = $aData['sRecommended']? $oTpl->tbHtml( $sFile, 'PRODUCTS_RECOMMENDED' ) : null;

        $aData['sDescriptionShort'] = changeTxt( $aData['sDescriptionShort'], 'nlNds' );
        $oTpl->setVariables( 'aData', $aData );
        $aData['sDescriptionShort'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_DESCRIPTION' );

        $oTpl->setVariables( 'aData', $aData );

        if($aDataImage = $oFile->throwDefaultImage($aData['iProduct'],2)){
          $oTpl->setVariables( 'aDataImage', $aDataImage );
          $aData['sImage'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_IMAGE' );
        }
        else{
          $aData['sImage'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_NO_IMAGE' );
        }

        if( is_numeric( $aData['fPrice'] ) ){
          if( isset( $sBasketPage ) && $aData['iQuantity']>0 ){
            $aData['sBasketPage'] = $sBasketPage;
            $oTpl->setVariables( 'aData', $aData );
            $aData['sBasket'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_BASKET' );
          } else {
            $aData['sBasket'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_OUT_OF_STOCK' );
          }
          $oTpl->setVariables( 'aData', $aData );
          $aData['sPrice'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_PRICE' );
        } else {
          $oTpl->setVariables( 'aData', $aData );
          $aData['sPrice'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_NO_PRICE' );
        }

        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, 'PRODUCTS_LIST' );
        $i++;
      } // end for

      if( isset( $content ) ){
        if($manufacturers) {
          $oTpl->setVariables('manufacturers',$manufacturers);
          $oTpl->setVariables('sLinkName',throwPageUrl( $oPage->aPages[$iContent]['sLinkName'], true ).$sUrlExt);
    	  $aData['manufacturers'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_MANUFACTURERS' );
        }


        if( $iCount > $iList ){
          $aData['sPages'] = countPages( $iCount, $iList, $iPageNumber, throwPageUrl( $oPage->aPages[$iContent]['sLinkName'], true ), $sUrlExt, FRIENDLY_LINKS );
          $aData['sHidePages'] = null;
        }
        else
          $aData['sHidePages'] = ' hide';

        $oTpl->setVariables( 'aData', $aData );
        return $oTpl->tbHtml( $sFile, 'PRODUCTS_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'PRODUCTS_FOOT' );
      }
    }
  } // end function listProducts

  /**
  * List products
  * @return string
  * @param string $sFile
  * @param int    $iContent
  * @param int    $iList
  * @param array  $aProducts
  */
  function listProductsGallery( $sFile, $iContent, $iList = null, $aProducts = null ){
    $oTpl   =& TplParser::getInstance( );
    $oFile  =& Files::getInstance( );
    $oPage  =& Pages::getInstance( );
    $content= null;
    $sUrlExt= null;
    $iColumns = 3;
    $iWidth   = (int) ( 100 / $iColumns );

    $query = $this->listProductsQuery($iContent,$aProducts,$sUrlExt);
    
    if( $query ){
      $sBasketPage = ( isset( $GLOBALS['config']['basket_page'] ) && isset( $oPage->aPages[$GLOBALS['config']['basket_page']] ) ) ? $oPage->aPages[$GLOBALS['config']['basket_page']]['sLinkName'].((defined( 'FRIENDLY_LINKS' ) && FRIENDLY_LINKS == true)?'?':'&amp;') : null;

      $iCount = DB::GetOne('SELECT 	count(it.id) 
					FROM premium_ecommerce_products_data_1 pr
					INNER JOIN (premium_warehouse_items_data_1 it) ON (pr.f_item_name=it.id)
					LEFT JOIN premium_ecommerce_descriptions_data_1 d ON (d.f_item_name=it.id AND d.f_language="'.LANGUAGE.'" AND d.active=1)
					 WHERE pr.f_publish=1 AND pr.active=1 AND it.active=1 AND ('.$query.') ORDER BY pr.f_position');

      if( !isset( $iList ) ){
        $iList = $GLOBALS['config']['products_list'];
      }
      $iList *= $iColumns;

      $iProducts = ceil( $iCount / $iList );
      $iPageNumber = isset( $GLOBALS['aActions']['o2'] ) ? $GLOBALS['aActions']['o2'] : 1;
      if( !isset( $iPageNumber ) || !is_numeric( $iPageNumber ) || $iPageNumber < 1 )
        $iPageNumber = 1;
      if( $iPageNumber > $iProducts && $iProducts>0)
        $iPageNumber = $iProducts;

      $iStart = ($iPageNumber-1) * $iList;

      $this->mData = null;

      $_SESSION['last_products_query'] = array($query,$iList,$iStart);
      $products = $this->getProducts($query,$iList,$iStart);
      $i = 0;
      foreach($products as $aData){

        $aData['iWidth']  = $iWidth;
        $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
        $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
        $aData['sPrice'] = is_numeric( $aData['fPrice'] ) ? displayPrice( $aData['fPrice'] ) : $aData['fPrice'];
        $aData['sPriceNet'] = is_numeric( $aData['fPriceNet'] ) ? displayPrice( $aData['fPriceNet'] ) : $aData['fPriceNet'];
        $aData['sPages'] = $this->throwProductsPagesTree( $aData['aCategories'] );
        $aData['sBasket']= null;
        $aData['sRecommended'] = $aData['sRecommended']? $oTpl->tbHtml( $sFile, 'PRODUCTS_RECOMMENDED' ) : null;

        if( $i > 0 && $i % $iColumns == 0 ){
          $oTpl->setVariables( 'aData', $aData );
          $content .= $oTpl->tbHtml( $sFile, 'PRODUCTS_GALLERY_BREAK' );
        }

        if( !empty( $aData['sDescriptionShort'] ) ){
          $aData['sDescriptionShort'] = changeTxt( $aData['sDescriptionShort'], 'nlNds' );
          $oTpl->setVariables( 'aData', $aData );
          $aData['sDescriptionShort'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_DESCRIPTION' );
        }

        $oTpl->setVariables( 'aData', $aData );

        if($aDataImage = $oFile->throwDefaultImage($aData['iProduct'],2)){
          $oTpl->setVariables( 'aDataImage', $aDataImage );
          $aData['sImage'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_IMAGE' );
        }
        else{
          $aData['sImage'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_NO_IMAGE' );
        }

        if( is_numeric( $aData['fPrice'] ) ){
          if( isset( $sBasketPage ) && $aData['iQuantity']>0 ){
            $aData['sBasketPage'] = $sBasketPage;
            $oTpl->setVariables( 'aData', $aData );
            $aData['sBasket'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_BASKET' );
          }
          $oTpl->setVariables( 'aData', $aData );
          $aData['sPrice'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_PRICE' );
        } else{
          $oTpl->setVariables( 'aData', $aData );
          $aData['sPrice'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_NO_PRICE' );
        }

        $oTpl->setVariables( 'aData', $aData );
        $content .= $oTpl->tbHtml( $sFile, 'PRODUCTS_GALLERY_LIST' );
        $i++;
      } // end for

      while( $i % $iColumns > 0 ){
        $content .= $oTpl->tbHtml( $sFile, 'PRODUCTS_GALLERY_BLANK' );
        $i++;
      } // end while

      if( isset( $content ) ){
        if($manufacturers) {
          $oTpl->setVariables('manufacturers',$manufacturers);
          $oTpl->setVariables('sLinkName',throwPageUrl( $oPage->aPages[$iContent]['sLinkName'], true ).$sUrlExt);
    	  $aData['manufacturers'] = $oTpl->tbHtml( $sFile, 'PRODUCTS_MANUFACTURERS' );
        }

        if( $iCount > $iList ){
          $aData['sPages'] = countPages( $iCount, $iList, $iPageNumber, throwPageUrl( $oPage->aPages[$iContent]['sLinkName'], true ), $sUrlExt, FRIENDLY_LINKS );
          $aData['sHidePages'] = null;
        }
        else
          $aData['sHidePages'] = ' hide';

        $oTpl->setVariables( 'aData', $aData );
        return $oTpl->tbHtml( $sFile, 'PRODUCTS_GALLERY_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'PRODUCTS_GALLERY_FOOT' );
      }
    }
  } // end function listProducts

  /**
  * Return page data
  * @return array
  * @param int  $iProduct
  */
  function throwProduct( $iProduct ){
    $aData = $this->getProduct($iProduct, true);
    if( $aData ){
	    $aData['sPrice'] = is_numeric( $aData['fPrice'] ) ? displayPrice( $aData['fPrice'] ) : $aData['fPrice'];
	    $aData['sPriceNet'] = is_numeric( $aData['fPriceNet'] ) ? displayPrice( $aData['fPriceNet'] ) : $aData['fPriceNet'];
	    return $aData;
    }
    return null;
  } // end function throwProduct

  /**
  * Return products pages tree
  * @return string
  * @param int  $iProduct
  */
  function throwProductsPagesTree( $pages ){
      global $oPage;
      $content = null;
      $oPage->mData = null;
      foreach( $pages as $iPage ){
        if( isset( $content ) )
          $content .= '<em>|</em>';
        if( isset( $this->mData[$iPage] ) ){
          $content .= $this->mData[$iPage];
        }
        else{
          $sTree = $oPage->throwPagesTree( $iPage );
          if( !empty( $sTree ) )
            $sTree .= '&nbsp;&raquo;&nbsp;';
          $sTree .= '<a href="'.$oPage->aPages[$iPage]['sLinkName'].'">'.$oPage->aPages[$iPage]['sName'].'</a>';
          $content .= $this->mData[$iPage] = $sTree;
        }
      }

      return $content;
  } // end function throwProductsPagesTree

  /**
  * List products to sitemap
  * @return string
  * @param string $sFile
  * @param int    $iPage
  */
  function listSiteMap( $sFile, $iPage ){
    if( isset( $this->aProducts ) ){
      foreach( $this->aProducts as $prod){
        if( isset( $prod['aCategories'][$iPage] ) ){
          $aProducts[] = $prod['iProduct'];
        }
      }
      $aProducts = $this->getProducts('it.f_category '.DB::like().' \'%\\_\\_'.($iPage/4).'\\_\\_%\' OR it.f_category '.DB::like().' \'%/'.($iPage/4).'\\_\\_%\'');

      if( isset( $aProducts ) ){
        $oTpl   =& TplParser::getInstance( );
        $content= null;
        foreach($aProducts as $aData) {
          $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
          $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
          if( is_numeric( $aData['fPrice'] ) ){
            $aData['sPrice'] = displayPrice( $aData['fPrice'] );
            $aData['sPriceNet'] = displayPrice( $aData['fPriceNet'] );
            $oTpl->setVariables( 'aData', $aData );
            $aData['sPrice'] = $oTpl->tbHtml( $sFile, 'SITEMAP_PRODUCTS_PRICE' );
          }
          else{
            $aData['sPrice'] = $aData['fPrice'];
            $aData['sPriceNet'] = $aData['fPriceNet'];
            $oTpl->setVariables( 'aData', $aData );
            $aData['sPrice'] = $oTpl->tbHtml( $sFile, 'SITEMAP_PRODUCTS_NO_PRICE' );
          }

          $oTpl->setVariables( 'aData', $aData );
          $content .= $oTpl->tbHtml( $sFile, 'SITEMAP_PRODUCTS' );
        } // end for
        return $oTpl->tbHtml( $sFile, 'SITEMAP_HEAD_PRODUCTS' ).$content.$oTpl->tbHtml( $sFile, 'SITEMAP_FOOT_PRODUCTS' );
      }
    }
  } // end function listSiteMap

  /**
  * Return products cross sell
  * @return string
  * @param string $sFile
  * @param int    $iProduct
  */
  function listProductsCrossSell( $sFile, $iProduct ){
    $content= null;
    $oTpl   =& TplParser::getInstance( );
    
    if(!is_array($iProduct)) $iProduct = array($iProduct);
    $cache_id = md5(serialize($iProduct));
    if(file_exists('cache/cross/'.$cache_id) && filemtime('cache/cross/'.$cache_id)>time()-3600*72) {
	$products = @unserialize(file_get_contents('cache/cross/'.$cache_id));
	if(!is_array($products)) $products = array();
    } else {
        $products = DB::GetAssoc('SELECT or_det.f_item_name,count(or_det.f_item_name) FROM premium_warehouse_items_orders_details_data_1 or_det 
			    WHERE or_det.f_item_name NOT IN ('.implode(',',$iProduct).') AND or_det.f_transaction_id IN 
			    (SELECT ord.f_transaction_id FROM premium_warehouse_items_orders_details_data_1 or_det2 
			    INNER JOIN premium_ecommerce_orders_data_1 ord ON ord.f_transaction_id=or_det2.f_transaction_id
			    WHERE ord.f_language=%s AND or_det2.f_item_name IN ('.implode(',',$iProduct).')) GROUP BY or_det.f_item_name ORDER BY count(or_det.f_item_name) DESC LIMIT 5',array(LANGUAGE));
	file_put_contents('cache/cross/'.$cache_id,serialize($products));
    }

    foreach($products as $p=>$num) {
          $aData = $this->getProduct($p);
          if(!$aData) continue;
          $aData['iQuantity'] = $aSort[$i][0];
          $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
          $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
          if( is_numeric( $aData['fPrice'] ) ){
            $aData['sPrice'] = displayPrice( $aData['fPrice'] );
            $aData['sPriceNet'] = displayPrice( $aData['fPriceNet'] );
            $oTpl->setVariables( 'aData', $aData );
            $aData['sPrice'] = $oTpl->tbHtml( $sFile, 'CROSS_SELL_PRICE' );
          }
          else{
            $aData['sPrice'] = $aData['fPrice'];
            $aData['sPriceNet'] = $aData['fPriceNet'];
            $oTpl->setVariables( 'aData', $aData );
            $aData['sPrice'] = $oTpl->tbHtml( $sFile, 'CROSS_SELL_NO_PRICE' );
          }

          $oTpl->setVariables( 'aData', $aData );
          $content .= $oTpl->tbHtml( $sFile, 'CROSS_SELL_LIST' );
        } // end for

        if( isset( $content ) )
          return $oTpl->tbHtml( $sFile, 'CROSS_SELL_HEAD' ).$content.$oTpl->tbHtml( $sFile, 'CROSS_SELL_FOOT' );
  } // end function listProductsCrossSell
}
?>