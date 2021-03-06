<?php
/* $Revision: 1.4 $ */
include('includes/DefineSerialItems.php');
include('includes/DefineStockAdjustment.php');

$PageSecurity = 11;
include('includes/session.inc');

$title = _('Adjusting Controlled Items');
/* Session started in header.inc for password checking and authorisation level check */
include('includes/header.inc');


if (!isset($_SESSION['Adjustment'])) {
	/* This page can only be called when a stock adjustment is pending */
	echo '<CENTER><A HREF="' . $rootpath . '/StockAdjustment.php?' . SID . '&NewAdjustment=Yes">'. _('Enter A Stock Adjustment'). '</A><br>';
	prnMsg( _('This page can only be opened if a stock adjustment for a controlled item has been entered').'<BR>','error');
	echo '</CENTER>';
	include('includes/footer.inc');
	exit;
}
if (isset($_SESSION['Adjustment'])){
	if ($_GET['AdjType']!=''){
		$_SESSION['Adjustment']->AdjustmentType = $_GET['AdjType'];
	}
}

/*Save some typing by referring to the line item class object in short form */
$LineItem = &$_SESSION['Adjustment'];

//Make sure this item is really controlled
if ( $LineItem->Controlled != 1 ){
	echo '<CENTER><A HREF="' . $rootpath . '/StockAdjustment.php?' . SID . '&NewAdjustment=Yes">'._('Enter A Stock Adjustment').'</A></CENTER>';
	prnMsg('<BR>'. _('Notice') . ' - ' . _('The adjusted item must be defined as controlled to require input of the batch numbers or serial numbers being adjusted'),'error');
	include('includes/footer.inc');
	exit;
}

/*****  get the page going now... *****/
echo '<CENTER>';

echo '<br><a href="'.$rootpath.'/StockAdjustments.php?'  . SID .'">' . _('Back to Adjustment Screen') . '</A>';

echo '<br><FONT SIZE=2><B>'. _('Adjustment of controlled item').' ' . $LineItem->StockID  . ' - ' . $LineItem->ItemDescription ;

/** vars needed by InputSerialItem : **/
$LocationOut = $_SESSION['Adjustment']->StockLocation;
$StockID = $LineItem->StockID;
if ($LineItem->AdjustmentType == 'ADD'){
	echo '<BR>'. _('Adding Items').'...';
	$ItemMustExist = false;
	$InOutModifier = 1;
	$ShowExisting = false;
} elseif  ($LineItem->AdjustmentType == 'REMOVE'){
	echo '<BR>'._('Removing Items').'...';
	$ItemMustExist = true;
	$InOutModifier = -1;
	$ShowExisting = true;
} else {
	prnMsg( _('The Adjustment Type needs to be set') . '. ' . _('Please try again'). '.' );
	include('includes/footer.inc');
	exit;
}
echo '</B></FONT>';
include ('includes/InputSerialItems.php');

/*TotalQuantity set inside this include file from the sum of the bundles
of the item selected for adjusting */
$_SESSION['Adjustment']->Quantity = $TotalQuantity;

/*Also a multi select box for adding bundles to the adjustment without keying, showing only when keying */
include('includes/footer.inc');
?>
