# Braathwaate FrontAccounting v2.4

This is a lightly modified fork of the
[Ap.Muthu FrontAccounting v2.4 github repo](https://github.com/apmuthu/frontac24.git,)
which is a current stable version of vanilla FrontAccounting (FA).
I chose to base my modifications off this fork,
but the
[Official GitHub Mirror Repo](https://github.com/FrontAccountingERP/FA/tree/master)
would also have been a good choice.

The "wwh" branch is the main repository.

The braathwaate modifications are mostly usability enhancements
to make data entry and search faster and less error prone
although there are also bug fixes and feature enhancements.

BF (Braathwaate Fork) does require some database changes (see below).
If desired, both BF and FA can run properly using the same database.
Following is a list of modifications.

## wwh customizations

## Quick entry inactive support
In FA, the list of Quick entries accumulate ad infinitum.

BF supports marking the Quick entry inactive so it does not
show up in the payment list.

## Allow bank account payments on supplier direct invoice
FA restricts payment on a supplier direct invoice
to cash accounts.
For other forms of payments,
one must select "Delayed"
and enter the payment separately.

BF allows any bank account to be used for the payment,
such as a credit card.

## Allow bank accounts in POS
FA restricts payment on a customer direct invoice
to cash accounts,
because the POS setup is restricted to cash accounts.

POS systems often allow direct deposit into bank accounts,
so BF does not restrict the POS "cash sale" to cash accounts

## apportion service items; bug fixes
According to Steven Bragg in his "GAAP Guidebook",
"In general, inventory is to be accounted for at cost,
which is considered to be the sum of those expenditures required to bring an inventory item to its present condition and location." 

This mod gives the user the option to apportion service costs
(e.g. freight, setup charges, discounts, etc) on the PO
into prices of inventory items.
By apportioning these costs into item prices, they are then accounted for in inventory
and thus accounted for in COGS only when then items are sold, as per GAAP.
Three methods of apportionment are provided: by item quantity, line item, and line total.

Note: This does not easily support apportioning costs from multiple suppliers into
item prices.  For example, if shipping is paid outside of the invoice,
it is difficult to incorporate the shipping into the item prices.
See, http://frontaccounting.com/punbb/viewtopic.php?pid=31220#p31220.  

Note: To prevent COGS costs from changing the item purchase price in the purch_data table,
update of this table was moved from the invoice/grn commit code
to the purchase order entry code,
so that the table can be updated before COGS costs are added to the purchase price.

additional bug fixes:

    Viewing order totals on POs with items with decimal precision greater than user decimals could be wrong because of rounding. Changes in the code reflect the method used to generate the PO, by rounding each line item to user decimals, then adding. Prior code added then rounded.

    php.ini precision could cause disabling of FA PO decimal precision for items with precise Conversion Factors. Fix was to use php.ini precision rather than disable the feature.

    credit memos for tax exempt items do not have tax gl account, and code generated a PHP warning. This did not cause any problem other than the PHP warning, because G/L was created on the remaining total.

## ajax browser sync
In FA, when a user modifies selection criteria on a FA page and AJAX updates the page, then the user clicks to another page such as via the editor, the browser back button (or FA back link) does not return to the page as the user modified it. This forces the user to reenter the selection criteria and hit the Show/Search button again.

This mod updates the browser page history when AJAX updates the page so that the back button returns to the FA page as the user modified it.

## keep reconcile on bank trans mod
When a bank transaction is modified, FA unreconciles it, even if
just the memo or date was changed.

BF retains the reconciled status.

## autocomplete
anoopmb created the original autocomplete feature for FA
http://frontaccounting.com/punbb/viewtopic.php?id=6198
but this was not incorporated into the core.
This option (config.php: $auto_select_box = true)
adds an autocomplete feature to all lists.

When this option is set to "true",
an autocomplete list is updated after every character
of the search text is entered into the menu box.
Thus, once the desired search item appears on the autocomplete list,
the user can make just one click to select the search item.

The original feature did not work with the popup search.
BF enables the popup search for these lists.

The original feature disabled itself if any of the search box
checkboxes were checked in Company Setup.
BF allows both features to be used.
The stock item checkbox is necessary for barcode scanning to work.
However,
the F1 or CONTROL-B keys now activates the search box
instead of the space key because space is a valid search character
for the autocomplete function.

For stock items,
the BF autocomplete option searches by item code or description.
A search is initiated with either the space bar or control-b.
The latter is useful for barcode readers programmed to
initiate a barcode with control-b.

## improve reports
FA displays an empty PDF if there is no data to display.

BF displays a message.

## Add delete link, direct link to void trans
Voiding a transaction is time-consuming and error prone in FA because
the user must provide a transaction number.

BF adds a red X icon to transactions.
Deleting transaction becomes as simple as clicking the red X
rather than having to navigate to Setup->Void.

## Add dimension to payments
FA does not dimension payments, A/R or A/P.
In cash basis accounting,
these accounts are income/expenditure accounts
rather than balance sheet accounts.
Thus the P/L for cash basis by dimension will not be correct.

BF dimensions payments, A/R, and A/P.

## allow negative quantity and amount on invoices
FA does not support invoices or payments
with negative quantities or amounts for customer refunds.
It expects that a credit memo be issued.
Nor does FA does not have the ability
to create a direct credit memo, nor does is it able to enter
purchased items and returned items on the same invoice.

BF allows this.
Besides code changes, this required altering
the "amt" field in table cust_allocations from "double unsigned"
to "double":

    mysql alter table cust_allocations modify amt double;

This has no adverse effect, and vanilla FA will also work
correctly on the altered table.

Note that negative quantities on an invoice
are returned to stock in BF.  If the customer returned an
item that cannot be returned to stock, then an inventory
adjustment will need to be made.  This is a manual operation.

## display balance in balance column and add debit/credit total
FA G/L inquiry is confusing because it displays the ending balance
under debit/credit.

BF displays the debit/credit in the proper columns
and the balance under the balance column.

## customer inquiry: display zero invoices; filter on tax group
BF displays zero invoices.
A zero invoice is often used when supplying
a customer with free product for marketing purposes.

## rename statement to register
FA uses the confusing term Bank Statement on Bank Account Inquiry
and on reports.

BF renames Bank Statement to Bank Account Inquiry and changes
reports to use Bank Register,
because data displayed is from FA
(all transactions entered)
and not from a bank statement
(all transactions processed by the bank).

## check_negative_stock allow for rounding on imprecise amounts
Decimal precise stock quantities
cause problems in FA because it is impossible to get them zeroed out.

BF assumes stock is zero when it falls below the precision
specified in Setup.

## g/l list description first to enable search by description

## FEATURE: use ajax for numeric calculations (part 1: purchasing)
FA is not a very responsive calculator or spreadsheet,
requiring the user to click an update button to do any math.

BF enables ajax on numeric text fields so that totals
are calculated as the user enters data in fields.

## add editor/delete funcs to reconcile

## allow user_transaction_days to be in future (negative)
BF allows a negative user_transaction_days in Setup so that
pages will display out into the future.

## supplier and customer name links on inquiry pages
BF makes supplier and customer names a html link
to their respective information pages
on the banking inquiry pages.
This allows one to quickly check or modify supplier/customer
information from the inquiry pages.
BF also adds the magnifying glass search icon next to these names
and this links to the supplier/customer inquiry pages.

## allow any user to edit another user transaction (disable user reference check)
BF allows a user to edit other user's transactions.

## move due date on invoice from header to footer and display only if delayed
FA displays a due date on direct supplier invoices.

BF only displays the due date if the transaction is delayed.
That is because it is meaningless if the transaction is being processed
immediately.

## remove more zero entries from Customer Balance report
The FA Customer Balance report still prints some zero entries
when no-zeros is selected.

BF fixes this problem.

## ajax enable account pull down menu on gl and bank inquiry
BF displays data without having to hit "Search".

## Scroll table instead of page
FA scrolls the whole screen on the inquiry pages.

BF scrolls the table instead of the page
and automatically positions the scroll at the latest entries.

## powderblue color on tax override
When creating a supplier invoice,
FA starts doing tax overrides after the "Update" key is pressed.
There is no visual indication that FA will stop
calculating the tax after adding another item.

BF colors the tax fields to indicate override.

## Enable Enter key on pop-up search
FA ignores the Enter key.

BF retains the Enter key.
This means that data can be submitted with the Enter key
(as the user would expect)
because the user only needs to type the search clause
and hit enter, without having to move the mouse and click on "Search".

## Support for Enter payment on bank inquiry page
BF adds a pulldown menu at the top of bank inquiry page
to enter another payment, deposit, bank transfer or general journal transaction.
Often a user will keep the bank inquiry page open
and continually add transactions from there, rather than navigating
around through FA's pages of links.

## add total equities line to balance sheet report
The balance sheet report is missing a subtotal. It displays a Total Liabilities and Equities line at the bottom, which is a grand total of the Liabilities, Total Equity and Calculated Return.

This mod adds a Total Equities line to the report, which is equal to Total Equity and Calculated Return, which is the actual shareholder equity during the period.

With this mod, the Total Liabilities and Equities line then makes obvious sense.

## add missing spaces to rep102.php sql
report sql was broken because of missing spaces. Report works much better now.

## Update inst_lang.php
Fix for:
Illegal offset type in file: inst_lang.php at line 139

If you have error reporting enabled, you get this error when installing a language, at least manually. While the error can be ignored because the language successfully installs, it is disconcerting.

## Add frequently used dates to date picker
This mod updates the date picker to add frequently picked dates at the bottom of the date picker. Frequently, a person wants to review activity for the entire year or prior year. The current date picker does not offer these options, and requires so many clicks to to get these dates, that it is easier not to use the date picker and just enter the dates using the text interface.  This mod places these dates at the bottom of the date picker.

The Back button is replaced by clicking on the date picker again, to hide or show it (i.e. toggle). Toggling is an expected behaviour by web users, as they have become accustomed to toggling the display of select menus.

The tiny characters to go to the previous/next month/year
are replaced by "Prev/Next" and the actual year,
making it easier to click on them
because of the larger footprint.

## Add paging and scrolling to select pop-up
In FA, the select pop-up shows a (configurable) limited amount of items.
This can confuse the user into thinking that the search item does
no exist when in reality the search was limited.

BF adds scrolling and paging to the select pop-up
and no items are eliminated.
Internally,
the custom select display code was replaced with the built-in db_pager function.
This eliminates many lines of code and makes the user interface consistent
with other FA search functions.

## Name browser tab titles more specifically
Renaming tabs with differing content makes it easier for
the user to keep track of multiple open tabs.
Set the browser tab title to the value of a search element on the page
Thus when the element changes value, the browser tab title changes as well.
For example, when the user does a bank account inquiry on a certain bank
account, the bank account name shows in the tab title rather than just
"Bank Account Inquiry".   Then if the user opens another tab and does
a bank account inquiry on a different bank account, the tabs have the
bank account names rather than the duplicate title "Bank Account Inquiry".

## Change Quick Entries maintenance to use drop down menu instead of table
FA uses a table to select and modify quick entries.
If many quick entries are used,
this table quickly grows to an unmanageble size,
and it becomes time-consuming to scroll down the page to edit
the quick entry data.

BF replaces the table with a drop down menu.  Advantages are easy search,
eliminates scrolling down the page to edit the data,
presents an identical interface to other FA pages (like GL Accounts, etc),
and reduces and reuses existing code.

## G/L search on Quick Entries
FA does not offer a way to search for transactions for quick entries.

BF allows G/L search on Quick Entries.

## BUGFIX: 0004475: Editing GJ transaction with no counterparty displays incorrect edit table
## FEATURE: Add legal text to payment terms
FA provides a simple mechanism to add a small amount of legal text to the footer of all invoices in Setup->System and General GL Setup.

BF retains this mechanism, but also adds legal text to payment terms, which appear on invoices using those terms. 
Payment terms often determine the content of the legal text
at the footer of the invoice.
For example, a cash invoice does not require language about
payment defaults.
In addition, it allows the legal text
to be tailored by customer or order, which is useful for different
customer types or customers located in other jurisdictions, where
differing legal text is required specific to those districts.

BF removes all the hardcoded
boiler plate footer info (e.g. Bank name, currency), relying instead
entirely on the legal text fields in setup or payment terms.
BF also checks the size of the legal text,
wraps it automatically on space boundaries and reduces the font size accordingly if necessary to fit into the allocated footer space.
These changes accomodate longer legal text than is possible in FA.

This feature required a database change by adding a legal text field to the payment_terms table.
You will need to add this field if you want to use BF instead of FA.
```
ALTER TABLE `0_payment_terms` ADD COLUMN `legal_text` varchar(4096);
```

## FEATURE: Add system preference option to sort g/l accounts by name rather than account code
## FEATURE: Add ability to enter payment memo on direct invoice
The FA direct invoice feature can create a payment as well as an invoice.
However, FA does not offer a way to directly enter the payment memo,
which is set to "<pos name> # <invoice number>".
(Although one can edit the memo afterwards).

BF adds another field to the cart, payment_info.  This field is tacked
onto the end of the FA generated memo.

## BUGFIX: 0004490: Cannot edit a quick entry transaction properly if the quick entry type changes
## FEATURE: Return to referer
The FA pencil on several inquiry forms is used to modify a transaction.
After the transaction is modified, the user has to use the back button
to get back to the inquiry form.

BF has expanded inquiry forms to have a delete link which calls the
void transaction and links to enter another payment and deposit.

This new feature returns control back to the inquiry form that calls
the modify, enter or delete links.

The result is that one can keep a bank account open to visually monitor
transactions while entering, modifying and deleting transactions.
This presents a user interface similar to the Quickbooks register,
where one can initiate all kinds of transactions from a single page.

## FEATURE: Make default account same as earlier transaction (expanded)
On Feb 2016, cambell-prince added the original feature to FA
with this pull request:
https://github.com/FrontAccountingERP/FA/pull/10.
The feature applied only to payments to suppliers made with
the Payments to Suppliers form and recalls the last bank account
used for the supplier made with this form.

BF generalizes this feature such that it applies to payments and deposits
made with Banking and General Ledger
and Customer Payments
for any person type (customer, supplier, miscellaneous, or quick entry).
It also replaces the code for Payments to Suppliers.
Thus, if a payment to a supplier is made with either interface,
the bank account from that payment will be recalled.

## FEATURE: system preference option to sort item lists by description rather than by stock id
## FEATURE: Hide Process Button until add/edit is confirmed/canceled
BF removes the Process Button while payment items are being added or edited;
Otherwise, if the user neglects to confirm, the work is lost unexpectantly.

## FEATURE: confirm price changes on invoice entry when changing suppliers/customers
During invoice entry, FA automatically changes prices
(and related fields, including supplier reference)
if the supplier/customer is changed.
This allows one to compare prices between suppliers
or change prices for another customer who may have a differing price list.
This assumes that the item price information is up-to-date.

However, often the user is doing simple data entry and
may have forgotten to set the correct supplier/customer beforehand.
Changing the name afterwards loses the data entry
and can result in erroneous pricing.

BF provides a pop-up confirmation whether the user wants to reprice
the order or simply change the supplier/customer without
any additional update of the order.

## FEATURE: reissue customer invoice
FA offers very limited editing of customer invoices, just the invoice date.

The "Invoice Editable" extensions by boxygen
http://frontaccounting.com/punbb/viewtopic.php?id=7029
created an extension to allow full editing of invoices
created by Direct Invoice.
BF incorporates this technique into its core with these bug fixes:
1. move "void invoice" to before stock check to prevent stock check failure
2. change of customer did not void old invoice
3. do not create payments when editing invoice

Note that a FA user could accomplish the same thing by
manually performing a void of the invoice
and editing the remaining sales order,
followed by delivery and reinvoice.
However, a void of an auto generated delivery in FA zeroes all quantities
in the sales order, so this isn't a practical solution for direct invoices.

The order quantities could be be retained in the sales order,
but if the user does not realize that the deletion
of a sales invoice leaves the sales order intact,
and then creates another direct invoice,
the old sales order creates additional inventory demand
when none exists.
This is likely why FA zeroes the quantities in the sales order.
BF cancels the sales order rather leaving zeroed quantities in
an open sales order.

The differing behavior of invoice deletion creates confusion
for a user who creates both standard and direct invoices.
Ideally there should be a popup on a void of an invoice asking
if the deliveries and sales orders should be voided or left as is.

## FEATURE: create new customer option during sales invoice entry
It is convenient to be able to create a new customer during order entry.
While FA offers this ability with the F2 function key,
operation isn't quite optimal,
requiring a function key to activate a popup,
finding and selecting New Customer in the drop down menu,
creating the customer,
closing the popup,
and selecting the newly created customer in the customer list.

BF retains this feature but also adds "New Customer" to the customer list
and a "Create New Customer" link whenever "New Customer" is selected.
This requires only one additional click to create the customer
and after the customer is created, the order entry screen
is set to the new customer.

## FEATURE: add new customer defaults for sales type, sales area, tax group
## FEATURE: remember last bank account transfer
## BUGFIX:  0004594: Tax group still showing when inactive
## FEATURE: tax groups can be sales or purchases only
BF tax groups can be designated for suppliers or customers only.
This simply removes them from the list of tax groups when adding or modifying
suppliers or customers.
This can help prevent assignment of the incorrect tax group
to a supplier or customer.

    mysql alter table 0_tax_groups add no_sale tinyint not null;
    mysql alter table 0_tax_groups add no_purchase tinyint not null;

## BUGFIX:  0004606: Cannot add a zero price to an item with a purchase price in inventory adjustment
## BUGFIX:  0004614: Allocated journal entry payment shows in red (overdue) on customer transactions"
## FEATURE: manufacturing pick list costs from g/l accounts
The FA basic manufacturing feature supports two g/l additional costs,
named "Labour" and "Overhead".

In BF, these are renamed "Cost 1" and "Cost 2",
because these accounts can be used for any G/L account,
so the FA names can be inappropriate.

BF provides a link to open up the G/L account
and pick list costs from the G/L account.
This can assist in determining which costs are applicable
to the manufacturing process.

BF removes the restriction that a BOM is required,
to support simple manufacturing without components in inventory.
For example, a volume of a manufactured item
could be expanded by adding water
without any BOM needed.

## FEATURE: add "No Counterparty" to Counterparty drop-down list
In FA, General Journal forces the use of a counterparty if
the G/L account is listed in a supplier as an accounts payable account
or in a customer as an accounts receivable account.

This is inconsistent with Payments/Deposits, which allow posting
to these G/L accounts using Miscellaneous or Quick Entry.

BF adds "No Counterparty" to the Counterparty drop-down list
in General Journal.

## FEATURE: add ability to copy BOM to another parent
## FEATURE: find and mark inactive items during popup search
In FA, all of the popup searches (magnifying glass)
return inactive items
(except for the item search which does not ever show inactive items).
There is no checkbox option to configure this;
the checkbox option on the main page applies only to the dropdown list
on that page.

We have found that this is extremely confusing to our users.
Users who rely on the popup search
are unable to find inactive items
and the customer and supplier lists are bloated with inactive entries.

BF displays inactive entries, but marked with (INACTIVE)
in the name or description and sorted to the bottom of the list.
All lists function identically in this manner.

As a design note, the idea of yet another inactive checkbox was discarded
because the length of search lists in the popup tend to be short,
so the addition of marked inactive entries at the bottom
do not bloat the lists.
Additionally,
we have found that users often resort
to using the popup list when they are unable to find an item,
and do not guess that the reason is because the item was marked inactive.

## do not show bank deposits when invoice filter is selected in supplier inquiry
## add viewport for improved mobile device support
BF adds declares a viewport to size the FA view to the device size.
This should have no effect on the desktop, but makes it
easier to use FA on a mobile device.
While this is far from creating a truly mobile FA,
it is a good interim measure.

(One reason to use FA on a mobile device is the ability to
use the device touchscreen to sign PDF invoices generated by FA.
This is easy to do by adding the Adobe Sign & Fill App (or comparable)
to the device.)

See http://frontaccounting.com/punbb/viewtopic.php?pid=30616#p30616.

## select next item after item delete
When an item is deleted in FA, the item list is refreshed
and positioned to "New Item".
This is not very helpful, because often the user wants to delete
more than one item.
In addition,
it is useful to redisplay the list with the deleted item removed,
giving the user satisfaction that the item is indeed gone.

BF positions the list to the next item after the deleted one
and selects that item.
Thus, if the user is deleting a series of consecutive inventory items,
the user just has to keep hitting the delete key
to delete them.

## FEATURE: basic manufacturing option to specify single component/no bom
It is useful to be able to transfer one item to another
so that a single manufactured item can be made in different ways
(part substitution).

For example, item A is a manufactured item offered for sale,
item B is a manufactured item built one way
and item C is a manufactured item built another way (different BOM).
Thus to replenish the supply of item A,
either B or C can be transferred to item A.

Item transfer can also be used to manufacture of items sized by volume.
For example, manufactured item A is the blend of item B and item C;
where volume(A) = volume(B) + volume(C).

One could accomplish this in FA Basic Manufacture
by creating a BOM of item A with component ratios,
but this often difficult to calculate in practice,
especially if the exact blend is inconsequential.
Often one wants to add ingredients on a step-wise basis
before the final quantity is known.

A better way is to use FA Advanced Manufacture
which can assign issues
("ingredients" or "raw materials") to a work order.
It does not require a BOM.
For manufactured items where the intermediate result is
not desired, each ingredient can be added
and the quantity produced can be edited.

However, if intermediate results are needed
(such as if the item is needed in another manufacturing process),
the item needs to be produced and another
workorder is required for further work.

Multiple workorders for a single item
are also needed when the manufacturer
wants to monitor the manufactured item in inventory rather than
as works-in-progress in the manufacturing tab.
For example,
a legal authority monitoring alcohol inventory does not want to
see alcohol disappear from inventory when it is used as an ingredient
in Advanced Manufacture for a blended item that has not yet
been produced.

So when multiple workorders are needed to add each ingredient,
an easier process is desired.

FA Basic Manufacturing requires a BOM and does not
allow the assignment of issues.

BF modifies Basic Manufacturing
so that a BOM is not required, just like for Advanced Manufacture.
BF allows the addition of a single issue
to support item transfer and one ingredient per workorder.
Using this approach,
Basic Manufacturing is simpler than Advanced Manufacturing
when a single item/multiple workorder production process is desired.

This is strictly a user-interface change.
No database changes were needed.

## FEATURE: allow component stock item to be changed on BOM
FA disallows changing the component stock item during BOM edit. 
If one wants to change a stock item on a BOM, one has to delete it
and then add the desired one.
This is slow and error prone.

BF allows changing the component stock item.

## FEATURE: inactive checkbox and zero inventory button on inv. adjustments
BF adds a checkbox to show inactive inventory items and
a "zero inventory" button to zero out an inventory item.

## FEATURE: Allow searching of all stock items by foreign code and hide them from lists
FA allows searching by foreign codes during sales order entry.
This supports sales of stock items using these codes, usually UPC/EAN.
Thus a barcode scanner can be used for sales order entry.

However, these additional codes clutter the visable lists
which are also used for manual entry.

BF removes these codes from the visable lists
while enablng search for these codes in any list of stock items,
which is helpful in inventory management,
such as when entering purchase order receipts.

BF also allows a foreign code to be a regular expression (RE).
REs are useful to map multiple barcodes into a single inventory item.
For example,
a supplier offer a series of barcodes for essentially the same item,
at least from the buyers perspective.
While these can be entered as additional foreign codes,
alternatively an RE can be devised to handle them as a single foreign code.

## BUGFIX: 0004664: gl_bank.php use of $_SESSION['pay_items'] corrupted by multiple tabs entering/modifying payments
## Retain date on successive order entry
## BUGFIX: 0004743: Modifying a bank transfer from a cash account by changing date to an earlier date can fail
## Confirm when user exits/refreshes sales invoice page and data has been entered
## FEATURE: Display transactions but not QOH for service items
In FA, bug fix "0004641: items->transactions should not show QOH for service items"
was fixed by disabling transactions when non inventory item.
While this is arguably the simple fix,
it left FA with no way to display transactions for service items,
which can be a useful way to search for orders for a service item.

BF retains the transactions tab for service items, but removes the QOH field,
which is not meaningful.

## FEATURE: Support for tax rate lookup by address
For goods that are shipped to a customer location,
the states of the United States are quickly migrating to
a system of charging tax according to the jurisidication in which the customer resides.

Because there are many such jurisdications per state
(over 600 in Colorado for example)
and 50 states,
maintaining an ecommerce site using a database of tax rates built into an accounting
system is quickly becoming unmanageable.
In FA, one must create tax types, tax groups, and g/l accounts for
each of these jurisdications,
assign the correct jurisdication to the customer when the customer is created,
and maintain this mass of data in the face of ongoing tax rate changes which
can occur at any time.

BF provides support for tax rate lookup by address,
pushing the tax rate data responsibility to an external database.

This is done in a similar fashion to currency rate lookup.
There is a new hook in hooks.php: retrieve_tax_rate($tax_group, $address).
To override the FA tax system for a given tax_group,
create an extension with this hook function
and return the rate that based on the passed $address parameter.

The address is supplied internally from the customer branch address
in the third argument of the set_branch() call to set the tax rate in the cart,
replacing the tax_group_name parameter which was defined but not used.
The set_branch() function
now calls retrieve_tax_rate()
and if a tax rate is returned, it uses that.
Otherwise it uses the FA tax rate data just as in FA.
External software (i.e. extensions) can call set_branch() directly to set the tax rate.

Note that there is an additional function in FA, set_delivery() which
sets a delivery address and would appear to be more appropriate for
setting the tax rate.  However, delivery address is always
set to the internal branch address.
It is copied to the order table, so one minor difference is that it remains
unchanged in the order even if the customer branch address is changed.

## check all/none buttons on bank reconcile and remember dates after refresh
## improve memo search and display on gl inquiry
Display and allow search on the gl account transaction memo as well as
the bank transaction memo.

## add g/l category search to g/l popup search
## retain date on successive supplier entry
## filter out voided transactions in supplier and customer inquiry
## make release date the default date for advanced manufacturing
## do not grey out item type and units when item has foreign codes
## auto calculate g/j difference to make g/j entry faster

## improve adding and downloading attachments 
BF makes the following improvemnts:
* BF adds an attachment icon on inquiry pages to make adding attachments easier.
* The attachment filename on viewing or download is the same as the name used
to store the attachment and an appropriate filename extension is generated based
on the MIME type.
* When adding an attachment through the inquiry page or the data entry subpage,
only the attachments for that document are shown,
resulting in less clutter.
* The attachment page is more compact.
## do not show voided customer deliveries
## Add start date/end date to customer statement/aged customer analysis
The customer statement and aged customer analysis reports in FA
have an option "Show Also Allocated" that shows transactions that have
not been fully allocated and all transactions.
To get accurate balances
(for example, see http://frontaccounting.com/punbb/viewtopic.php?pid=20867#p20867,
the option Show Also Allocated must be true.
However, this will show every transaction from the start of time.
Furthermore, the aged customer balance table will often not be correct because
the code does not analyze transation aging dynamically (for example, see
http://frontaccounting.com/punbb/viewtopic.php?id=7522).

The BF solution is to remove the "Show Also Allocated" option from these reports
and show all transactions
(as if Show Also Allocated was true)
and add start/end dates to these reports
to limit the transactions display.
This is how the FA Customer Balances report works and makes these reports consistent.

Finally, the customer balance table is constructed using allocations
(as if Show Also Allocated was false).
This means that in order to get a correct customer balance table,
all transactions must be properly allocated.

BF also correctly handles allocated journal entries.
This is the only core charge; the BF reports are extensions.

## select amount text with click
See, http://frontaccounting.com/punbb/viewtopic.php?pid=34388#p34388.

## g/l inquiry search on Miscellaneous names

## filter sales items using sales_type
When a sales invoice item is entered in FA, if a price is not defined for a given
sales type, FA picks whatever price it finds,
without any alert to the user.
So if a user is entering wholesale invoices, perhaps retail prices
may appear, causing incorrect billing.
Even if the user catches the mistake,
the user might just override the price during invoice entry
rather than bothering to update the item,
leaving the same problem for the next user.

In BF, only those sales items that have a price for the given sales type
are able to be picked from the menu, making incorrect billing impossible.
It forces the user to update the item with the correct price in order
for the invoice to be created.

## default customer payment branch to first allocation
In FA, when entering a customer payment for a customer that has multiple branches,
a branch must also be chosen for the customer.
This is a step easily missed.

BF defaults the branch to the first item in the allocation list, because usually
the payment is for this particular item.  (It would be preferable to default the
branch to the allocation item actually selected, but this is more difficult to code,
and still does not make any sense if the payment was for allocations to different
branches).

Note: it is unclear to me why payments have the customer branch anyway.  The payment
is allocated correctly regardless if the payment branch and the invoice branch
do not match.  I think it would be better if the branch selection was removed
from the customer payment screen, but this would affect other areas of FA
(customer statements?) that display or use the customer branch,
and that would have to be researched.

## show all accounts of gl transaction during gl search
GL Inquiry lists transactions in two different ways.
If a G/L account is not specified (e.g. all accounts),
all accounts of the gl transactions are displayed.
If a G/L account is specified, only the matching account of the transaction
is shown, and balances are shown.

In FA, if search criteria are used,
and a G/L account is not specified,
only one account of the matching transaction is shown
In BF, all accounts of the matching transaction are shown
if a G/L account is not specified,
regardless of whether search criteria are used.

Seeing all accounts of the transaction is what one expects to see
when "All Accounts" is specified.
This is particularly useful in the Quick Report extension
that shows all accounts of the transaction on a single line.

## expand the use of customer branch in reporting
FA features a two tier system of customers: the customer and customer branches.
Yet there is very limited or no support for display, searching or sorting by branch.
Many of the reports display only by customer with no breakdown by branch.
(Perhaps sage advice in vanilla FA is to use only one branch per customer
to avoid difficulty.)

BF seeks to remedy this deficiency.  The Sales Summary Report (rep114) is updated to separate
sales by customer branch.  The Inventory Sales Report (rep304) allows filtering by branch.

## do not clear customer payment allocation table/memo when updating bank account on single currency systems
On the Customer Payment Entry screen, if the bank account is updated, the allocations
table is refreshed and other data is cleared.
FA does this because the bank account currency could change which would affect
the display of this table.

However, on single currency installations, this is unnecessary, because the currency does not change.
BF does not refresh in this case.

## display memo on inventory adjustments, delete link, and display inquiry on inventory tab
## display memo on view/print transactions, simplify code, and sort by date
## BUGFIX: Manufacturing Values Off By Small Amounts
See https://frontaccounting.com/punbb/viewtopic.php?id=8662

## Restore dies with php memory error
Restore of a large database returned this error:

Allowed memory size of 134217728 bytes exhausted (tried to allocate 94 bytes) in
core/admin/db/maintenance_db.inc on line 325

I would hope that the restore function could be rewritten as not to consume an
enormous amount of memory.

The workaround was to increase the php memory_limit but this is just a bandaid.
====================================================================== 

---------------------------------------------------------------------- 
 (0018225) janusz (administrator) - 2019-06-12 15:18
 http://mantis.frontaccounting.com/view.php?id=4745#c18225 
---------------------------------------------------------------------- 
Enormous file upload consumes enormous memory. Do you have any preposition how
to address this issue for arbitrary file size?

Workaround of uploading backup via other tools like cmndline mmyslq client
should work to some extent. 

---------------------------------------------------------------------- 
 (0018756) braathwaate (reporter) - 2019-12-02 23:05
 http://mantis.frontaccounting.com/view.php?id=4745#c18756 
---------------------------------------------------------------------- 
Reading the file line by line rather than reading the entire file into memory
can cuts memory consumption for standard restore in half.
This code change is not a complete solution but is a step in the right direction.

---------------------------------------------------------------------- 
 (0018921) janusz (administrator) - 2020-01-24 21:31
 http://mantis.frontaccounting.com/view.php?id=4745#c18921 
---------------------------------------------------------------------- 
This is not enough to solve the limitation. db_import stores all queries from
file in temporary tables, to preserve right order in import. Also sql compressed
files could not be processed line by line. 

The php memory limit can be safely increased for small servers, and where it is
not possible, there is always another way to import backup file like phpmyadmin
or in mysql client, so I think it is not worth the effort to fix this limitation
in app code.

I will close the report for now, however if we will find complete solution we
will integrate it in the codebase. 

## reload page on ajax failure
See, https://frontaccounting.com/punbb/viewtopic.php?id=8672.

## 0005081: Editing a customer payment which is not allocated to an invoice and using the allocation table changes the payment amount
BF automatically fills in allocation amounts.

## Order customer allocation invoices by due date rather than transaction number
FA orders the list of invoices to be allocated to a customer payment
by transaction number.
Transaction number is meaningless to the user because it is just a database file index
and invoice dates could have changed or been voided and readded
causing the list of invoices to be out of date order.

BF orders the invoices by due date, because usually an incoming payment
is allocated to the earliest invoices.
This makes it consistent with supplier application invoices,
which are already ordered by due date in FA.

## Order customer transactions by transaction date and filter out deliveries for All Types

## Default Shipping Company
FA orders the list of shippers by name.
While alphabetic order is pleasing, especially if there are alot of shippers,
the default shipper becomes the first shipper in the alphabetic list, which is probably not desired.

FA makes the default shipper the first shipper as as shown in Setup->Shipping Company.
(The correct way to define the default shipper would be in Setup->System and General GL Setup,
but this would require more code changes, so I leave this feature request to the development team).

## Allow deletion of items that have foreign codes or are used in kits
If an item has a foreign code or is used in a kit, FA prevents deletion of the item.
This makes it time-consuming to delete items because the codes must be manually
deleted before the item can be deleted.

See also, "do not grey out item type and units when item has foreign codes"

## BUGFIX:  Different Service Item Description From Same Vendor
See, https://frontaccounting.com/punbb/viewtopic.php?id=8754

## FEATURE: Allow inactive G/L accounts that are bank accounts
FA does not allow making a G/L account inactive if it is a bank account.

BF allows making a bank G/L account inactive if the bank account is inactive.
It prevents making an inactive bank account active if the G/L account is inactive.

## FEATURE: Retain date on successive bank payment/deposit entry
## FEATURE: Return quantity needed on low stock check
## FEATURE: Add links to customer transaction inquiry to create or email customer invoice
## FEATURE: Replace FA landing page after order entry with inquiry page
The common design of FA is to display a landing page after
order entry with text hyperlinks of where to go next.

Experimentally, BF replaces the landing page with the appropriate inquiry page
with the just-entered order displayed.
I believe that this approach is superior for several reasons:
* the inquiry page displays the same options in familiar icon format
* text is hard to follow; each text page is different
* visual feedback to the user that the order was correctly entered (i.e. date/customer/amount)
* the text page is additional unnecessary duplicate code

## FEATURE: Default the last wo costing account rather than the last bank account in mfg
## BUGFIX: Email using Dear <first name> rather than Dear <last name>
## FEATURE: Running total for bank payments/deposits
In FA, one enters each line item in a bank payment/deposit and FA sums up
the lines and the result is the total of the bank payment/deposit.

In BF, one enters the total of the bank payment/deposit and as each new line item
is entered, the remaining amount to complete the payment/deposit is shown.
This is preferred because usually the total amount of the payment/deposit
is known but multiple line item amounts may not be known in advance, so BF
helps calculate them out as the line items are entered (such as allocating
the payment between two dimensions, for example).

## FEATURE: Make query variables (particularly date ranges) session-wide
FA resets query variables on inquiry pages each time the page is re-entered.
This is frustrating for a user who is examining data across multiple pages using
the same query variables.  This is particularly frustrating when the user sets
a specific date range, just to see it reset on the next inquiry page.

BF retains query variables as session variables, making the defaults the same
across the browser.

## FEATURE: Simplified supplier aging
FA assumes that a supplier is invoiced so that payments can be applied to that invoice.
While FA allows a supplier to be paid without an invoice, the supplier account will
go negative, making it appear that the supplier is overpaid.
Data entry staff must aware when a supplier should be paid by a supplier payment
or by a bank payment.

FA does not distinguish between a supplier payment and a bank payment.
Both can be allocated to an invoice.
The database updates are the same,
given that the bank payment is made to the supplier Accounts Payable G/L account.

But it is a likely error to allocate a bank payment or deposit to
an invoiced supplier with a G/L account other than Accounts Payable.
The supplier will appear to be paid in full,
but Accounts Payable will not reconcile and the G/L account that
was used will not be correct.
FA also does not currently allow a supplier payment to be edited.

BF offers a simplified supplier aging feature
that changes how supplier accounts are aged.
This feature is enabled with the config.php option "simplified_supplier_again=true".
Simplified aging is based only on Supplier Invoices, Payments and Credits.
Only supplier payments and credits can be allocated to supplier invoices.
Bank Payments/Deposits/General Journal Entries are not allowed and not calculated
in any balances.

If a need arises to increase the balance due of a suppliers account,
it cannot be done directly with a general journal entry
or bank payment.
Instead, a supplier invoice or supplier payment would be necessary.

If a need arises to decrease the balance due of a suppliers account,
it cannot be done directly with a general journal entry
or a bank deposit.
Instead, a supplier credit (merchandise return)
or refund would have to be generated.
A refund can only be issued through the customer payment page
if the account has been overpaid.
This creates a negative supplier payment
against Accounts Payable that can be allocated to
the supplier invoice.

Note that an argument could be made to simplify customer aging as well.
But there seems to be less confusion among data entry staff
if customers are always invoiced and payments
and are always entered as customer payments instead of bank deposits.

## FEATURE: retain reconciled status when editing customer payments
