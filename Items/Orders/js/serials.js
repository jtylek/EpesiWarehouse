set_serials_fields = function(qty, order_details) {
	var serial_el = $("_serial__data");
	if (serial_el) {
		serial_el.parentNode.style.display = "none";
		var tbody = serial_el.parentNode.parentNode;
	}
	qty+=2;
	for( var x = 0; tbody.childNodes[x]; x++ ) {
		if (x>=qty) {
			tbody.removeChild(tbody.childNodes[x]);
			x--;
		}
	}
	serial_label = $("serial_form_main_label").innerHTML;
	note_label = $("serial_form_note_label").innerHTML;
	shelf_label = $("serial_form_shelf_label").innerHTML;
	while (x<qty) {
		var newEl = document.createElement('tr');
		tbody.appendChild(newEl);
		newEl.innerHTML = 
			'<td class="label">'+serial_label+(x-1)+'</td><td class="data" id="serial_field_space_'+(x-1)+'">...<td>'+
			'<td class="label">'+note_label+(x-1)+'</td><td class="data" id="note_field_space_'+(x-1)+'">...<td>'+
			'<td class="label">'+shelf_label+(x-1)+'</td><td class="data" id="shelf_field_space_'+(x-1)+'">...<td>';
		new Ajax.Request('modules/Premium/Warehouse/Items/Orders/js/get_serial_field.php', {
			method: 'post',
			parameters:{
				order_details: order_details,
				serial: x-1,
				cid: Epesi.client_id
			},
			onSuccess:function(t) {
				eval(t.responseText);
			}
		});
		x++;
	}
}

set_serials_based_on_quantity = function(order_details) {
	var el = $("quantity");
	var val = parseInt(el.value);
	if (val>0) set_serials_fields(val, order_details);
}

allowed_empty_serials = new Array();

get_first_available_serial = function(item, i){
	j = 1;
	el = $("serial__"+item+"__0");
	while (j!=el.options.length) {
		if (!el.options[j]) return "NULL";
		val = el.options[j].value;
		k = 0;
		while (k<i) {
			if ($("serial__"+item+"__"+k).value==val) break;
			k++;
		}
		if (k==i) {
			return val;
		}
		j++;
	}
	return "NULL";
}

check_serial_duplicates = function (item) {
	i = 0;
	empty_so_far = 0;
	do {
		el = $("serial__"+item+"__"+i);
		if (!el) break;
		val = el.value;
		if (val == "NULL") {
			if (empty_so_far==allowed_empty_serials[item]) {
				el.value = get_first_available_serial(item, i);
			} else {
				empty_so_far++;
			}
		} else {
			for (j=0;j<i;j++) {
				val2 = $("serial__"+item+"__"+j).value;
				if (val2==val) {
					if (empty_so_far==allowed_empty_serials[item]) {
						el.value = get_first_available_serial(item, i);
					} else {
						el.value = "NULL";
						empty_so_far++;
					}
					break;
				}
			}
		}
		i++;
	} while (true);
}