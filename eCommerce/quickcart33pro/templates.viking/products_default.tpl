<!-- BEGIN CONTAINER -->
<script type="text/javascript">
<!--
  var sTitle = "$aData[sName]";
  var fPrice = Math.abs( "$aData[fPrice]" );
//-->
</script>
<div id="product">
  $sTxtSize
  <h1>$aData[sName]</h1>
  $sRecommended
  <h4>$aData[sPages]</h4>
  $aImages[3]
  $aImages[1]
  <div id="box">
    $sPrice
    $sAvailable
    $sBasket
  </div>
  $aImages[2]
  <div class="content" id="productDescription">$aData[sDescriptionFull]</div>
  $sFeatures
  $sFilesList
  $aImages[4]
  $sProductsRelated
  $sCrossSell
  $sCommentsList
  $sCommentsForm
</div>
<!-- END CONTAINER -->
<!-- BEGIN AVAILABLE -->
<div id="available">$aData[sAvailable]</div>
<!-- END AVAILABLE -->

<!-- BEGIN RECOMMENDED --><div class="recommended"><img src="$config[dir_templates]img/recommended.png" alt="$lang[Recommended]" title="$lang[Recommended]" /></div><!-- END RECOMMENDED -->

<!-- BEGIN TXT_SIZE -->
<div class="tS"><div><a href="javascript:txtSize( 0 )" class="tS0">A</a></div><div><a href="javascript:txtSize( 1 )" class="tS1">A</a></div><div><a href="javascript:txtSize( 2 )" class="tS2">A</a></div></div>
<!-- END TXT_SIZE -->

<!-- BEGIN BASKET -->
<form action="$sBasketPage" method="post" id="addBasket">
  <fieldset>
    <input type="hidden" name="iProductAdd" value="$aData[iProduct]" />
    <input type="hidden" name="iQuantity" value="1" />
    <input type="submit" value="$lang[Basket_add]" class="submit" />
  </fieldset>
</form>
<!-- END BASKET -->

<!-- BEGIN BASKET_AJAX -->
<button class="submit" $aData[sDisplayAddButton] onClick="var elem=this;this.disabled=1;new ajax('$sBasketPage&amp;ajax=1&amp;iProductAdd=$aData[iProduct]&amp;iQuantity=1',{onComplete:function(res){eval(res.responseText)}})">$lang[Basket_add]</button><button class="submit" $aData[sDisplayDeleteButton] onClick="var elem=this;this.disabled=1;new ajax('$sBasketPage&amp;ajax=1&amp;iProductDelete=$aData[iProduct]&amp;iQuantity=1',{onComplete:function(res){eval(res.responseText)}})">$lang[Basket_delete]</button>
<!-- END BASKET_AJAX -->

<!-- BEGIN BASKET_POPUP -->
<div id="basketPopup" style="display:none;width:600px;">
<h1>$lang[Product_added_to_basket]: <br />$aData[sName]</h1>
<div style="clear:both">&nbsp;</div>
$sPopupProducts
<span class="prev"><a href="javascript:void(0)" onClick="myLightbox.end();Element.show('bottomNavClose')">$lang[Continue_shopping]</a></span>
<span class="next"><a href="$sBasketPage" rel="nofollow">$lang[Show_basket]</a></span>
<div style="clear:both">&nbsp;</div>
</div>
<a rel="lightbox" style="display:none" href="#basketPopup" id="basketPopupL"></a>
<div id="addBasket">
<button class="submit" onClick="myLightbox.start(document.getElementById('basketPopupL'));Element.hide('bottomNavClose');new ajax('$sBasketPage&amp;ajax=1&amp;iProductAdd=$aData[iProduct]&amp;iQuantity=1',{onComplete:function(res){eval(res.responseText)}})">$lang[Basket_add]</button>
</div>
<!-- END BASKET_POPUP -->

<!-- BEGIN PRICE --><div id="price"><em>$lang['Price']:</em><strong id="priceValue">&#36;&nbsp;$aData[sPrice]</strong></div><!-- END PRICE -->
<!-- BEGIN NO_PRICE --><div id="noPrice">$lang['Call_for_price']</div><!-- END NO_PRICE -->
<!-- BEGIN OUT_OF_STOCK --><div id="noPrice">$lang['Out_of_stock']</div><!-- END OUT_OF_STOCK -->

