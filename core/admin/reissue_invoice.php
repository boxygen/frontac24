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
$page_security = 'SA_VOIDTRANSACTION';
$path_to_root = "..";
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/admin/db/transactions_db.inc");

include_once($path_to_root . "/admin/db/voiding_db.inc");
$js = "";
if (user_use_date_picker())
	$js .= get_js_date_picker();
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);
	
if (isset($_GET['trans_no'])) {
    $_POST['FromTransNo'] = $_POST['ToTransNo'] = $_POST['selected_id'] = $_POST['trans_no'] = $_GET['trans_no'];
    $_POST['filterType'] = ST_SALESINVOICE;
    $_POST['ProcessVoiding'] = true;
}

page(_($help_context = "Reissue Invoice"), false, false, "", $js);

simple_page_mode(true);
//----------------------------------------------------------------------------------------
function returnToReferer($message)
{
    $referer=parse_url($_POST['referer'], PHP_URL_PATH);
    $params = parse_url(htmlspecialchars_decode($_POST['referer']), PHP_URL_QUERY);
    $params = preg_replace('/[&]*message.*/', '', $params);
    if (!empty($params))
        $params .= "&";
    $params .= "message=$message";

    meta_forward($referer, $params);
}

function exist_transaction($type, $type_no)
{
	$void_entry = get_voided_entry($type, $type_no);

	if ($void_entry != null)
		return false;

	switch ($type) 
	{
		case ST_JOURNAL : // it's a journal entry
			if (!exists_gl_trans($type, $type_no))
				return false;
			break;

		case ST_BANKPAYMENT : // it's a payment
		case ST_BANKDEPOSIT : // it's a deposit
		case ST_BANKTRANSFER : // it's a transfer
			if (!exists_bank_trans($type, $type_no))
				return false;
			break;

		case ST_SALESINVOICE : // it's a customer invoice
		case ST_CUSTCREDIT : // it's a customer credit note
		case ST_CUSTPAYMENT : // it's a customer payment
		case ST_CUSTDELIVERY : // it's a customer dispatch
			if (!exists_customer_trans($type, $type_no))
				return false;
			break;

		case ST_LOCTRANSFER : // it's a stock transfer
			if (get_stock_transfer_items($type_no) == null)
				return false;
			break;

		case ST_INVADJUST : // it's a stock adjustment
			if (get_stock_adjustment_items($type_no) == null)
				return false;
			break;

		case ST_PURCHORDER : // it's a PO
			return false;

		case ST_SUPPRECEIVE : // it's a GRN
			if (!exists_grn($type_no))
				return false;
			break;

		case ST_SUPPINVOICE : // it's a suppler invoice
		case ST_SUPPCREDIT : // it's a supplier credit note
		case ST_SUPPAYMENT : // it's a supplier payment
			if (!exists_supp_trans($type, $type_no))
				return false;
			break;

		case ST_WORKORDER : // it's a work order
			if (!get_work_order($type_no, true))
				return false;
			break;

		case ST_MANUISSUE : // it's a work order issue
			if (!exists_work_order_issue($type_no))
				return false;
			break;

		case ST_MANURECEIVE : // it's a work order production
			if (!exists_work_order_produce($type_no))
				return false;
			break;

		case ST_SALESORDER: // it's a sales order
		case ST_SALESQUOTE: // it's a sales quotation
			return false;
		case ST_COSTUPDATE : // it's a stock cost update
			return false;
	}

	return true;
}

function view_link($trans)
{
	if (!isset($trans['type']))
		$trans['type'] = $_POST['filterType'];
	return get_trans_view_str($trans["type"], $trans["trans_no"]);
}

function select_link($row)
{
	if (!isset($row['type']))
		$row['type'] = $_POST['filterType'];
	if (!is_date_in_fiscalyear($row['trans_date'], true))
		return _("N/A");
  	return button('Edit'.$row["trans_no"], _("Select"), _("Select"), ICON_EDIT);
}

function gl_view($row)
{
	if (!isset($row['type']))
		$row['type'] = $_POST['filterType'];
	return get_gl_view_str($row["type"], $row["trans_no"]);
}

function date_view($row)
{
	return $row['trans_date'];
}

function ref_view($row)
{
	return $row['ref'];
}

