<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the 
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * $Header: /cvsroot/vtigercrm/vtiger_crm/modules/Quotes/Save.php,v 1.5 2005/07/13 20:02:12 crouchingtiger Exp $
 * Description:  Saves an Account record and then redirects the browser to the 
 * defined return URL.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('modules/Quotes/Quote.php');
require_once('include/logging.php');
//require_once('database/DatabaseConnection.php');
require_once('include/database/PearDatabase.php');

$local_log =& LoggerManager::getLogger('index');

global $vtlog;

$vtlog->logthis("Inside Quote Save",'debug');
if ($_REQUEST['return_module']=="Potentials")
	{
$_REQUEST['return_module']="Quotes";
$return_id_old = $_REQUEST['return_id'];
$_REQUEST['return_id'] = $return_id_old+1;
	$vtlog->logthis("Return Module ".$_REQUEST['return_module'],'debug');
	$vtlog->logthis("Return ID ".$_REQUEST['return_id'],'debug');
	}
$focus = new Quote();
if(isset($_REQUEST['record']))
{
	$focus->id = $_REQUEST['record'];
	$vtlog->logthis("Quote ID ".$focus->id,'debug');
}
if(isset($_REQUEST['mode']))
{
	$focus->mode = $_REQUEST['mode'];
	$vtlog->logthis("Mode is  ".$focus->mode,'debug');
}



//$focus->retrieve($_REQUEST['record']);

foreach($focus->column_fields as $fieldname => $val)
{
	if(isset($_REQUEST[$fieldname]))
	{
		$value = $_REQUEST[$fieldname];
		//echo '<BR>';
		//echo $fieldname."         ".$value;
		//echo '<BR>';
		$focus->column_fields[$fieldname] = $value;
	}
		
}
$vtlog->logthis("The Field Value Array -----> ".$focus->column_fields ,'debug');
$vtlog->logthis("quoteid is ".$focus->column_fields['quoteid'],'info'); 
$vtlog->logthis("quoteid is ".$focus->column_fields['return_id'],'info'); 
$focus->save("Quotes");
if($focus->mode == 'edit')
{
	
	$vtlog->logthis("Deleting from quotesproductrel table ",'debug');
	$query1 = "delete from quotesproductrel where quoteid=".$focus->id;
	//echo $query1;
	$adb->query($query1);

}
//Printing the total Number of rows
$tot_no_prod = $_REQUEST['totalProductCount'];
$vtlog->logthis("The total Product Count is  ".$tot_no_prod,'debug');

for($i=1; $i<=$tot_no_prod; $i++)
{
	$product_id_var = 'hdnProductId'.$i;
	$status_var = 'hdnRowStatus'.$i;
	$qty_var = 'txtQty'.$i;
	$list_price_var = 'txtListPrice'.$i;
	$discount_var = 'txtDiscount'.$i;
		
	$prod_id = $_REQUEST[$product_id_var];
	$prod_status = $_REQUEST[$status_var];
	$qty = $_REQUEST[$qty_var];
	$listprice = $_REQUEST[$list_price_var];
	$discount2 = $_REQUEST[$discount_var];
        $discount = ($discount2/100);
		
	if($prod_status != 'D')
	{
		
		$query ="insert into quotesproductrel values(".$focus->id.",".$prod_id.",".$qty.",".$listprice.",".$discount.")";
		//echo $query;
		$adb->query($query);
		//Checking the re-order level and sending mail	
//		updateStk($prod_id,$qty); 	
	}	
}


/*
echo 'rowid : '.$_REQUEST[$product_id_var];
echo '<BR>';
echo 'status : '.$_REQUEST['hdnRowStatus1'];
echo '<BR>';
echo 'qty : '.$_REQUEST['txtQty1'];
echo '<BR>';
echo 'LP: '.$_REQUEST['txtListPrice1'];
*/
$return_id = $focus->id;
$return_module = "Quotes";
$return_action = "DetailView";

/*
if(isset($_REQUEST['return_module']) && $_REQUEST['return_module'] != "") $return_module = $_REQUEST['return_module'];
else $return_module = "Quotes";
if(isset($_REQUEST['return_action']) && $_REQUEST['return_action'] != "") $return_action = $_REQUEST['return_action'];
else $return_action = "DetailView";
if(isset($_REQUEST['return_id']) && $_REQUEST['return_id'] != "") $return_id = $_REQUEST['return_id'];
*/

$local_log->debug("Saved record with id of ".$return_id);