<!-- BEGIN FILES_LIST --><li class="l$aData[sStyle]"><img src="$config[dir_files]ext/$aData[sIcon].gif" alt="ico" /><a href="$config[dir_files]$aData[sFileName]">$aData[sFileName]</a>$aData[sDescriptionContent]</li><!-- END FILES_LIST -->
<!-- BEGIN FILES_DESCRIPTION -->, <em>$aData[sDescription]</em><!-- END FILES_DESCRIPTION -->
<!-- BEGIN FILES_HEAD --><ul id="filesList"><!-- END FILES_HEAD -->
<!-- BEGIN FILES_FOOT --></ul><!-- END FILES_FOOT -->

<!-- BEGIN IMAGES_LIST_1 --><li><a href="$config[dir_files]$aData[sFileName]" rel="lightbox-product" title="$aData[sDescription]"><img src="$config[dir_files]$aData[iSizeValue2]/$aData[sFileName]" alt="$aData[sDescription]" /></a>$aData[sDescriptionContent]</li><!-- END IMAGES_LIST_1 -->
<!-- BEGIN IMAGES_DESCRIPTION_1 --><div>$aData[sDescription]</div><!-- END IMAGES_DESCRIPTION_1 -->
<!-- BEGIN IMAGES_HEAD_1 --><ul class="imagesList" id="imagesList1"><!-- END IMAGES_HEAD_1 -->
<!-- BEGIN IMAGES_FOOT_1 --></ul><!-- END IMAGES_FOOT_1 -->

<!-- BEGIN IMAGES_LIST_2 --><li><a href="$config[dir_files]$aData[sFileName]" rel="lightbox-product" title="$aData[sDescription]"><img src="$config[dir_files]$aData[iSizeValue2]/$aData[sFileName]" alt="$aData[sDescription]" /></a>$aData[sDescriptionContent]</li><!-- END IMAGES_LIST_2 -->
<!-- BEGIN IMAGES_DESCRIPTION_2 --><div>$aData[sDescription]</div><!-- END IMAGES_DESCRIPTION_2 -->
<!-- BEGIN IMAGES_HEAD_2 --><ul class="imagesList" id="imagesList2"><!-- END IMAGES_HEAD_2 -->
<!-- BEGIN IMAGES_FOOT_2 --></ul><!-- END IMAGES_FOOT_2 -->

<!-- BEGIN FEATURES_LIST --><tr class="l$aData[iStyle]">
<!--{ epesi -->
  <th>
    $aData[sGroup]
  </th>
<!--} epesi -->
  <th>
    $aData[sName]
  </th>
  <td>
    $aData[sValue]
  </td>
</tr><!-- END FEATURES_LIST -->
<!-- BEGIN FEATURES_HEAD -->
<table id="features" cellspacing="1">
  <thead>
    <tr>
<!-- epesi was <td colspan="2"> -->
      <td colspan="3">
        $lang[Features]
      </td>
    </tr>
  </thead>
  <tbody>
<!-- END FEATURES_HEAD -->
<!-- BEGIN FEATURES_FOOT --></tbody></table><!-- END FEATURES_FOOT -->

<!-- BEGIN CROSS_SELL_LIST -->
  <li class="l$aData[sStyle]"><a href="$aData[sLinkName]">$aData[sName]</a>$aData[sPrice]</li>
<!-- END CROSS_SELL_LIST -->
<!-- BEGIN CROSS_SELL_PRICE --><strong>&#36;&nbsp;$aData[sPrice]</strong><!-- END CROSS_SELL_PRICE -->
<!-- BEGIN CROSS_SELL_NO_PRICE --><strong class="noPrice">$aData[sPrice]</strong><!-- END CROSS_SELL_NO_PRICE -->
<!-- BEGIN CROSS_SELL_HEAD -->
<div id="crossSell">
  <h3>$lang[Cross_sell_info]:</h3>
  <ul>
<!-- END CROSS_SELL_HEAD -->
<!-- BEGIN CROSS_SELL_FOOT -->
  </ul>
</div>
<!-- END CROSS_SELL_FOOT -->

<!-- BEGIN COMMENTS_FORM -->
<script type="text/javascript" src="$config[dir_core]checkForm.js"></script>
<form action="$aData[sLinkName]" method="post" id="commentForm" onsubmit="return checkForm( this );">
  <fieldset>
  <input type="hidden" name="sOption" value="saveComment" />
  <table cellspacing="0">
    <tr>
      <th>
        <label for="comment_name">$lang[Name_and_surname]</label>
      </th>
      <td>
        <input type="text" name="sName" class="input" value="$_SESSION[sUserName]" id="comment_name" alt="simple" maxlength="40" size="30" />
      </td>
    </tr>
    <tr>
      <th>
        <label for="comment_text">$lang[Comment_content]</label>
      </th>
      <td>
        <textarea cols="45" rows="7" name="sContent" id="comment_text" title="simple"></textarea>
      </td>
    </tr>
    <tr class="save">
      <th></th>
      <td>
        <input type="submit" value="$lang[Add_comment]" class="submit" />
      </td>
    </tr>
  </table>
  </fieldset>
