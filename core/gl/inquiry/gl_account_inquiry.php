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
$page_security = 'SA_GLTRANSVIEW';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");

include($path_to_root . "/includes/db_pager.inc");

include_once($path_to_root . "/admin/db/fiscalyears_db.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/gl/includes/gl_db.inc");

$js = '';
set_focus('account');
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();
$fields = array("account", "TransFromDate", "TransToDate", "Dimension", "Dimension2", "Memo", "amount_min", "amount_max", "person_type", "person_id", "select");
$js .= get_js_history($fields);

page(_($help_context = "General Ledger Inquiry"), false, false, '', $js, false, '', true);

//----------------------------------------------------------------------------------------------------
// Ajax updates
//
if (get_post('Show')
    || list_updated('account')
    || list_updated('person_type')
    || list_updated('person_id'))
{
	$Ajax->activate('trans_tbl');
}

set_posts($fields);

if (!isset($_POST["amount_min"]))
	$_POST["amount_min"] = price_format(0);
if (!isset($_POST["amount_max"]))
	$_POST["amount_max"] = price_format(0);

//----------------------------------------------------------------------------------------------------

function gl_inquiry_controls()
{
    global $Ajax;
	$dim = get_company_pref('use_dimension');
    start_form();

    start_table(TABLESTYLE_NOBORDER);
	start_row();
    if (get_post('select')) {
        label_cells(_("Account:"), get_gl_account_name(get_post('account')));
        hidden('account', get_post('account'));
    } else
        gl_all_accounts_list_cells(_("Account:"), 'account', null, false, false, _("All Accounts"), true);
    $days = user_transaction_days();
    date_cells(_("from:"), 'TransFromDate', '', null, -abs($days));
    if ($days >= 0) {
        date_cells(_("to:"), 'TransToDate');
    } else {
        date_cells(_("to:"), 'TransToDate', '', null, 0, 2);
    }
    end_row();
	end_table();

    div_start('header');
	start_table(TABLESTYLE_NOBORDER);
	start_row();
        if (!isset($_POST['Dimension'])
            || $_POST['Dimension'] != -1) {
            if ($dim >= 1)
		dimensions_list_cells(_("Dimension")." 1:", 'Dimension', null, true, " ", false, 1);
            if ($dim > 1)
		dimensions_list_cells(_("Dimension")." 2:", 'Dimension2', null, true, " ", false, 2);
        }

	ref_cells(_("Memo:"), 'Memo', '',null, _('Enter memo fragment or leave empty'));
	small_amount_cells(_("Amount min:"), 'amount_min', null, " ");
	small_amount_cells(_("Amount max:"), 'amount_max', null, " ");

    if (!isset($_POST['person_type'])) 
        $_POST['person_type'] = '';

    payment_person_types_list_cells( _("Person Type:"), 'person_type', $_POST['person_type'], true, true);
    if (list_updated('person_type')) {
        unset($_POST['person_id']);
        $Ajax->activate('header');
    }

    switch ($_POST['person_type'])
    {
        case '' :
            unset($_POST['person_id']);
            hidden('person_id');
            break;
        case PT_MISC :
            text_cells_ex(_("Name:"), 'person_id', 40, 50);
            break;
        case PT_SUPPLIER :
            supplier_list_cells(_("Supplier:"), 'person_id', null, false, true, false, true);
            break;
        case PT_CUSTOMER :
            customer_list_cells(_("Customer:"), 'person_id', null, false, true, false, true);
            break;
        case PT_QUICKENTRY :
            quick_entries_list_cells(_("Type").":", 'person_id', null, null, true);
    }

    hidden('select', get_post('select')); 
	submit_cells('Show',_("Show"),'','', 'default');
	end_row();
	end_table();
    div_end();

	echo '<hr>';
    end_form();
}

function edit_link($row)
{
    global $page_nested;

    $str = '';
    if ($page_nested)
        return '';

    switch($row['type']) {
        case ST_SALESINVOICE:
            $myrow = get_customer_trans($row["type_no"], $row["type"]);
            $str = "/sales/sales_order_entry.php?NewInvoice=".$myrow['order_']."&InvoiceNo=".$row['type_no'];
            return pager_link(_('Edit'), $str, ICON_EDIT);
            break;
        case ST_CUSTCREDIT:
            $myrow = get_customer_trans($row["type_no"], $row["type"]);
            return $myrow['order_'] ? '' :   // allow  only free hand credit notes edition
                trans_editor_link($row['type'], $row['type_no']);
        default:
            return trans_editor_link($row['type'], $row['type_no']);
    }
}

function delete_link($row)
{
        return is_closed_trans($row['type'], $row['type_no']) ? "--" : pager_link(_("Delete"), "/admin/void_transaction.php?trans_no=" . $row['type_no'] . "&filterType=". $row['type'], ICON_DELETE);
}

function ok_link($row)
{
        return pager_link_absolute(_("OK"), preg_replace('/&select=.*/','',htmlspecialchars_decode($_POST['referer'])) . "&select=".get_post('select') . "&amount=". $row['amount'] . "&memo=".$row['memo_'], ICON_OK);
}

//----------------------------------------------------------------------------------------------------

