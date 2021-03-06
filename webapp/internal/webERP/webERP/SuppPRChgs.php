<?php


/* $Revision: 1.7 $ */

/*The supplier transaction uses the SuppTrans class to hold the information about the invoice
the SuppTrans class contains an array of ProdRuns objects - containing details of all production run charges for invoicing
Production Run charges are posted to the debit of GRN suspense if the Creditors - GL link is on
This is cleared against credits to the GRN suspense when the products are received into stock and any
purchase price variance calculated when the production run is closed */

include('includes/DefineSuppTransClass.php');

$PageSecurity = 5;

/* Session started here for password checking and authorisation level check */
include('includes/session.inc');

$title = _('Production Run Charges or Credits');

include('includes/header.inc');

if (!isset($_SESSION['SuppTrans'])){
	prnMsg(_('Production Run charges or credits are entered against supplier invoices or credit notes respectively') . '. ' . _('To enter supplier transactions the supplier must first be selected from the supplier selection screen') . ', ' . _('then the link to enter a supplier invoice or credit note must be clicked on'),'info');
	echo "<BR><A HREF='$rootpath/SelectSupplier.php?" . SID ."'>" . _('Select A Supplier') . '</A>';
	exit;
	/*It all stops here if there aint no supplier selected and invoice/credit initiated ie $_SESSION['SuppTrans'] started off*/
}

/*If the user hit the Add to transaction button then process this first before showing  all GL codes on the invoice otherwise it wouldnt show the latest addition*/

if (isset($_POST['AddProdRunsChgToInvoice'])){

	$InputError = False;
	if ($_POST['ProdRunsRef'] == ""){
		$_POST['ProdRunsRef'] = $_POST['ProdRunsSelection'];
	}
	if (!is_numeric($_POST['ProdRunsRef'])){
		prnMsg(_('The production run reference must be numeric') . '. ' . _('This production run charge cannot be added to the invoice'),'error');
		$InputError = True;
	}

	if (!is_numeric($_POST['Amount'])){
		prnMsg(_('The amount entered is not numeric') . '. ' . _('This production run charge cannot be added to the invoice'),'error');
		$InputError = True;
	}

	if ($InputError == False){
		$_SESSION['SuppTrans']->Add_ProdRuns_To_Trans($_POST['ProdRunsRef'], $_POST['Amount'], $_POST['InvoiceType']);
		unset($_POST['ProdRunsRef']);
		unset($_POST['Amount']);
		unset($_POST['InvoiceType']);
	}
}

if (isset($_GET['Delete'])){

	$_SESSION['SuppTrans']->Remove_ProdRuns_From_Trans($_GET['Delete']);
}

/*Show all the selected ProdRunsRefs so far from the SESSION['SuppInv']->ProdRuns array */
if ($_SESSION['SuppTrans']->InvoiceOrCredit=='Invoice'){
	echo '<CENTER><FONT SIZE=4 COLOR=BLUE>' . _('Production Run charges on Invoice') . ' ';
} else {
	echo '<CENTER><FONT SIZE=4 COLOR=BLUE>' . _('Production Run credits on Credit Note') . ' ';
}

echo $_SESSION['SuppTrans']->SuppReference . ' ' ._('From') . ' ' . $_SESSION['SuppTrans']->SupplierName;

echo "<TABLE CELLPADDING=2>";
$TableHeader = "<TR><TD CLASS='tableheader'>" . _('Production Run') . "</TD>
		<TD CLASS='tableheader'>" . _('Amount') . "</TD><TD CLASS='tableheader'>" . _('Type') . "</TD></TR>";
echo $TableHeader;

$TotalProdRunValue = 0;

foreach ($_SESSION['SuppTrans']->ProdRuns as $EnteredProdRunsRef){

	echo '<TR><TD>' . $EnteredProdRunsRef->ProdRunsRef . '</TD>
		<TD ALIGN=RIGHT>' . number_format($EnteredProdRunsRef->Amount,2) . "</TD><TD>" . $EnteredProdRunsRef->InvoiceType . "</TD>
		<TD><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "&Delete=" . $EnteredProdRunsRef->Counter . "'>" . _('Delete') . '</A></TD></TR>';

	$TotalProdRunValue = $TotalProdRunValue + $EnteredProdRunsRef->Amount;

	$i++;
	if ($i>15){
		$i = 0;
		echo $TableHeader;
	}
}

echo '<TR>
	<TD COLSPAN=2 ALIGN=RIGHT><FONT SIZE=4 COLOR=BLUE>' . _('Total') . ':</FONT></TD>
	<TD ALIGN=RIGHT><FONT SIZE=4 COLOR=BLUE><U>' . number_format($TotalProdRunValue,2) . '</U></FONT></TD>