</form>
<!-- END COMMENTS_FORM -->

<!-- BEGIN COMMENTS_TITLE -->
<h3 id="commentTitle">$lang[Comments]:</h3>
<!-- END COMMENTS_TITLE -->
<!-- BEGIN COMMENTS_LIST --><tr class="l$aData[iStyle]">
  <th>
    <h6>$aData[sName]</h6>
    <p>$aData[sDate]</p>
  </th>
  <td>
    $aData[sContent]
  </td>
</tr><!-- END COMMENTS_LIST -->
<!-- BEGIN COMMENTS_HEAD --><table cellspacing="0" id="comments"><!-- END COMMENTS_HEAD -->
<!-- BEGIN COMMENTS_FOOT --></table><!-- END COMMENTS_FOOT -->

<!-- BEGIN GALLERY_LIST --><td style="width:$aData[iWidth]%;">
  <p><a href="$config[dir_files]$aData[sFileName]" rel="lightbox-page" title="$aData[sDescription]"><img src="$config[dir_files]$aData[iSizeValue2]/$aData[sFileName]" alt="$aData[sDescription]" /></a></p>
  $aData[sDescriptionContent]
</td><!-- END GALLERY_LIST -->
<!-- BEGIN GALLERY_DESCRIPTION --><div>$aData[sDescription]</div><!-- END GALLERY_DESCRIPTION -->
<!-- BEGIN GALLERY_BREAK --></tr><tr><!-- END GALLERY_BREAK -->
<!-- BEGIN GALLERY_BLANK --><td>&nbsp;</td><!-- END GALLERY_BLANK -->
<!-- BEGIN GALLERY_HEAD --><table id="imagesGallery$aData[iType]" class="imagesGallery" cellspacing="0"><tr><!-- END GALLERY_HEAD -->
<!-- BEGIN GALLERY_FOOT --></tr></table><!-- END GALLERY_FOOT -->

<!-- BEGIN RELATED_HEAD -->
<div class="clear">&nbsp;</div>
<h3 class="productsRelated">$lang[Products_related_client]</h3>
<table id="productsRelated" cellspacing="0">
  <tr>
<!-- END RELATED_HEAD -->
<!-- BEGIN RELATED_LIST -->
  <td style="width:$aData[iWidth]%;">
    $aData[sImage]
    <a href="$aData[sLinkName]" title="$aData[sName]">$aData[sName]</a>
    $aData[sPrice]
    $aData[sBasket]
  </td>
<!-- END RELATED_LIST -->
<!-- BEGIN RELATED_PRICE --><div class="price"><strong>&#36;&nbsp;$aData[sPrice]</strong></div><!-- END RELATED_PRICE -->
<!-- BEGIN RELATED_BREAK -->
  </tr>
  <tr>
<!-- END RELATED_BREAK -->
<!-- BEGIN RELATED_BLANK -->
  <td>
    &nbsp;
  </td>
<!-- END RELATED_BLANK -->
<!-- BEGIN RELATED_IMAGE -->
<div class="photo"><a href="$aData[sLinkName]"><img src="$config[dir_files]$aDataImage[iSizeValue1]/$aDataImage[sFileName]" alt="$aDataImage[sFileDescription]" /></a></div>
<!-- END RELATED_IMAGE -->
<!-- BEGIN RELATED_NO_IMAGE --><!-- END RELATED_NO_IMAGE -->
<!-- BEGIN RELATED_FOOT -->
  </tr>
</table>
<div class="clear">&nbsp;</div>
<!-- END RELATED_FOOT -->

<!-- BEGIN NEXT_PRODUCT -->
<a href="$aData[sNextLinkName]">Next</a>
<!-- END NEXT_PRODUCT -->

<!-- BEGIN PREV_PRODUCT -->
<a href="$aData[sPrevLinkName]">Previous</a>
<!-- END PREV_PRODUCT -->

<!-- BEGIN RELATED_MORE -->
  </tr>
</table>
<div class="clear">&nbsp;</div>
<a href="javascript:void(0)" style="float:right" onClick="this.nextSibling.style.display='block';this.style.display='none';"><h3 class="productsRelated" style="border-top:0px;margin-right:20px;margin-bottom:15px">$lang[More_products]</h3></a><table id="productsRelated" cellspacing="0" style="display:none">
  <tr>
<!-- END RELATED_MORE -->
