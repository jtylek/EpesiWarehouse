<!-- BEGIN FORM -->
<h1><img src="$config[dir_templates]admin/img/ico_products.gif" alt="$lang[Features_form]" />$lang[Features_form]</h1>

<form action="?p=$p" method="post" id="mainForm" onsubmit="return checkForm( this );">
  <fieldset id="type2">
    <input type="hidden" name="iFeature" value="$aData[iFeature]" />
    <table cellspacing="1" class="mainTable" id="feature">
      <thead>
        <tr class="save">
          <th colspan="2">
            <input type="submit" value="$lang['save_list'] &raquo;" name="sOptionList" />
            <input type="submit" value="$lang['save'] &raquo;" name="sOption" />
          </th>
        </tr>
      </thead>
      <tfoot>
        <tr class="save">
          <th colspan="2">
            <input type="submit" value="$lang['save_list'] &raquo;" name="sOptionList" />
            <input type="submit" value="$lang['save'] &raquo;" name="sOption" />
          </th>
        </tr>
      </tfoot>
      <tbody>
        <tr class="l0">
          <th>$lang[Name]</th>
          <td><input type="text" name="sName" value="$aData[sName]" size="80" maxlength="50" class="input" alt="simple" /></td>
        </tr>
        <tr class="l1">
          <th>$lang[Position]</th>
          <td><input type="text" name="iPosition" value="$aData[iPosition]" size="3" maxlength="3" class="input" alt="int" /></td>
        </tr>
      </tbody>
    </table>
  </fieldset>
</form>
<!-- END FORM -->

<!-- BEGIN LIST_TITLE -->
<h1><img src="$config[dir_templates]admin/img/ico_products.gif" alt="$lang[Features]" />$lang[Features]</h1>
<!-- END LIST_TITLE -->
<!-- BEGIN LIST -->
<tr class="l$aData[iStyle]">
  <td>
    $aData[iFeature]
  </td>
  <td>
    <a href="?p=$aActions[f]-form&amp;iFeature=$aData[iFeature]">$aData[sName]</a>
  </td>
  <td>
    $aData[iPosition]
  </td>
  <td class="options">
    <a href="?p=$aActions[f]-form&amp;iFeature=$aData[iFeature]"><img src="$config[dir_templates]admin/img/ico_edit.gif" alt="$lang['edit']" title="$lang['edit']" /></a>
    <a href="?p=$aActions[f]-delete&amp;iFeature=$aData[iFeature]" onclick="return del( );"><img src="$config[dir_templates]admin/img/ico_del.gif" alt="$lang['delete']" title="$lang['delete']"/></a>  
  </td>
</tr>
<!-- END LIST -->
<!-- BEGIN HEAD --><table id="list" class="boxes" cellspacing="1">
  <thead>
    <tr>
      <td class="id">$lang['Id']</td>
      <td class="name">$lang['Name']</td>
      <td class="position">$lang['Position']</td>
      <td class="options">&nbsp;</td>
    </tr>
  </thead>
  <tbody><!-- END HEAD -->
<!-- BEGIN FOOT --></tbody></table><!-- END FOOT -->

<!-- BEGIN FEATURES_LIST -->
    <tr>
      <td>
        $aData[sName]
      </td>
      <td>
        <input type="text" name="aFeatures[$aData[iFeature]]" value="$aData[sValue]" class="input" />
      </td>
    </tr>
<!-- END FEATURES_LIST -->
<!-- BEGIN FEATURES_HEAD -->
<tr>
  <td>
    $lang[Features]
  </td>
  <td>
    <table cellspacing="1" id="features">
<!-- END FEATURES_HEAD -->
<!-- BEGIN FEATURES_FOOT -->
  </table>
</td>
</tr>
<!-- END FEATURES_FOOT -->