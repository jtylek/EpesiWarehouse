<table>
	<tr>
		<td>
			<font size=8>
				{if isset($warehouse.invoice_display_name)}{$warehouse.invoice_display_name}{else}{$company.company_name}{/if}<br/>
				{$warehouse.address_1}<br/>
				{$warehouse.postal_code} {$warehouse.city}<br/>
				{if $warehouse.phone}{$labels.tel} {$warehouse.phone}<br/>{/if}
				{if $warehouse.fax}{$labels.fax} {$warehouse.fax}<br/>{/if}
				{$company.web_address}
			</font>
		</td>
		<td align="right">
			{$warehouse.city}, {$date}<br/>
			{$labels.sale_date} {$order.transaction_date}
		</td>
	</tr>
</table>
<div width="100%" align="center">
	{if $order.status==1 || $order.status==""}
		<font size=12><b>{$labels.po} {$order.proforma_id}</b></font><br>
	{else}
		{if $order.receipt}
			<font size=12><b>{$labels.receipt} {$order.invoice_id}</b></font><br>
		{else}
			{if isset($order.invoice_id) && $order.invoice_id}
				<font size=12><b>{$labels.invoice} {$order.invoice_id}</b></font><br>
			{else}
				{if isset($order.po_id)}<font size=11><b>{$labels.order} {$order.po_id}</b></font><br>{/if}
			{/if}
		{/if}
	{/if}
	{$labels.copy}
</div>
<table>
	<tr>
		<td align="right" width="90px">
			<font size=10><b>
				{$labels.seller}
			</b></font>
		</td>
		<td width="10px">
		</td>
		<td align="left" width="400">
			{$company.company_name}
		</td>
	</tr>
	<tr>
		<td align="right" width="90px">
			{$labels.seller_address}
		</td>
		<td width="10px">
		</td>
		<td align="left" width="400">
			{$warehouse.postal_code} {$warehouse.city}, {$warehouse.address_1}
		</td>
	</tr>
	<tr>
		<td align="right" width="90px">
			{$labels.seller_id_number}
		</td>
		<td width="10px">
		</td>
		<td align="left" width="400">
			<b>{$warehouse.tax_id}</b>
		</td>
	</tr>
</table>
<br>
{if !$order.receipt}
<table>
	<tr>
		<td align="right" width="90px">
			<font size=10><b>
				{$labels.buyer}
			</b></font>
		</td>
		<td width="10px">
		</td>
		<td align="left" width="400">
			{$order.company_name}
		</td>
	</tr>
	<tr>
		<td align="right" width="90px">
			{$labels.buyer_address}
		</td>
		<td width="10px">
		</td>
		<td align="left" width="400">
			{$order.postal_code} {$order.city}, {$order.address_1}
		</td>
	</tr>
	<tr>
		<td align="right" width="90px">
			{$labels.buyer_id_number}
		</td>
		<td width="10px">
		</td>
		<td align="left" width="400">
			<b>{if isset($order.tax_id)}{$order.tax_id}{/if}</b>
		</td>
	</tr>
</table>
<br>
{/if}
<table>
	<tr>
		<td align="right" width="90px">
			<b>{$labels.payment_method}</b>
		</td>
		<td width="10px">
		</td>
		<td width="90px" align="left">
			{$order.payment_type_label}
		</td>
		<td width="90px" align="right">
			{$labels.due_date}
		</td>
		<td width="5px">
		</td>
		<td align="left" width="160px">
			{$order.terms_label}
		</td>
	</tr>
	<tr>
		<td align="right" width="90px">
			{$labels.bank}
		</td>
		<td width="10px">
		</td>
		<td align="left" colspan="4">
			{$warehouse.bank_account}
		</td>
	</tr>
</table>

<br>
<center>
	<table border="1">
		<tr>
			<td width="6%" style="text-align:center;">
				<font size="7"><b>
					{$labels.no}
				</b></font>
			</td>
			<td width="20%" style="text-align:center;">
				<font size="7"><b>
					{$labels.item_name}
				</b></font>
			</td>
			<td width="8%" style="text-align:center;">
				<font size="7"><b>
					{$labels.classification}
				</b></font>
			</td>
			<td width="8%" style="text-align:center;">
				<font size="7"><b>
					{$labels.quantity}
				</b></font>
			</td>
			<td width="6%" style="text-align:center;">
				<font size="7"><b>
					{$labels.units}
				</b></font>
			</td>
            <td width="8%" style="text-align:center;">
                <font size="7"><b>
                        {$labels.unit_price}
                    </b></font>
            </td>
            <td width="6%" style="text-align:center;">
                <font size="7"><b>
                        {$labels.markup_discount_rate}
                    </b></font>
            </td>
			<td width="8%" style="text-align:center;">
				<font size="7"><b>
					{$labels.net_price}
				</b></font>
			</td>
			<td width="6%" style="text-align:center;">
				<font size="7"><b>
					{$labels.tax_rate}
				</b></font>
			</td>
			<td width="8%" style="text-align:center;">
				<font size="7"><b>
					{$labels.gross_value}
				</b></font>
			</td>
			<td width="8%" style="text-align:center;">
				<font size="7"><b>
					{$labels.net_value}
				</b></font>
			</td>
			<td width="8%" style="text-align:center;">
				<font size="7"><b>
					{$labels.tax_value}
				</b></font>
			</td>
		</tr>
	</table>
</center>