function updateStk($product_id,$qty)
{
	global $vtlog;
	global $adb;
	global $current_user;
	$vtlog->logthis("Inside Quote updateStk function.",'debug');
	$vtlog->logthis("Product Id is".$product_id,'debug');
	$vtlog->logthis("Qty is".$qty,'debug');

	$prod_name = getProductName($product_id);
	$qtyinstk= getPrdQtyInStck($product_id);
	$vtlog->logthis("Prd Qty in Stock ".$qtyinstk,'debug');
	$upd_qty = $qtyinstk-$qty;
	$vtlog->logthis("Prd upd_qty ".$upd_qty,'debug');
	//Check for reorder level and send mail
	$reorderlevel = getPrdReOrderLevel($product_id);
	$vtlog->logthis("Prd reorder level ".$reorderlevel,'debug');
	if($upd_qty < $reorderlevel)
	{
		
		//send mail to the handler
		$vtlog->logthis("Sending mail to handler ",'debug');
		$handler=getPrdHandler($product_id);
		$handler_name = getUserName($handler);
		$vtlog->logthis("Handler Name is ".$handler_name,'debug');
		$to_address= getUserEmail($handler);
		$vtlog->logthis("Handler Email is ".$to_address,'debug');
		//Get the email details from database;
		$query = "select * from inventorynotification where notificationname='QuoteNotification'";
		$result = $adb->query($query);

		$subject = $adb->query_result($result,0,'notificationsubject');
		$body = $adb->query_result($result,0,'notificationbody');

		$subject = str_replace('{PRODUCTNAME}',$prod_name,$subject);
		$body = str_replace('{HANDLER}',$handler_name,$body);	
		$body = str_replace('{PRODUCTNAME}',$prod_name,$body);	
		$body = str_replace('{CURRENTSTOCK}',$qtyinstk,$body);	
		$body = str_replace('{QUOTEQUANTITY}',$qty,$body);	
		$body = str_replace('{CURRENTUSER}',$current_user->user_name,$body);	

		SendMailToCustomer($to_address,$current_user->id,$subject,$body);
		
	}
}


function getPrdQtyInStck($product_id)
{
	global $adb;
	$query1 = "select qtyinstock from products where productid=".$product_id;
	$result=$adb->query($query1);
	$qtyinstck= $adb->query_result($result,0,"qtyinstock");
	return $qtyinstck;
	
	
}
function getPrdReOrderLevel($product_id)
{
	global $adb;
	$query1 = "select reorderlevel from products where productid=".$product_id;
	$result=$adb->query($query1);
	$reorderlevel= $adb->query_result($result,0,"reorderlevel");
	return $reorderlevel;
	
}
function getPrdHandler($product_id)
{
	global $adb;
	$query1 = "select handler from products where productid=".$product_id;
	$result=$adb->query($query1);
	$handler= $adb->query_result($result,0,"handler");
	return $handler;
	
}

function SendMailToCustomer($to,$current_user_id,$subject,$contents)
{
	global $vtlog;
	$vtlog->logthis("Inside SendMailToCustomer function.",'debug');		
	require_once("modules/Emails/class.phpmailer.php");

	$mail = new PHPMailer();
	
	$mail->Subject = $subject;
	$mail->Body    = nl2br($contents);	
	$mail->IsSMTP();

	if($current_user_id != '')
	{
		global $adb;
		$sql = "select * from users where id= ".$current_user_id;
		$result = $adb->query($sql);
		$from = $adb->query_result($result,0,'email1');
		$initialfrom = $adb->query_result($result,0,'user_name');
	}
	if($mail_server=='')
        {
		global $adb;
                $mailserverresult=$adb->query("select * from systems where server_type='email'");
                $mail_server=$adb->query_result($mailserverresult,0,'server');
                $_REQUEST['server']=$mail_server;
        }
	$mail->Host = $mail_server;
        $mail->SMTPAuth = true;
        $mail->Username = $mail_server_username;
        $mail->Password = $mail_server_password;
	$mail->From = $from;
	$mail->FromName = $initialfrom;

	$mail->AddAddress($to);
	$mail->AddReplyTo($from);
	$mail->WordWrap = 50;

	$mail->IsHTML(true);

	$mail->AltBody = "This is the body in plain text for non-HTML mail clients";

	if(!$mail->Send())
	{
		$errormsg = "Mail Could not be sent...";	
	}
}

header("Location: index.php?action=$return_action&module=$return_module&record=$return_id");
?>
