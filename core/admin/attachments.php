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
$path_to_root="..";
$page_security = 'SA_ATTACHDOCUMENT';

include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/admin/db/attachments_db.inc");
include_once($path_to_root . "/admin/db/transactions_db.inc");

if (isset($_GET['vw']))
	$view_id = $_GET['vw'];
else
	$view_id = find_submit('view');
if ($view_id != -1)
{
	$row = get_attachment($view_id);
	if ($row['filename'] != "")
	{
		if(in_ajax()) {
			$Ajax->popup($_SERVER['PHP_SELF'].'?vw='.$view_id);
		} else {
			$type = ($row['filetype']) ? $row['filetype'] : 'application/octet-stream';	
    		header("Content-type: ".$type);
    		header('Content-Length: '.$row['filesize']);
 			header("Content-Disposition: inline; filename=" . '"' . $row['filename'] . mime2ext($row['filetype']) . '"');
	    	echo file_get_contents(company_path(). "/attachments/".$row['unique_name']);
    		exit();
		}
	}	
}
if (isset($_GET['dl']))
	$download_id = $_GET['dl'];
else
	$download_id = find_submit('download');

if ($download_id != -1)
{
	$row = get_attachment($download_id);
	if ($row['filename'] != "")
	{
		if(in_ajax()) {
			$Ajax->redirect($_SERVER['PHP_SELF'].'?dl='.$download_id);
		} else {
			$type = ($row['filetype']) ? $row['filetype'] : 'application/octet-stream';	
    		header("Content-type: ".$type);
	    	header('Content-Length: '.$row['filesize']);
    		header('Content-Disposition: attachment; filename=' . '"' . $row['filename'] . mime2ext($row['filetype']) . '"');
    		echo file_get_contents(company_path()."/attachments/".$row['unique_name']);
	    	exit();
		}
	}	
}

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);
page(_($help_context = "Attach Documents"), false, false, "", $js);

simple_page_mode(true);
//----------------------------------------------------------------------------------------
set_posts(array('filterType', 'trans_no'));

if ($Mode == 'ADD_ITEM' || $Mode == 'UPDATE_ITEM')
{
	
	$filename = basename($_FILES['filename']['name']);
	if (!transaction_exists($_POST['filterType'], $_POST['trans_no']))
		display_error(_("Selected transaction does not exists."));
	elseif ($Mode == 'ADD_ITEM' && !in_array(strtoupper(substr($filename, strlen($filename) - 3)), array('JPG','PNG','GIF', 'PDF', 'DOC', 'ODT')))
	{
		display_error(_('Only graphics,pdf,doc and odt files are supported.'));
	} elseif ($Mode == 'ADD_ITEM' && !isset($_FILES['filename']))
		display_error(_("Select attachment file."));
	elseif ($Mode == 'ADD_ITEM' && ($_FILES['filename']['error'] > 0)) {
    	if ($_FILES['filename']['error'] == UPLOAD_ERR_INI_SIZE) 
		  	display_error(_("The file size is over the maximum allowed."));
    	else
		  	display_error(_("Select attachment file."));
  	} elseif ( strlen($filename) > 60) {
		display_error(_("File name exceeds maximum of 60 chars. Please change filename and try again."));
	} else {
		//$content = base64_encode(file_get_contents($_FILES['filename']['tmp_name']));
		$tmpname = $_FILES['filename']['tmp_name'];

		$dir =  company_path()."/attachments";
		if (!file_exists($dir))
		{
			mkdir ($dir,0777);
			$index_file = "<?php\nheader(\"Location: ../index.php\");\n";
			$fp = fopen($dir."/index.php", "w");
			fwrite($fp, $index_file);
			fclose($fp);
		}

		$filesize = $_FILES['filename']['size'];
		$filetype = $_FILES['filename']['type'];

		// file name compatible with POSIX
		// protect against directory traversal
		if ($Mode == 'UPDATE_ITEM')
		{
		    $row = get_attachment($selected_id);
		    if ($row['filename'] == "")
        		exit();
			$unique_name = $row['unique_name'];
			if ($filename && file_exists($dir."/".$unique_name))
				unlink($dir."/".$unique_name);
		}
		else
			$unique_name = random_id();

		//save the file
		move_uploaded_file($tmpname, $dir."/".$unique_name);

		if ($Mode == 'ADD_ITEM')
		{
			add_attachment($_POST['filterType'], $_POST['trans_no'], $_POST['description'],
				$filename, $unique_name, $filesize, $filetype);
			display_notification(_("Attachment has been inserted.")); 
		}
		else
		{
			update_attachment($selected_id, $_POST['filterType'], $_POST['trans_no'], $_POST['description'],
				$filename, $unique_name, $filesize, $filetype); 
			display_notification(_("Attachment has been updated.")); 
		}
		reset_form();
	}
	refresh_pager('trans_tbl');
	$Ajax->activate('_page_body');
}