function show_results()
{
	global $path_to_root, $systypes_array;

	if (!isset($_POST["account"]))
		$_POST["account"] = null;

	$act_name = $_POST["account"] ? get_gl_account_name($_POST["account"]) : "";
	$dim = get_company_pref('use_dimension');

    /*Now get the transactions  */
    if (!isset($_POST['Dimension']))
    	$_POST['Dimension'] = 0;
    if (!isset($_POST['Dimension2']))
    	$_POST['Dimension2'] = 0;
	$result = get_gl_transactions($_POST['TransFromDate'], $_POST['TransToDate'], -1,
    	$_POST["account"], $_POST['Dimension'], $_POST['Dimension2'], null,
    	input_num('amount_min'), input_num('amount_max'), get_post('person_type'), get_post('person_id'), $_POST['Memo']);

	$colspan = ($dim == 2 ? "8" : ($dim == 1 ? "7" : "6"));

	if ($_POST["account"] != null)
		display_heading($_POST["account"]. "&nbsp;&nbsp;&nbsp;".$act_name);

	// Only show balances if an account is specified AND we're not filtering
	$show_balances =
        $_POST["account"] != null && 
        input_num("amount_min") == 0 && 
        input_num("amount_max") == 0 &&
        empty($_POST["person_id"]) &&
        empty($_POST["Memo"]);
		
	start_table(TABLESTYLE);
	
	$first_cols = array(_("Type"), _("#"), _("Reference"), _("Date"));
	
	if ($_POST["account"] == null)
	    $account_col = array(_("Account"));
	else
	    $account_col = array();

    if (@$_POST["person_id"] == null)
        $person_col = array(_("Person/Item"));
    else
        $person_col = array();
	
	if ($dim == 2)
		$dim_cols = array(_("Dimension")." 1", _("Dimension")." 2");
	elseif ($dim == 1)
		$dim_cols = array(_("Dimension"));
	else
		$dim_cols = array();
	
	if ($show_balances)
	    $remaining_cols = array(_("Debit"), _("Credit"), _("Balance"), _("Memo"), "", "");
	else
	    $remaining_cols = array(_("Debit"), _("Credit"), _("Memo"), "", "");
	    
	$th = array_merge($first_cols, $account_col, $dim_cols, $person_col, $remaining_cols);
			
	table_header($th);
	if ($_POST["account"] != null && is_account_balancesheet($_POST["account"]))
		$begin = "";
	else
	{
		$begin = get_fiscalyear_begin_for_date($_POST['TransFromDate']);
		if (date1_greater_date2($begin, $_POST['TransFromDate']))
			$begin = $_POST['TransFromDate'];
		$begin = add_days($begin, -1);
	}

    if ($_POST["account"] != null)
    	$colspan--;

	$bfw = 0;
	if ($show_balances) {
	    $bfw = get_gl_balance_from_to($begin, $_POST['TransFromDate'], $_POST["account"], $_POST['Dimension'], $_POST['Dimension2']);
    	start_row("class='inquirybg'");
    	label_cell("<b>"._("Opening Balance")." - ".$_POST['TransFromDate']."</b>", "colspan=$colspan");
    	label_cell("");
    	label_cell("");
    	amount_cell($bfw, true);
    	end_row();
	}
	
	$running_total = $bfw;
	$j = 1;
	$k = 0; //row colour counter

    // TEMPORARY: this needs to be converted to db_pager to prevent ajax timeouts
    if (db_num_rows($result) > 5000) {
        db_seek($result, db_num_rows($result) - 5000);
    	alt_table_row_color($k);
        label_cell("Earlier data is not shown for performance reasons.  Adjust search criteria to reduce number of rows.", "align=center colspan=100%");
        end_row();
    }

	while ($myrow = db_fetch($result))
	{

    	alt_table_row_color($k);

    	$running_total += $myrow["amount"];

    	$trandate = sql2date($myrow["tran_date"]);

    	label_cell($systypes_array[$myrow["type"]]);
		label_cell(get_gl_view_str($myrow["type"], $myrow["type_no"], $myrow["type_no"], true));
		label_cell(get_trans_view_str($myrow["type"],$myrow["type_no"],$myrow['reference']));
    	label_cell($trandate);
    	
    	if ($_POST["account"] == null)
    	    label_cell($myrow["account"] . ' ' . get_gl_account_name($myrow["account"]));
    	
		if ($dim >= 1)
			label_cell(get_dimension_string($myrow['dimension_id'], true));
		if ($dim > 1)
			label_cell(get_dimension_string($myrow['dimension2_id'], true));
        if (@$_POST["person_id"] == null)
            label_cell(payment_person_name_link($myrow["person_type_id"],$myrow["person_id"], true, $trandate));
		display_debit_or_credit_cells($myrow["amount"]);
		if ($show_balances)
		    amount_cell($running_total);
    	label_cell($myrow['memo']);
        if (get_post('select'))
            echo "<td>" . ok_link($myrow) . "</td>";
        else {
            echo "<td>" . edit_link($myrow) . "</td>";
            echo "<td>" . delete_link($myrow) . "</td>";
        }
    	end_row();

    	$j++;
    	if ($j == 12)
    	{
    		$j = 1;
    		table_header($th);
    	}
	}
	//end of while loop

    start_row("class='inquirybg'");
	if ($show_balances) {
    	label_cell("<b>" . _("Ending Balance") ." - ".$_POST['TransToDate']. "</b>", "colspan=$colspan");
        display_debit_or_credit_cells($running_total-$bfw, true);
    	amount_cell($running_total, true);
	} else {
        label_cell("<b>" . _("Total") ." - ".$_POST['TransToDate']. "</b>", "colspan=$colspan");
        display_debit_or_credit_cells($running_total-$bfw, true);
    }
    end_row();

	end_table();
	if (db_num_rows($result) == 0)
		display_note(_("No general ledger transactions have been created for the specified criteria."), 0, 1);

}

//----------------------------------------------------------------------------------------------------

gl_inquiry_controls();

div_start('trans_tbl');

if (get_post('Show')
    || get_post('account')
    || get_post('person_type')
    || get_post('person_id'))
    show_results();

div_end();
scroll_down('trans_tbl');

//----------------------------------------------------------------------------------------------------

end_page(true);

