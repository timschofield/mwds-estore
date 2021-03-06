<?php
////////////////////////////////////////////////////
// PHPMailer - PHP email class
//
// Class for sending email using either
// sendmail, PHP mail(), or SMTP.  Methods are
// based upon the standard AspEmail(tm) classes.
//
// Copyright (C) 2001 - 2003  Brent R. Matzelle
//
// License: LGPL, see LICENSE
////////////////////////////////////////////////////

/**
 * PHPMailer - PHP email transport class
 * @package PHPMailer
 * @author Brent R. Matzelle
 * @copyright 2001 - 2003 Brent R. Matzelle
 */

require_once('modules/Emails/Email.php');
require_once('include/logging.php');
require("class.phpmailer.php");
require_once('include/database/PearDatabase.php');

$local_log =& LoggerManager::getLogger('index');

echo get_module_title("Emails", $mod_strings['LBL_MODULE_NAME'], true); 

sendmail($_REQUEST['assigned_user_id'],$current_user->user_name,$_REQUEST['name'],$_REQUEST['description'],$mail_server,$mail_server_username,$mail_server_password);

function sendmail($to,$from,$subject,$contents,$mail_server,$mail_server_username,$mail_server_password)
{
global $adb,$root_directory,$mod_strings;

	$sql = $_REQUEST['query'];
	$result= $adb->query($sql);
	
	$noofrows = $adb->num_rows($result);

	$dbQuery = 'select attachments.*, activity.subject, emails.description  from emails inner join crmentity on crmentity.crmid = emails.emailid inner join activity on activity.activityid = crmentity.crmid left join seattachmentsrel on seattachmentsrel.crmid = emails.emailid left join attachments on seattachmentsrel.attachmentsid = attachments.attachmentsid where crmentity.crmid = '.$_REQUEST['return_id'];

        $result1 = $adb->query($dbQuery) or die("Couldn't get file list");
	$temparray = $adb->fetch_array($result1);

	$notequery = 'select  attachments.*, notes.notesid, notes.filename,notes.notecontent  from notes inner join senotesrel on senotesrel.notesid= notes.notesid inner join crmentity on crmentity.crmid= senotesrel.crmid left join seattachmentsrel  on seattachmentsrel.crmid =notes.notesid left join attachments on seattachmentsrel.attachmentsid = attachments.attachmentsid where crmentity.crmid='.$_REQUEST['return_id'];
	$result2 = $adb->query($notequery) or die("Couldn't get file list");

	        $mail = new PHPMailer();
	
                $mail->Subject =$adb->query_result($result1,0,"subject");

		$DESCRIPTION = $adb->query_result($result1,0,"description");
		$DESCRIPTION .= '<br><br>';
		$DESCRIPTION .= '<font color=darkgrey>'.$adb->query_result($adb->query("select * from users where user_name='".$from."'"),0,"signature").'</font>';

                $mail->Body    = nl2br($DESCRIPTION);
		$initialfrom = $from;
		$mail->IsSMTP();

if($mail_server=='')
{
        $mailserverresult=$adb->query("select * from systems where server_type = 'email'");
        $mail_server=$adb->query_result($mailserverresult,0,'server');
	$_REQUEST['server']=$mail_server;
}
		$mail->Host = $mail_server;
		$mail->SMTPAuth = true;
		$mail->Username = "";
		$mail->Password = "";
		$mail->From = $adb->query_result($adb->query("select * from users where user_name='".$from."'"),0,"email1");
		$mail->FromName = $initialfrom;
//		$mail->AddAddress($to);
		$mail->AddReplyTo($from);
		$mail->WordWrap = 50;

//store this to the hard disk and give that url

for($i=0;$i< $adb->num_rows($result1);$i++)
{
	$fileContent = $adb->query_result($result1,$i,"attachmentcontents");
	$filename=$adb->query_result($result1,$i,"name");
	$filesize=$adb->query_result($result1,$i,"attachmentsize");

	if(!@$handle = fopen($root_directory."/test/upload/".$filename,"wb")){}

	//chmod("/home/rajeshkannan/test/".$fileContent,0755);
	if(!@fwrite($handle,base64_decode($fileContent),$filesize)){}
	if(!@fclose($handle)){}

	//select 
	$mail->AddAttachment($root_directory."/test/upload/".$filename);//temparray['filename']) // add attachments

//	$mail->IsHTML(true);
//	$mail->AltBody = "This is the body in plain text for non-HTML mail clients";
}

for($i=0;$i< $adb->num_rows($result2);$i++)
{
        $fileContent = $adb->query_result($result2,$i,"attachmentcontents");
        $filename=$adb->query_result($result2,$i,"name");
        $filesize=$adb->query_result($result2,$i,"attachmentsize");

        if(!@$handle = fopen($root_directory."/test/upload/".$filename,"wb")){}

        //chmod("/home/rajeshkannan/test/".$fileContent,0755);
        if(!@fwrite($handle,base64_decode($fileContent),$filesize)){}
        if(!@fclose($handle)){}

        //select
        $mail->AddAttachment($root_directory."/test/upload/".$filename);//temparray['filename']) // add attachments

//        $mail->IsHTML(true);
//        $mail->AltBody = "This is the body in plain text for non-HTML mail clients";
}
$mail->IsHTML(true);
$mail->AltBody = "This is the body in plain text for non-HTML mail clients";
echo '<table>';
$count = 1;
for($i=0;$i< $noofrows;$i++)
{
	$mail->ClearAddresses();
	if($_REQUEST['mailto'] == 'users')
	{
		$to=$adb->query_result($result,$i,"email1");
		if($to == '')
			$to=$adb->query_result($result,$i,"email2");
		if($to == '')
			$to=$adb->query_result($result,$i,"yahoo_id");
	}
	else
		$to=$adb->query_result($result,$i,"email");

	$mail->AddAddress($to);

	$emailoptout = $adb->query_result($result,$i,"emailoptout");
	if($emailoptout == 1 && $to != '')
	{
		$mail->ClearAddresses();
		$mail->AddAddress("");
		$emailoptout_error = true;
	}

	$j=$i+1;


	if($mail->Send())
	{
		$flag = true;
	        if($flag && $count == 1 && $noofrows >= 1)
		{
			$count = 0;
			if($_REQUEST['mailto'] == 'users')
				echo '<tr><b><h3>'.$mod_strings['MESSAGE_MAIL_HAS_SENT_TO_USERS'].' </h3></b></tr>';
			else
				echo '<tr><b><h3>'.$mod_strings['MESSAGE_MAIL_HAS_SENT_TO_CONTACTS'].' </h3></b></tr>';
		}
                echo '<center><tr align="left"><b><h3>'.$j.' . '.$to.'</h3></b></tr></center>';
	}
	else
	{
		$message = substr($mail->ErrorInfo,0,49);
		$flag = false;
		if($message=='Language string failed to load: connect_host')
		{
			if($i == 0)
				echo "<br><b><h3>".$mod_strings['MESSAGE_CHECK_MAIL_SERVER_NAME']."</b></h3>";
		}
	        elseif( $count == 1 && $noofrows >= 1)
		{
			$count = 0;
			if($_REQUEST['mailto'] == 'users')
				echo '<tr><b><h3>'.$mod_strings['MESSAGE_MAIL_HAS_SENT_TO_USERS'].' </h3></b></tr>';
			else
				echo '<tr><b><h3>'.$mod_strings['MESSAGE_MAIL_HAS_SENT_TO_CONTACTS'].' </h3></b></tr>';
		}
		if($message=='Language string failed to load: recipients_failed')
		{
			if($emailoptout_error == true)
			{
				$ERROR_MESSAGE = $mod_strings['MESSAGE_CONTACT_NOT_WANT_MAIL'];
				$flag = true;
			}
			else
				$ERROR_MESSAGE = $mod_strings['MESSAGE_MAIL_ID_IS_INCORRECT'];

		                echo '<center><tr align="left"><font color="purple"<b><h3>'.$j.' . '.$ERROR_MESSAGE.' </h3></font></b></tr></center>';
		}
	}
}
if($i==0) echo '<br><td align="left"><font color="red"><b><center><h3>'.$mod_strings['MESSAGE_ADD_USER_OR_CONTACT'].'</h3></b></font>';
if($i>1 && $flag)echo "<br><br><B><center><h3>".$mod_strings['MESSAGE_MAIL_SENT_SUCCESSFULLY']." </h3></B>";
echo '</table>';
}
?>