if ($Mode == 'Delete')
{
	$row = get_attachment($selected_id);
	$dir =  company_path()."/attachments";
	if (file_exists($dir."/".$row['unique_name']))
		unlink($dir."/".$row['unique_name']);
	delete_attachment($selected_id);	
	display_notification(_("Attachment has been deleted.")); 
	reset_form();
}

if ($Mode == 'RESET')
	reset_form();

function reset_form()
{
	global $selected_id;
	unset($_POST['trans_no']);
	unset($_POST['description']);
	$selected_id = -1;
}

function viewing_controls()
{
	global $selected_id;
	
    start_table(TABLESTYLE_NOBORDER);

	start_row();
	systypes_list_cells(_("Type:"), 'filterType', null, true);
	if (list_updated('filterType'))
		reset_form();

	if(get_post('filterType') == ST_CUSTOMER ){
		customer_list_cells(_("Select a customer: "), 'trans_no', null, false, true, true);
	} elseif(get_post('filterType') == ST_SUPPLIER){
		supplier_list_cells(_("Select a supplier: "), 'trans_no', null,  false, true,true);
	}

	end_row();
    end_table(1);

}

function trans_view($trans)
{
	return get_trans_view_str($trans["type_no"], $trans["trans_no"]);
}

function edit_link($row)
{
	if (!transaction_exists($row["type_no"], $row["trans_no"]))
        return '';
    else
        return button('Edit'.$row["id"], _("Edit"), _("Edit"), ICON_EDIT);
}

function view_link($row)
{
  	return button('view'.$row["id"], _("View"), _("View"), ICON_VIEW);
}

function download_link($row)
{
  	return button('download'.$row["id"], _("Download"), _("Download"), ICON_DOWN);
}

function delete_link($row)
{
  	return button('Delete'.$row["id"], _("Delete"), _("Delete"), ICON_DELETE);
}

function add_edit()
{
	global $selected_id, $Mode;

    if ($selected_id != -1)
    {
        if ($Mode == 'Edit')
        {
            $row = get_attachment($selected_id);
            hidden('unique_name', $row['unique_name']);
            hidden('trans_no', $row['trans_no']);
        }	
        hidden('selected_id', $selected_id);

        return array(array($row['trans_no'], 10),
            array(text_input('description', $row['description'], 40)),
            array("<input type='file' name='filename'>"),
            array(submit('UPDATE_ITEM', _("Update_Item"), false), "style='text-align:center' colspan=7"));
    }

    return array(array(text_input('trans_no'), 10),
        array(text_input('description', '', 40)),
        array("<input type='file' name='filename'>"),
        array(submit('ADD_ITEM', _("Add new"), false), "style='text-align:center' colspan=7"));
}

function display_rows($type, $trans_no)
{
	$sql = get_sql_for_attached_documents($type, $trans_no);
	$cols = array(
		_("#") => $type == ST_SUPPLIER || $type == ST_CUSTOMER? 'skip' : array('fun'=>'trans_view', 'ord'=>''),
	    _("Description") => array('name'=>'description'),
	    _("Filename") => array('name'=>'filename'),
	    _("Size") => array('name'=>'filesize'),
	    _("Filetype") => array('name'=>'filetype'),
	    _("Date Uploaded") => array('name'=>'tran_date', 'type'=>'date'),
	    	array('insert'=>true, 'fun'=>'edit_link'),
	    	array('insert'=>true, 'fun'=>'view_link'),
	    	array('insert'=>true, 'fun'=>'download_link'),
	    	array('insert'=>true, 'fun'=>'delete_link')
	    );	

	$table =& new_db_pager('trans_tbl', $sql, $cols);

	$table->width = "60%";
        $table->set_footer("add_edit");

	display_db_pager($table);
}