</TR>
</TABLE>';

if ($_SESSION['SuppTrans']->InvoiceOrCredit == 'Invoice'){
	echo "<BR><A HREF='$rootpath/SupplierInvoice.php?" . SID . "'>" . _('Back to Invoice Entry') . '</A><HR>';
} else {
	echo "<BR><A HREF='$rootpath/SupplierCredit.php?" . SID . "'>" . _('Back to Credit Note Entry') . '</A><HR>';
}

/*Set up a form to allow input of new Production Run charges */
echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

echo '<TABLE>';
echo '<TR><TD>' . _('Production Run Costing Reference') . ":</TD>
	<TD><INPUT TYPE='Text' NAME='ProdRunsRef' SIZE=12 MAXLENGTH=11 VALUE=" .  $_POST['ProdRunsRef'] . '></TD></TR>';
echo '<TR><TD>' . _('Production Run Costing Selection') . ':<BR><FONT SIZE=1>' . _('If you know the code enter it above') . '<BR>' . _('otherwise select the production run from the list') . "</FONT></TD><TD><SELECT NAME='ProdRunsSelection'>";

$sql = "SELECT prref, 
		other, 
		eta
	FROM productionruncosting  
	WHERE closed=0";

$result = DB_query($sql, $db);

while ($myrow = DB_fetch_array($result)) {
	if ($myrow['prref']==$_POST['ProdRunsSelection']) {
		echo '<OPTION SELECTED VALUE=';
	} else {
		echo '<OPTION VALUE=';
	}
	echo $myrow['prref'] . '>' . $myrow['prref'] . ' - ' . $myrow['other'] . ' ' . _('ETA') . ' ' . ConvertSQLDate($myrow['eta']) . ' ' . _('from') . ' ' . $myrow['suppname'];
}

echo '</SELECT>';
//echo $sql;
echo '</TD></TR>';

echo '<TR><TD>' . _('Amount') . ":</TD>
	<TD><INPUT TYPE='Text' NAME='Amount' SIZE=12 MAXLENGTH=11 VALUE=" .  $_POST['Amount'] . '></TD></TR>';
	
	


echo "<TR><TD>Invoice Type:  </TD><TD><SELECT NAME='InvoiceType'>";
	if (!isset($_POST['InvoiceType']) || ($_POST['InvoiceType']=="")){
		echo '<option value="" selected>-- none --</option><option value="Brokerage" >Brokerage fees</option><option value="Fair Trade Premium" >Fair Trade Premium</option><option value="Shipping" >Shipment Fees</option><option value="Production Run Fees" >Production Run Fees</option>';
	}
	
	elseif ($_POST['InvoiceType']=="Brokerage"){
		echo '<option value="">-- none --</option><option value="Brokerage" selected>Brokerage fees</option><option value="Fair Trade Premium" >Fair Trade Premium</option><option value="Shipping" >Shipment Fees</option><option value="Production Run Fees" >Production Run Fees</option>';
	}
	
	elseif ($_POST['InvoiceType']=="Fair Trade Premium"){
		echo '<option value="">-- none --</option><option value="Brokerage">Brokerage fees</option><option value="Fair Trade Premium" selected>Fair Trade Premium</option><option value="Shipping" >Shipment Fees</option><option value="Production Run Fees" >Production Run Fees</option>';
	}
	
	elseif ($_POST['InvoiceType']=="Shipping"){
		echo '<option value="">-- none --</option><option value="Brokerage">Brokerage fees</option><option value="Fair Trade Premium">Fair Trade Premium</option><option value="Shipping" selected>Shipment Fees</option><option value="Production Run Fees" >Production Run Fees</option>';
	}
	
	elseif ($_POST['InvoiceType']=="Production Run Fees"){
		echo '<option value="">-- none --</option><option value="Brokerage">Brokerage fees</option><option value="Fair Trade Premium">Fair Trade Premium</option><option value="Shipping">Shipment Fees</option><option value="Production Run Fees" selected>Production Run Fees</option>';
	}

	else {
		echo '<option value="">-- none --</option><option value="Brokerage" >Brokerage fees</option><option value="Fair Trade Premium" >Fair Trade Premium</option><option value="Shipping" >Shipment Fees</option><option value="Production Run Fees" >Production Run Fees</option>';
	}
	
	

		echo '</select></TD></TR>';

	
	
	
echo '</TABLE>';
	
echo "<INPUT TYPE='Submit' NAME='AddProdRunsChgToInvoice' VALUE='" . _('Enter Production Run Charge') . "'>";

echo '</FORM>';
include('includes/footer.inc');
?>
