<?php
require_once DIR_CORE.'features.php';
if( isset( $aActions['a'] ) && is_numeric( $aActions['a'] ) ){

  require_once DIR_CORE.'productsRelated.php';
  $iProduct = $aActions['a'];
  $sBasket  = null;
  $aData = $oProduct->throwProduct( $iProduct );
  if( isset( $aData ) ){
    if( !empty( $aData['sTemplate'] ) )
      $oTpl->setFileAlt( $config['default_products_template'] );
    else
      $aData['sTemplate'] = $config['default_products_template'];

    if( !empty( $aData['iComments'] ) && $aData['iComments'] == 1 ){
      if( isset( $_POST['sOption'] ) && $_POST['sOption'] == 'saveComment' && !empty( $_POST['sContent'] ) && !preg_match( "/\[url|<a href=/i", $_POST['sContent'] ) && !empty( $_POST['sName'] ) ){
        addComment( $_POST, $iProduct, true);
        $sIndex = ( !isset( $config['index'] ) || ( isset( $config['index'] ) && $config['index'] == '?' ) ) ? $_SERVER['PHP_SELF'] : null;
        $sAnd = isset( $sIndex ) ? '&' : '?';
        header( 'Location: '.$sIndex.$aData['sLinkName'].$sAnd.'iCommentAdded=1' );
        exit;
      }

      $sCommentsList = $oTpl->tbHtml( $aData['sTemplate'], 'COMMENTS_TITLE' );
      $sCommentsList .= listComments( $aData['sTemplate'], $iProduct, true );
      $oTpl->unsetVariables( );
      $sCommentsForm = isset( $iCommentAdded ) ? $oTpl->tbHtml( 'messages.tpl', 'COMMENT_ADDED' ) : $oTpl->tbHtml( $aData['sTemplate'], 'COMMENTS_FORM' );
    }
    else{
      $sCommentsForm = null;
      $sCommentsList = null;
    }

    if( !empty( $aData['sTheme'] ) )
      $sTheme = $aData['sTheme'];
    if( !empty( $aData['sMetaKeywords'] ) )
      $sKeywords = $aData['sMetaKeywords'];
    if( !empty( $aData['sMetaDescription'] ) )
      $sDescription = $aData['sMetaDescription'];
    if( empty( $aData['sDescriptionFull'] ) )
      $aData['sDescriptionFull'] = $aData['sDescriptionShort'];
    $aData['sDescriptionFull'] = changeTxt( $aData['sDescriptionFull'], 'nlNds' );

    $aData['sPages'] = $oProduct->throwProductsPagesTree( $aData['aCategories'] );

    $sTxtSize   = ( $config['text_size'] == true ) ? $oTpl->tbHtml( $aData['sTemplate'], 'TXT_SIZE' ) : null;
    $sAvailable = !empty( $aData['sAvailable'] ) ? $oTpl->tbHtml( $aData['sTemplate'], 'AVAILABLE' ) : null;
    $sRecommended = $aData['sRecommended'] ? $oTpl->tbHtml( $aData['sTemplate'], 'RECOMMENDED' ) : null;
    $sNextProduct = $aData['sNextLinkName'] ? $oTpl->tbHtml( $aData['sTemplate'], 'NEXT_PRODUCT' ) : null;
    $sPrevProduct = $aData['sPrevLinkName'] ? $oTpl->tbHtml( $aData['sTemplate'], 'PREV_PRODUCT' ) : null;
    $sTitle     = strip_tags( ( !empty( $aData['sNameTitle'] ) ? $aData['sNameTitle'] : $aData['sName'] ).' - ' );

    $aImages    = $oFile->listImagesByTypes( $aData['sTemplate'], $iProduct, 2 );
    $sFilesList = $oFile->listFiles( $aData['sTemplate'], $iProduct, 2 );

    $sProductsRelated = listProductsRelated( $aData['sTemplate'], $iProduct );
    $sPopupProducts = listProductsRelated( $aData['sTemplate'], $iProduct, 'popup' );
    $sFeatures  = listProductFeatures( $aData['sTemplate'], $iProduct );
    $sCrossSell = ( isset( $config['cross_sell'] ) && $config['cross_sell'] === true ) ? $oProduct->listProductsCrossSell( $aData['sTemplate'], $iProduct ) : null;

    $oTpl->unsetVariables( );

    if( is_numeric( $aData['fPrice'] ) && $aData['iQuantity'] ){
      if( isset( $config['basket_page'] ) && isset( $oPage->aPages[$config['basket_page']] ) ){
        $sBasketPage = $oPage->aPages[$config['basket_page']]['sLinkName'];
        if($sPopupProducts)
            $sBasket = $oTpl->tbHtml( $aData['sTemplate'], 'BASKET_POPUP' );
        else
            $sBasket = $oTpl->tbHtml( $aData['sTemplate'], 'BASKET' );
      }
      $sPrice = $oTpl->tbHtml( $aData['sTemplate'], 'PRICE' );
    } elseif( is_numeric( $aData['fPrice'] ) ) {
      $sPrice = $oTpl->tbHtml( $aData['sTemplate'], 'OUT_OF_STOCK' ). $oTpl->tbHtml( $aData['sTemplate'], 'PRICE' );    
    } else {
      $sPrice = $oTpl->tbHtml( $aData['sTemplate'], 'NO_PRICE' );
      $sAvailable = null;
    }

    $content .= $oTpl->tbHtml( $aData['sTemplate'], 'CONTAINER' );
    saveProductStat( $iProduct );
  }
  else{
    $content .= $oTpl->tbHtml( 'messages.tpl', 'ERROR' );
  }
}
?>