function mime2ext($mime) {
        $mime_map = [
            'video/3gpp2'                                                               => '3g2',
            'video/3gp'                                                                 => '3gp',
            'video/3gpp'                                                                => '3gp',
            'application/x-compressed'                                                  => '7zip',
            'audio/x-acc'                                                               => 'aac',
            'audio/ac3'                                                                 => 'ac3',
            'application/postscript'                                                    => 'ai',
            'audio/x-aiff'                                                              => 'aif',
            'audio/aiff'                                                                => 'aif',
            'audio/x-au'                                                                => 'au',
            'video/x-msvideo'                                                           => 'avi',
            'video/msvideo'                                                             => 'avi',
            'video/avi'                                                                 => 'avi',
            'application/x-troff-msvideo'                                               => 'avi',
            'application/macbinary'                                                     => 'bin',
            'application/mac-binary'                                                    => 'bin',
            'application/x-binary'                                                      => 'bin',
            'application/x-macbinary'                                                   => 'bin',
            'image/bmp'                                                                 => 'bmp',
            'image/x-bmp'                                                               => 'bmp',
            'image/x-bitmap'                                                            => 'bmp',
            'image/x-xbitmap'                                                           => 'bmp',
            'image/x-win-bitmap'                                                        => 'bmp',
            'image/x-windows-bmp'                                                       => 'bmp',
            'image/ms-bmp'                                                              => 'bmp',
            'image/x-ms-bmp'                                                            => 'bmp',
            'application/bmp'                                                           => 'bmp',
            'application/x-bmp'                                                         => 'bmp',
            'application/x-win-bitmap'                                                  => 'bmp',
            'application/cdr'                                                           => 'cdr',
            'application/coreldraw'                                                     => 'cdr',
            'application/x-cdr'                                                         => 'cdr',
            'application/x-coreldraw'                                                   => 'cdr',
            'image/cdr'                                                                 => 'cdr',
            'image/x-cdr'                                                               => 'cdr',
            'zz-application/zz-winassoc-cdr'                                            => 'cdr',
            'application/mac-compactpro'                                                => 'cpt',
            'application/pkix-crl'                                                      => 'crl',
            'application/pkcs-crl'                                                      => 'crl',
            'application/x-x509-ca-cert'                                                => 'crt',
            'application/pkix-cert'                                                     => 'crt',
            'text/css'                                                                  => 'css',
            'text/x-comma-separated-values'                                             => 'csv',
            'text/comma-separated-values'                                               => 'csv',
            'application/vnd.msexcel'                                                   => 'csv',
            'application/x-director'                                                    => 'dcr',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
            'application/x-dvi'                                                         => 'dvi',
            'message/rfc822'                                                            => 'eml',
            'application/x-msdownload'                                                  => 'exe',
            'video/x-f4v'                                                               => 'f4v',
            'audio/x-flac'                                                              => 'flac',
            'video/x-flv'                                                               => 'flv',
            'image/gif'                                                                 => 'gif',
            'application/gpg-keys'                                                      => 'gpg',
            'application/x-gtar'                                                        => 'gtar',
            'application/x-gzip'                                                        => 'gzip',
            'application/mac-binhex40'                                                  => 'hqx',
            'application/mac-binhex'                                                    => 'hqx',
            'application/x-binhex40'                                                    => 'hqx',
            'application/x-mac-binhex40'                                                => 'hqx',
            'text/html'                                                                 => 'html',
            'image/x-icon'                                                              => 'ico',
            'image/x-ico'                                                               => 'ico',
            'image/vnd.microsoft.icon'                                                  => 'ico',
            'text/calendar'                                                             => 'ics',
            'application/java-archive'                                                  => 'jar',
            'application/x-java-application'                                            => 'jar',
            'application/x-jar'                                                         => 'jar',
            'image/jp2'                                                                 => 'jp2',
            'video/mj2'                                                                 => 'jp2',
            'image/jpx'                                                                 => 'jp2',
            'image/jpm'                                                                 => 'jp2',
            'image/jpeg'                                                                => 'jpeg',
            'image/pjpeg'                                                               => 'jpeg',
            'application/x-javascript'                                                  => 'js',
            'application/json'                                                          => 'json',
            'text/json'                                                                 => 'json',
            'application/vnd.google-earth.kml+xml'                                      => 'kml',
            'application/vnd.google-earth.kmz'                                          => 'kmz',
            'text/x-log'                                                                => 'log',
            'audio/x-m4a'                                                               => 'm4a',
            'application/vnd.mpegurl'                                                   => 'm4u',
            'audio/midi'                                                                => 'mid',
            'application/vnd.mif'                                                       => 'mif',
            'video/quicktime'                                                           => 'mov',
            'video/x-sgi-movie'                                                         => 'movie',
            'audio/mpeg'                                                                => 'mp3',
            'audio/mpg'                                                                 => 'mp3',
            'audio/mpeg3'                                                               => 'mp3',
            'audio/mp3'                                                                 => 'mp3',
            'video/mp4'                                                                 => 'mp4',
            'video/mpeg'                                                                => 'mpeg',
            'application/oda'                                                           => 'oda',
            'audio/ogg'                                                                 => 'ogg',
            'video/ogg'                                                                 => 'ogg',
            'application/ogg'                                                           => 'ogg',
            'application/x-pkcs10'                                                      => 'p10',
            'application/pkcs10'                                                        => 'p10',
            'application/x-pkcs12'                                                      => 'p12',
            'application/x-pkcs7-signature'                                             => 'p7a',
            'application/pkcs7-mime'                                                    => 'p7c',
            'application/x-pkcs7-mime'                                                  => 'p7c',
            'application/x-pkcs7-certreqresp'                                           => 'p7r',
            'application/pkcs7-signature'                                               => 'p7s',
            'application/pdf'                                                           => 'pdf',
            'application/octet-stream'                                                  => 'pdf',
            'application/x-x509-user-cert'                                              => 'pem',
            'application/x-pem-file'                                                    => 'pem',
            'application/pgp'                                                           => 'pgp',
            'application/x-httpd-php'                                                   => 'php',
            'application/php'                                                           => 'php',
            'application/x-php'                                                         => 'php',
            'text/php'                                                                  => 'php',
            'text/x-php'                                                                => 'php',
            'application/x-httpd-php-source'                                            => 'php',
            'image/png'                                                                 => 'png',
            'image/x-png'                                                               => 'png',
            'application/powerpoint'                                                    => 'ppt',
            'application/vnd.ms-powerpoint'                                             => 'ppt',
            'application/vnd.ms-office'                                                 => 'ppt',
            'application/msword'                                                        => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/x-photoshop'                                                   => 'psd',
            'image/vnd.adobe.photoshop'                                                 => 'psd',
            'audio/x-realaudio'                                                         => 'ra',
            'audio/x-pn-realaudio'                                                      => 'ram',
            'application/x-rar'                                                         => 'rar',
            'application/rar'                                                           => 'rar',
            'application/x-rar-compressed'                                              => 'rar',
            'audio/x-pn-realaudio-plugin'                                               => 'rpm',
            'application/x-pkcs7'                                                       => 'rsa',
            'text/rtf'                                                                  => 'rtf',
            'text/richtext'                                                             => 'rtx',
            'video/vnd.rn-realvideo'                                                    => 'rv',
            'application/x-stuffit'                                                     => 'sit',
            'application/smil'                                                          => 'smil',
            'text/srt'                                                                  => 'srt',
            'image/svg+xml'                                                             => 'svg',
            'application/x-shockwave-flash'                                             => 'swf',
            'application/x-tar'                                                         => 'tar',
            'application/x-gzip-compressed'                                             => 'tgz',
            'image/tiff'                                                                => 'tiff',
            'text/plain'                                                                => 'txt',
            'text/x-vcard'                                                              => 'vcf',
            'application/videolan'                                                      => 'vlc',
            'text/vtt'                                                                  => 'vtt',
            'audio/x-wav'                                                               => 'wav',
            'audio/wave'                                                                => 'wav',
            'audio/wav'                                                                 => 'wav',
            'application/wbxml'                                                         => 'wbxml',
            'video/webm'                                                                => 'webm',
            'audio/x-ms-wma'                                                            => 'wma',
            'application/wmlc'                                                          => 'wmlc',
            'video/x-ms-wmv'                                                            => 'wmv',
            'video/x-ms-asf'                                                            => 'wmv',
            'application/xhtml+xml'                                                     => 'xhtml',
            'application/excel'                                                         => 'xl',
            'application/msexcel'                                                       => 'xls',
            'application/x-msexcel'                                                     => 'xls',
            'application/x-ms-excel'                                                    => 'xls',
            'application/x-excel'                                                       => 'xls',
            'application/x-dos_ms_excel'                                                => 'xls',
            'application/xls'                                                           => 'xls',
            'application/x-xls'                                                         => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
            'application/vnd.ms-excel'                                                  => 'xlsx',
            'application/xml'                                                           => 'xml',
            'text/xml'                                                                  => 'xml',
            'text/xsl'                                                                  => 'xsl',
            'application/xspf+xml'                                                      => 'xspf',
            'application/x-compress'                                                    => 'z',
            'application/x-zip'                                                         => 'zip',
            'application/zip'                                                           => 'zip',
            'application/x-zip-compressed'                                              => 'zip',
            'application/s-compressed'                                                  => 'zip',
            'multipart/x-zip'                                                           => 'zip',
            'text/x-scriptzsh'                                                          => 'zsh',
        ];

        return isset($mime_map[$mime]) === true ? "." . $mime_map[$mime] : false;
    }

//----------------------------------------------------------------------------------------
if (list_updated('filterType') || list_updated('trans_no'))
	$Ajax->activate('_page_body');

start_form(true);

viewing_controls();

$type = get_post('filterType');

display_rows($type, get_post('trans_no'));

end_form();
end_page(true);

