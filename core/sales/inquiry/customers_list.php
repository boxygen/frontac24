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
  Page for searching customer list and select it to customer selection
  in pages that have the supplier dropdown lists.
  Author: bogeyman2007 from Discussion Forum. Modified by Joe Hunt
***********************************************************************/
$page_security = "SA_SALESORDER";
$path_to_root = "../..";
include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/sales/includes/db/customers_db.inc");

$mode = get_company_pref('no_customer_list');
if ($mode != 0)
	$js = get_js_set_combo_item();
else
	$js = get_js_select_combo_item();

page(_($help_context = "Customers"), true, false, "", $js);

function select_customer($myrow)
{
    global $mode;

    $name = $_GET["client_id"];
    $value = $myrow['debtor_no'];
    if ($mode != 0) {
        $text = $myrow['name'];
        return ahref_str(_("Select"), 'javascript:void(0)', '', 'setComboItem(window.opener.document, "'.$name.'",  "'.$value.'", "'.$text.'")');
    }
    else {
        return ahref_str(_("Select"), 'javascript:void(0)', '', 'selectComboItem(window.opener.document, "'.$name.'", "'.$value.'")');
    }
}
if(get_post("search")) {
  $Ajax->activate("customer_tbl");
}

start_form(false, false, $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);

start_table(TABLESTYLE_NOBORDER);

start_row();

text_cells_ex(_("Customer"), "customer", null, null, null, null, null, null, true);
submit_cells("search", _("Search"), "", _("Search customers"), "default");

end_row();

end_table();

$th = array("" => array('fun' => 'select_customer'), _("Customer"), _("Short Name"), _("Address"), _("Tax ID"));
$sql = get_customers_search_sql(get_post("customer"));
$table =& new_db_pager('customer_tbl', $sql, $th);
$table->width = "85%";
display_db_pager($table);

end_form();

end_page(true);
