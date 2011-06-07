<?php
if( isset( $iContent ) && is_numeric( $iContent ) ){
  $aData = $oPage->throwPage( $iContent );
  if( isset( $aData ) ){
    if( !empty( $aData['sUrl'] ) ){
      header( 'Location: '.$aData['sUrl'] );
      exit;
    }

    if( !empty( $aData['sTemplate'] ) )
      $oTpl->setFileAlt( $config['default_pages_template'] );
    else{
      if( $config['inherit_from_parents'] === true && !empty( $aData['iPageParent'] ) ){
        $aDataParent = $oPage->throwPage( $aData['iPageParent'] );
        if( !empty( $aDataParent['sTemplate'] ) ){
          $aData['sTemplate'] = $aDataParent['sTemplate'];
          $oTpl->setFileAlt( $config['default_pages_template'] );
        }
      }
      if( empty( $aData['sTemplate'] ) )
        $aData['sTemplate'] = $config['default_pages_template'];

    if( isset( $config['site_map'] ) && is_numeric( $config['site_map'] ) && $iContent == $config['site_map'] ){
      $sSiteMap = $oPage->listSiteMap( $aData['sTemplate'] );
      $oTpl->unsetVariables( );
    }
    else{
      $sSiteMap = null;
    }

    if( !empty( $aData['iComments'] ) && $aData['iComments'] == 1 ){
      if( isset( $_POST['sOption'] ) && $_POST['sOption'] == 'saveComment' && !empty( $_POST['sContent'] ) && !preg_match( "/\[url|<a href=|<a href=\"h/i", $_POST['sContent'] ) && !empty( $_POST['sName'] ) ){
        addComment( $_POST, $iContent );
        $sIndex = ( !isset( $config['index'] ) || ( isset( $config['index'] ) && $config['index'] == '?' ) ) ? REDIRECT : null;
        $sAnd = isset( $sIndex ) ? '&' : '?';
        header( 'Location: '.$sIndex.$aData['sLinkName'].$sAnd.'iCommentAdded=1' );
        exit;
      }

      $sCommentsList = $oTpl->tbHtml( $aData['sTemplate'], 'COMMENTS_TITLE' );
      $sCommentsList .= listComments( $aData['sTemplate'], $iContent );
      $oTpl->unsetVariables( );
      $sCommentsForm = isset( $iCommentAdded ) ? $oTpl->tbHtml( 'messages.tpl', 'COMMENT_ADDED' ) : $oTpl->tbHtml( $aData['sTemplate'], 'COMMENTS_FORM' );
    }
    else{
      $sCommentsForm = null;
      $sCommentsList = null;
    }

    $sUserPanel = '';
    if( isset( $config['contact_page'] ) && is_numeric( $config['contact_page'] ) && $iContent == $config['contact_page'] ){
      $sUserPanel = isset( $_POST['sSend'] ) ? sendEmail( $_POST ): $oTpl->tbHtml( $aData['sTemplate'], 'CONTACT_FORM' );
    } elseif( $iContent == 39 ){ //login form
      if(isset( $_POST['sSend'] )) {
      	if(!$oUser->login($_POST)) {
            $sUserPanel .= $oTpl->tbHtml( 'messages.tpl', 'PASSWORD_INVALID' );          
        }
      }
      if(isset($_SESSION['mail']) && !isset($_POST['sEmail'])) {
      	$_POST['sEmail'] = $_SESSION['mail'];
      	unset($_SESSION['mail']);
      }
      $sUserPanel .= $oTpl->tbHtml( $aData['sTemplate'], 'LOGIN_FORM' );
    } elseif( $iContent == 59 ){ //password reminder form
      $display_form = true;
      if(isset( $_POST['sSend'] )) {
      	if(!$oUser->remind_password($_POST,$aData['sTemplate'])) {
            $sUserPanel .= $oTpl->tbHtml( 'messages.tpl', 'EMAIL_INVALID' );          
        } else {
            $display_form = false;
            $sUserPanel .= $oTpl->tbHtml( 'messages.tpl', 'NEW_PASSWORD_SENT' );          
        }
      }
      if($display_form)
        $sUserPanel .= $oTpl->tbHtml( $aData['sTemplate'], 'PASSWORD_REMINDER_FORM' );
    } elseif( $iContent == 51 ){ //change password form
      if(isset( $_POST['sSend'] )) {
      	if(!$oUser->change_password($_POST)) {
            $sUserPanel .= $oTpl->tbHtml( 'messages.tpl', 'PASSWORD_INVALID' );          
      	}
      }
      $sUserPanel .= $oTpl->tbHtml( $aData['sTemplate'], 'CHANGE_PASSWORD_FORM' );
    } elseif( $iContent == 55 ){ //orders
      $sUserPanel = $oUser->orders('orders_panel.tpl');
    } elseif( $iContent == 47 ){ //logout
      if($oUser->logged()) {
      	$oUser->logout();
      }
    }

    if( !empty( $aData['iRss'] ) && isset( $oPage->aPagesChildrens[$iContent] ) ){
      $sRssUrl  = throwRssUrl( $iContent, $aData['sLinkName'] );
      $sRssIco  = $oTpl->tbHtml( $aData['sTemplate'], 'RSS' );
      $sRssMeta = $oTpl->tbHtml( $aData['sTemplate'], 'RSS_META' );
    }
    else{
      $sRssIco = null;
      $sRssMeta = null;
    }
    }

    if( !empty( $aData['sTheme'] ) )
      $sTheme = $aData['sTheme'];
    else{
      if( $config['inherit_from_parents'] === true && !empty( $aData['iPageParent'] ) ){
        if( !isset( $aDataParent ) )
          $aDataParent = $oPage->throwPage( $aData['iPageParent'] );
        if( !empty( $aDataParent['sTheme'] ) )
          $sTheme = $aDataParent['sTheme'];
      }
    }
    if( !empty( $aData['sMetaKeywords'] ) )
      $sKeywords = $aData['sMetaKeywords'];
    if( !empty( $aData['sMetaDescription'] ) )
      $sDescription = $aData['sMetaDescription'];
    if( empty( $aData['sDescriptionFull'] ) )
      $aData['sDescriptionFull'] = $aData['sDescriptionShort'];

    $aData['sPagesTree'] = $oPage->throwPagesTree( $iContent );

    $sTxtSize = ( $config['text_size'] == true ) ? $oTpl->tbHtml( $aData['sTemplate'], 'TXT_SIZE' ) : null;
    $sPagesTree = !empty( $aData['sPagesTree'] ) ? $oTpl->tbHtml( $aData['sTemplate'], 'PAGES_TREE' ) : null;
    $sPages     = isset( $aData['sPages'] ) ? $oTpl->tbHtml( $aData['sTemplate'], 'PAGES' ) : null;
    $sBanner    = !empty( $aData['sBanner'] ) ? $oTpl->tbHtml( $aData['sTemplate'], 'BANNER' ) : null;
    $sTitle     = strip_tags( ( !empty( $aData['sNameTitle'] ) ? $aData['sNameTitle'] : $aData['sName'] ).' - ' );
    $sSubpagesList = null;
    // display products in page
    $iProductsList = isset( $bViewAll ) ? 999 : null;
    if( $aData['iProducts'] == 1 || ( isset( $sPhrase ) && $config['page_search'] == $iContent ) ) {
    	if($aData['iSubpagesShow'] == 4)
	    	$sProductsList = $oProduct->listProductsGallery( $aData['sTemplate'], $iContent, $iProductsList );    	
    	else
	    	$sProductsList = $oProduct->listProducts( $aData['sTemplate'], $iContent, $iProductsList );
    } else {
    	 $sProductsList = null;
    }

    if( isset( $sPhrase ) && $config['page_search'] == $iContent && empty( $sProductsList ) )
      $sProductsList = $oTpl->tbHtml( 'messages.tpl', 'ERROR' );

    $aData['sDescriptionFull'] = changeTxt( $aData['sDescriptionFull'], 'nlNds' );
    if( $aData['iSubpagesShow'] > 0 ){
      if( $aData['iSubpagesShow'] < 3 )
        $sSubpagesList = $oPage->listSubpages( $iContent, $aData['sTemplate'], $aData['iSubpagesShow'] );
      elseif( $aData['iSubpagesShow'] == 3 )
        $sSubpagesList = $oPage->listSubpagesNews( $iContent, $aData['sTemplate'] );
      elseif( $aData['iSubpagesShow'] == 4 )
        $sSubpagesList = $oPage->listSubpagesGallery( $iContent, $aData['sTemplate'] );
    }

    if( $config['basket_page'] == $iContent ){
      // basket
      if( isset( $_POST['aProducts'] ) ){
        // save basket
        $oOrder->saveBasket( $_POST );
        if( isset( $_POST['sSave'] ) ){
          setCookie( 'sCustomer'.LANGUAGE, md5( $_SESSION['iCustomer'.LANGUAGE] ), time( ) + 259200 );
        }
        if( isset( $_POST['sNext'] ) && isset( $config['order_page'] ) && isset( $oPage->aPages[$config['order_page']] ) ){
          header( 'Location: '.REDIRECT.$oPage->aPages[$config['order_page']]['sLinkName'] );
          exit;
        }
      }
      if( isset( $iProductDelete ) && is_numeric( $iProductDelete ) ){
        // delete product from basket
        $oOrder->deleteFromBasket( $iProductDelete );
        if(isset($_REQUEST['ajax'])) {
            $oOrder->generateBasket( );
            $qty = 0;
            foreach($oOrder->aProducts as $p)
                $qty += $p['iQuantity'];
            print('$("basketNumProducts").innerHTML = "'.$qty.'";elem.style.display="none";elem.previousSibling.style.display="inline";elem.disabled=0;');
            exit;
        }
      }
      if( isset( $_POST['iProductAdd'] ) && isset( $_POST['iQuantity'] ) ){
        $iProductAdd = $_POST['iProductAdd'];
        $iQuantity = $_POST['iQuantity'];
      }
      $prod = $oProduct->getProduct($iProductAdd);
      if( isset( $iProductAdd ) && is_numeric( $iProductAdd ) && isset( $iQuantity ) && is_numeric( $iQuantity ) && $iQuantity > 0 && $iQuantity < 10000 && $prod && is_numeric( $prod['fPrice'] ) ){
        // add product to basket
        $oOrder->addToBasket( $iProductAdd, $iQuantity );
        if(isset($_REQUEST['ajax'])) {
            $oOrder->generateBasket( );
            $qty = 0;
            foreach($oOrder->aProducts as $p)
                $qty += $p['iQuantity'];
            print('$("basketNumProducts").innerHTML = "'.$qty.'";elem.style.display="none";elem.nextSibling.style.display="inline";elem.disabled=0;');
            if($_REQUEST['ajax']=='2')
                print('alert("'.$lang['Product_added_to_basket'].'");');
        } else {
            header( 'Location: '.REDIRECT.$aData['sLinkName'] );
        }
        exit;
      }
      // display basket
      $sBasketList = $oOrder->listProducts( 'orders_basket.tpl' );
      if( !isset( $sBasketList ) )
        $sBasketList = $oTpl->tbHtml( 'orders_basket.tpl', 'BASKET_EMPTY' );
      else {
        require_once DIR_CORE.'productsRelated.php';
        $prods = array();
        foreach($oOrder->aProducts as $p) $prods[] = $p['iProduct'];
        $sProductsRelated = listProductsRelated( $config['default_products_template'], $prods );
        $sCrossSell = ( isset( $config['cross_sell'] ) && $config['cross_sell'] === true ) ? $oProduct->listProductsCrossSell( $config['default_products_template'], $prods ) : null;
        $sBasketList .= '<br/>'.$sProductsRelated.'<br/>'.$sCrossSell;
      }
    }
    else{
      $sBasketList = null;
    }

    if( $config['order_page'] == $iContent ){
      // order
      if( $oOrder->checkEmptyBasket( ) === false ){
        if( isset( $_POST['sOrderSend'] ) ){
          $checkFieldsRet = $oOrder->checkFields( $_POST );
          if( $checkFieldsRet === true ){
            // save and print order
            $iOrder = $oOrder->addOrder( $_POST );
            if( !empty( $config['email'] ) ){
              $oOrder->sendEmailWithOrderDetails( 'orders_print.tpl', $iOrder );
            }

            $aOrder = $oOrder->throwOrder( $iOrder );
            $aOrder['sComment'] = preg_replace( '/\|n\|/', '<br />' , $aOrder['sComment'] );
            $sOrderProducts = $oOrder->listProducts( 'orders_print.tpl', $iOrder, 'ORDER_PRINT_' );

            if( !empty( $aOrder['iPaymentSystem'] ) && isset( $aOuterPaymentOption[$aOrder['iPaymentSystem']] ) ){
              $aUrl = parse_url( 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] );
              if( !empty( $aUrl['path'] ) )
                $aUrl['path'] = dirname( $aUrl['path'] ).'/';
              $aStreet = throwStreetDetails( $aOrder['sStreet'] );
              if( isset( $oOrder->aOrders[$iOrder]['fOrderSummary'] ) )
                $iAmount =  sprintf( '%01.2f', $oOrder->aOrders[$iOrder]['fOrderSummary'] ) * 100;
              if( $aOrder['iPaymentSystem'] == 5 )
                $sProductsZagielList = $oOrder->listProducts( 'payment.tpl', $iOrder, 'ZAGIEL_' );
              $sPaymentOuterForm = $oTpl->tbHtml( 'payment.tpl', 'PAYMENT_FORM_'.$aOrder['iPaymentSystem'] );
              $sPaymentOuter = $oTpl->tbHtml( 'payment.tpl', 'PAYMENT_OUTER' );
            }
            $aPayment = $oOrder->throwPaymentCarrier( $aOrder['iCarrier'], $aOrder['iPayment'] );
            if( !empty( $aPayment['sDescription'] ) || !empty( $sPaymentOuter ) )
              $sPaymentDescription = $oTpl->tbHtml( 'orders_print.tpl', 'ORDER_PRINT_PAYMENT' );
            $sOrder = $oTpl->tbHtml( 'orders_print.tpl', 'ORDER_PRINT' );
          } elseif( $checkFieldsRet === 'promotion_invalid' ){
            $sOrderError = $oTpl->tbHtml( 'messages.tpl', 'PROMOTION_INVALID' );          
          } elseif( $checkFieldsRet === 'promotion_invalid' ){
            $sOrderError = $oTpl->tbHtml( 'messages.tpl', 'PROMOTION_INVALID' );          
          } elseif( $checkFieldsRet === 'password_mismatch' ){
            $sOrderError = $oTpl->tbHtml( 'messages.tpl', 'PASSWORD_MISMATCH' );          
          } elseif( $checkFieldsRet === 'basket_empty' ){
            $ppid = $config['basket_page'];
            if(isset($_REQUEST['ajax']))
        	print('alert(\''.addcslashes($GLOBALS['lang']['Stock_exceeded_basket_empty'],'\\\'').'\');var endOfRef = window.location.href.length;if (window.location.search.length > 0 && window.location.href.indexOf(window.location.search) > 0)endOfRef = window.location.href.indexOf(window.location.search);window.location.replace(window.location.href.substring(0,endOfRef)+"?'.change2Url( $oPage->aPages[$ppid]['sName'] ).','.$ppid.'");');
            else
                $sOrder = '<script type="text/javascript">alert(\''.addcslashes($GLOBALS['lang']['Stock_exceeded_basket_empty'],'\\\'').'\');var endOfRef = window.location.href.length;if (window.location.search.length > 0 && window.location.href.indexOf(window.location.search) > 0)endOfRef = window.location.href.indexOf(window.location.search);window.location.replace(window.location.href.substring(0,endOfRef)+"?'.change2Url( $oPage->aPages[$ppid]['sName'] ).','.$ppid.'");</script>';
          } elseif( $checkFieldsRet === 'stock_exceeded' ){
            $ppid = $config['basket_page'];
            if(isset($_REQUEST['ajax']))
        	print('alert(\''.addcslashes($GLOBALS['lang']['Stock_exceeded_some'],'\\\'').'\');var endOfRef = window.location.href.length;if (window.location.search.length > 0 && window.location.href.indexOf(window.location.search) > 0)endOfRef = window.location.href.indexOf(window.location.search);window.location.replace(window.location.href.substring(0,endOfRef)+"?'.change2Url( $oPage->aPages[$ppid]['sName'] ).','.$ppid.'");');
            else
                $sOrder = '<script type="text/javascript">alert(\''.addcslashes($GLOBALS['lang']['Stock_exceeded_some'],'\\\'').'\');var endOfRef = window.location.href.length;if (window.location.search.length > 0 && window.location.href.indexOf(window.location.search) > 0)endOfRef = window.location.href.indexOf(window.location.search);window.location.replace(window.location.href.substring(0,endOfRef)+"?'.change2Url( $oPage->aPages[$ppid]['sName'] ).','.$ppid.'");</script>';
          } else {
            $sOrderError = $oTpl->tbHtml( 'messages.tpl', 'REQUIRED_FIELDS' );
          }
        }
        if( !isset( $_POST['sOrderSend'] ) || isset($sOrderError)){
          // display order form
          $oTpl->unsetVariables( );
          $sRules = null;
          if( isset( $config['rules_page'] ) && isset( $oPage->aPages[$config['rules_page']] ) ){
            $aRules = $oPage->aPages[$config['rules_page']];
            $sRules = $oTpl->tbHtml( 'orders_form.tpl', 'RULES_ACCEPT' );
          }
          $sOrderProducts = $oOrder->listProducts( 'orders_form.tpl', null, 'ORDER_PRODUCTS_' );
          $sPaymentCarriers = $oOrder->listCarriersPayments( 'orders_form.tpl' );
          $sPickupShops = $oOrder->listPickupShops( 'orders_form.tpl' );
          $oTpl->unsetVariables( );

	  if(!$oUser->logged())
	          $sNewAccount = $oTpl->tbHtml( 'orders_form.tpl', 'ORDER_NEW_ACCOUNT');
	  
	  $countries_id = DB::GetOne('SELECT id FROM utils_commondata_tree WHERE akey="Countries"');
	  if($countries_id===false)
		die('Common data key "Countries" not defined.');
	  $countries = DB::GetAssoc('SELECT p.akey, p.value FROM utils_commondata_tree p WHERE p.parent_id=%d ORDER BY akey',array($countries_id));
	  global $translations;
	  foreach($countries as $k=>$v) {
		if(isset($translations['Utils_CommonData'][$v]) && $translations['Utils_CommonData'][$v])
			$countries[$k] = $translations['Utils_CommonData'][$v];
	  }
	  foreach($countries as $k=>$v) {
		$countriesList .= '<option name="sAddress" value="'.$k.'" '.(strtolower($k)==LANGUAGE?'selected="1"':'').'>'.$v.'</option>';
	  }
	  
	  $oTpl->setVariables('countriesList',$countriesList);
	  
	  $addresses = $oUser->throwAddresses();
	  if($addresses) {
    	  $addressesList = '<select onChange="changeAddr(this.value)">';
    	  foreach($addresses as $id=>$addr) {
    	    if(!$addr['sFirstName'] || !$addr['sLastName']) continue;
    	    $addressesList .= '<option value="'.$id.'" '.($id=='default'?'selected':'').'>'.$addr['sFirstName'].' '.$addr['sLastName'].($addr['sCompanyName']?', '.$addr['sCompanyName']:'').($addr['sStreet']?', '.$addr['sStreet']:'').($addr['sCity']?', '.$addr['sCity']:'').($addr['sZipCode']?', '.$addr['sZipCode']:'').'</option>';
    	  }
   	      $addressesList .= '<option value="new">-- '.$GLOBALS['lang']['New_address'].' --</option>';
   	      $addresses['new'] = array('sFirstName'=>'','sLastName'=>'','sCompanyName'=>'','sStreet'=>'','sCity'=>'','sCountry'=>'','sZipCode'=>'');
	      $addressesList .= '</select><script type="text/javascript">var addressesList='.json_encode($addresses).';function changeAddr(val){var a=addressesList[val];for(p in a){document.forms["orderForm"].elements[p].value=a[p]}}</script>';
	      $oTpl->setVariables('addressesList',$addressesList);
	  }
	  
          $sOrder = $sOrderError.$oTpl->tbHtml( 'orders_form.tpl', 'ORDER_FORM'.($oUser->logged()?'_LOGGED':''));
        }
      }
      else{
        $sOrder = $oTpl->tbHtml( 'orders_basket.tpl', 'BASKET_EMPTY' );
      }
    }
    else{
      $sOrderForm = null;
    }

    $aImages    = $oFile->listImagesByTypes( $aData['sTemplate'], $iContent );
    $sFilesList = $oFile->listFiles( $aData['sTemplate'], $iContent );

    $oTpl->unsetVariables( );
    $content .= $oTpl->tbHtml( $aData['sTemplate'], 'CONTAINER' );
    savePageStat( $iContent );
  }
  else{
    $content .= $oTpl->tbHtml( 'messages.tpl', 'ERROR' );
  }
}
?>