function voiding_controls()
{
	global $selected_id;

	$not_implemented =  array(ST_PURCHORDER, ST_SALESORDER, ST_SALESQUOTE, ST_COSTUPDATE);

	start_form();
    hidden('filterType', $_POST['filterType']);
    hidden('trans_no', $_POST['trans_no']);

    if (!isset($_POST['ProcessVoiding']))
    	submit_center('ProcessVoiding', _("Void Transaction"), true, '', 'default');
    else 
    {
 		if (!exist_transaction($_POST['filterType'],$_POST['trans_no']))
 		{
			display_error(_("The entered transaction does not exist or cannot be voided."));
			unset($_POST['trans_no']);
			unset($_POST['memo_']);
			unset($_POST['date_']);
    		submit_center('ProcessVoiding', _("Void Transaction"), true, '', 'default');
		}	
 		else
 		{
    		display_warning(_("Are you sure you want to reissue this invoice?"), 0, 1);
    		display_warning(_("This will delete the existing invoice and delivery and open three editors beginning with the sales order.  This action cannot be undone."), 0, 1);
   			br();
    		submit_center_first('ConfirmVoiding', _("Proceed"), '', true);
    		submit_center_last('CancelVoiding', _("Cancel"), '', 'cancel');
    	}	
    }

	end_form();
}

//----------------------------------------------------------------------------------------

function check_valid_entries()
{
	if (is_closed_trans($_POST['filterType'],$_POST['trans_no']))
	{
		display_error(_("The selected transaction was closed for edition and cannot be voided."));
		set_focus('trans_no');
		return false;
	}
	if (!is_date($_POST['date_']))
	{
		display_error(_("The entered date is invalid."));
		set_focus('date_');
		return false;
	}
	if (!is_date_in_fiscalyear($_POST['date_']))
	{
		display_error(_("The entered date is out of fiscal year or is closed for further data entry."));
		set_focus('date_');
		return false;
	}

	if (!is_numeric($_POST['trans_no']) OR $_POST['trans_no'] <= 0)
	{
		display_error(_("The transaction number is expected to be numeric and greater than zero."));
		set_focus('trans_no');
		return false;
	}

	return true;
}

//----------------------------------------------------------------------------------------

function handle_void_transaction()
{
    global $path_to_root;
	if (check_valid_entries()==true) 
	{
		$void_entry = get_voided_entry($_POST['filterType'], $_POST['trans_no']);
		if ($void_entry != null) 
		{
			display_error(_("The selected transaction has already been voided."), true);
			unset($_POST['trans_no']);
			unset($_POST['memo_']);
			unset($_POST['date_']);
			set_focus('trans_no');
			return;
		}

        $order_no=get_customer_trans_order($_POST['filterType'], $_POST['trans_no']);

		$msg = void_transaction($_POST['filterType'], $_POST['trans_no'],
			$_POST['date_'], "Reissue invoice");

		if ($msg !== false) {
			display_error($msg);
            return;
        }

        // Void the deliveries (if any)
        // Note: deliveries were voided if invoice was direct invoice
        while ($delivery_trans=get_customer_delivery_trans($order_no)) {
            $msg = void_transaction(ST_CUSTDELIVERY, $delivery_trans,
                $_POST['date_'], "Reissue invoice");
            if ($msg !== false) {
                display_error($msg);
                return;
            }
        }

        $params="ModifyOrderNumber=".$order_no."&DirectInvoice=1";
        meta_forward($path_to_root . "/sales/sales_order_entry.php", $params);
	}
}

//----------------------------------------------------------------------------------------
// retrieves the related delivery for a given sales order

function get_customer_delivery_trans($order_no)
{
    $sql = "SELECT dt.trans_no FROM ".TB_PREF."debtor_trans dt LEFT JOIN ".TB_PREF."voided v ON dt.trans_no=v.id and dt.type=v.type WHERE dt.type='".ST_CUSTDELIVERY."' AND order_=".db_escape($order_no) . " AND ISNULL(v.date_)";

    $result = db_query($sql, "The debtor transaction could not be queried");

    $row = db_fetch_row($result);

    return $row[0];
}
//----------------------------------------------------------------------------------------


if (!isset($_POST['date_']))
{
	$_POST['date_'] = Today();
	if (!is_date_in_fiscalyear($_POST['date_']))
		$_POST['date_'] = end_fiscalyear();
}		
	
if (isset($_POST['ProcessVoiding']))
{
	if (!check_valid_entries())
		unset($_POST['ProcessVoiding']);
	$Ajax->activate('_page_body');
}

if (isset($_POST['ConfirmVoiding']))
{
	handle_void_transaction();
	$selected_id = '';
	$Ajax->activate('_page_body');
}

if (isset($_POST['CancelVoiding']))
{
    if (isset($_POST['referer']))
        returnToReferer("Reissue Canceled");
	$selected_id = -1;
	$Ajax->activate('_page_body');
}

//----------------------------------------------------------------------------------------

voiding_controls();

end_page();

