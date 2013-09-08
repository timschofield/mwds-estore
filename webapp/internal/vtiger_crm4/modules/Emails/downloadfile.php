<?php
/********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
* 
 ********************************************************************************/

require_once('config.php');
require_once('include/database/PearDatabase.php');

global $fileId;

$fileId = $_REQUEST['filename'];
$dbQuery = "SELECT filename,filesize,data,filetype ";
$dbQuery .= "FROM email_attachments ";
$dbQuery .= "WHERE filename = '" .$fileId ."' and parent_id='".$_REQUEST['record'] ."'";
$result = $adb->query($dbQuery) or die("Couldn't get file list");
if($adb->num_rows($result) == 1)
{
$fileType = @$adb->query_result($result, 0, "filetype");
$name = @$adb->query_result($result, 0, "filename");
//echo 'filetype is ' .$fileType;
$fileContent = @$adb->query_result($result, 0, "data");
$size = @$adb->query_result($result, 0, "filesize");
header("Content-type: $fileType");
//header("Content-length: $size");
header("Cache-Control: private");
header("Content-Disposition: attachment; filename=$name");
header("Content-Description: PHP Generated Data");
echo base64_decode($fileContent);
}
else
{
echo "Record doesn't exist.";
}
?>
