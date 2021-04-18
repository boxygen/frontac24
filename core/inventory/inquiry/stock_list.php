<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
/**********************************************************************
  Page for searching item list and select it to item selection
  in pages that have the item dropdown lists.
  Author: bogeyman2007 from Discussion Forum. Modified by Joe Hunt
***********************************************************************/
$page_security = "SA_ITEM";
$path_to_root = "../..";
include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/inventory/includes/db/items_db.inc");

$mode = get_company_pref('no_item_list');
if ($mode != 0)
	$js = get_js_set_combo_item();
else
	$js = get_js_select_combo_item();

page(_($help_context = "Items"), true, false, "", $js);

function select_item($myrow)
{
    global $mode;
    $name = $_GET["client_id"];
	$value = $myrow['item_code'];
	if ($mode != 0) {
		$text = $myrow['description'];
  		return ahref_str(_("Select"), 'javascript:void(0)', '', 'setComboItem(window.opener.document, "'.$name.'",  "'.$value.'", "'.$text.'")');
	}
	else {
  		return ahref_str(_("Select"), 'javascript:void(0)', '', 'selectComboItem(window.opener.document, "'.$name.'", "'.$value.'")');
	}
}

if(get_post("search")) {
  $Ajax->activate("item_tbl");
}

start_form(false, false, $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);

start_table(TABLESTYLE_NOBORDER);

start_row();

text_cells_ex(_("Description"), "description", null, null, null, null, null, null, true);
submit_cells("search", _("Search"), "", _("Search items"), "default");

end_row();

end_table();

$th = array("" => array('fun' => 'select_item'), _("Item Code"), _("Description"), _("Category"));
$sql = get_items_search_sql(get_post("description"), @$_GET['type']);
$table =& new_db_pager('item_tbl', $sql, $th);
$table->width = "85%";
display_db_pager($table);

end_form();

end_page(true);
