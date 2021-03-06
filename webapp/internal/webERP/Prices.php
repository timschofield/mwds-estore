<?php
/* $Revision: 1.7 $ */

$PageSecurity = 2;

include('includes/session.inc');

$title = _('Item Prices');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

//initialise no input errors assumed initially before we test
$InputError = 0;


if (isset($_GET['Item'])){
	$Item = $_GET['Item'];
}elseif (isset($_POST['Item'])){
	$Item = $_POST['Item'];
}

if (!isset($_POST['TypeAbbrev']) OR $_POST['TypeAbbrev']==""){
	$_POST['TypeAbbrev'] = $_SESSION['DefaultPriceList'];
}

if (!isset($_POST['CurrAbrev'])){
	$_POST['CurrAbrev'] = $_SESSION['CompanyRecord']['currencydefault'];
}

$result = DB_query("SELECT stockmaster.description, stockmaster.mbflag FROM stockmaster WHERE stockmaster.stockid='$Item'",$db);
$myrow = DB_fetch_row($result);

if (DB_num_rows($result)==0){
	prnMsg( _('The part code entered does not exist in the database') . '. ' . _('Only valid parts can have prices entered against them'),'error');
	$InputError=1;
}


if (!isset($Item)){
	echo '<P>';
	prnMsg (_('An item must first be selected before this page is called') . '. ' . _('The product selection page should call this page with a valid product code'),'error');
	include('includes/footer.inc');
	exit;
}

echo '<BR><FONT COLOR=BLUE SIZE=3><B>' . $Item . ' - ' . $myrow[0] . '</B></FONT> ';

echo '<FORM METHOD="post" action=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';
echo _('Pricing for part') . ':<INPUT TYPE=text NAME="Item" MAXSIZEe=22 VALUE="' . $Item . '" maxlength=20><INPUT TYPE=SUBMIT NAME=NewPart Value="' . _('Review Prices') . '">';
echo '<HR>';

if ($myrow[1]=="K"){
	prnMsg(_('The part selected is a kit set item') .', ' . _('these items explode into their components when selected on an order') . ', ' . _('prices must be set up for the components and no price can be set for the whole kit'),'error');
	exit;
}

if (isset($_POST['submit'])) {

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (!is_double((double) trim($_POST['Price'])) OR $_POST['Price']=="") {
		$InputError = 1;
		$msg = _('The price entered must be numeric');
	}

	if (isset($_POST['OldTypeAbbrev']) AND isset($_POST['OldCurrAbrev']) AND strlen($Item)>1 AND $InputError !=1) {

		//editing an existing price
		$sql = "UPDATE prices SET
				typeabbrev='" . $_POST['TypeAbbrev'] . "',
				currabrev='" . $_POST['CurrAbrev'] . "',
				price=" . $_POST['Price'] . "
			WHERE prices.stockid='$Item'
			AND prices.typeabbrev='" . $_POST['OldTypeAbbrev'] . "'
			AND prices.currabrev='" . $_POST['OldCurrAbrev'] . "'
			AND prices.debtorno=''";


		$msg =  _('This price has been updated') . '.';
	} elseif ($InputError !=1) {

	/*Selected price is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new price form */
		$sql1="select product_id,product_publish FROM jos_vm_product where product_sku='$Item' ";
			$result= DB_query($sql1,$db); 
                   $myrow1=DB_fetch_array($result);
                  $productid=$myrow1['product_id'];
       			$product_pub=$myrow1['product_publish'];
			            
			$timestamp=time();
        
			$sqlvm1 ="UPDATE jos_vm_product set
                        product_publish='" . $_POST['pub'] ."'  where product_sku='$Item'";
			 $result =DB_query($sqlvm1,$db);
 
          		 $sqlvm ="INSERT INTO jos_vm_product_price (product_id,
						   product_price,
						   product_currency,
						   product_price_vdate,
						product_price_edate,	
						shopper_group_id,
						cdate,
						mdate,
						price_quantity_start,
						price_quantity_end)
					  	VALUES('". $productid ."',
					        '". $_POST['Price'] ."',
						'" . $_POST['CurrAbrev'] . "',
						'0','0','5',
                                                 '". $timestamp ."',
						'". $timestamp ."','0','0')";
									
                                            $ErrMsg =  _('The item  Price could not be added because');
				$DbgMsg = _('The SQL that was used to add the item failed was');
				$result = DB_query($sqlvm,$db, $ErrMsg, $DbgMsg);



		$sql = "INSERT INTO prices (stockid,
						typeabbrev,
						currabrev,
						debtorno,
						price)
				VALUES ('$Item',
					'" . $_POST['TypeAbbrev'] . "',
					'" . $_POST['CurrAbrev'] . "',
					'',
					" . $_POST['Price'] . ")";
						$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);
		$msg =  _('The new price has been added') . '.';

	//VTiger priceing///
	  $NEWSQL="UPDATE stockmaster SET unit_price=   '". $_POST['Price'] ."' WHERE stockid=$productid";
		$result = DB_query($NEWSQL,$db, $ErrMsg, $DbgMsg);
	
	}
	//run the SQL from either of the above possibilites only if there were no input errors
	if ($InputError !=1){
		$result = DB_query($sql,$db,'','',false,false);
		
		if ($_POST['CurrAbrev'] == 'CDN')
		
			{
			$sql1 = "UPDATE stockmaster SET unit_price=" . $_POST['Price'] . "
			WHERE stockmaster.stockid='$Item' ";
			$ErrMsg =  _('The stockmaster unit price') . ' ' . $myrow[0] .  ' ' . _('could not be added because');
			$DbgMsg = _('Could not add CRM unit price') . ' ' . _('The SQL that was used to add the price to stockmaster');
					
			$CRMResult = DB_query($sql1,$db,$ErrMsg,$DbgMsg);
			
			}
		
		if (DB_error_no($db)!=0){
			If ($msg== _('This price has been updated')){
				$msg = _('The price could not be updated because') . ' - ' . DB_error_msg($db);
			} else {
				$msg = _('The price could not be added because') . ' - ' . DB_error_msg($db);
			}
			if ($debug==1){
				prnMsg(_('The SQL that caused the problem was') . ':<BR>' . $sql,'error');
			}
		} else {
			unset($_POST['Price']);
		}
	}
	prnMsg($msg);




} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
		// Functions For Vituremart prices Delete J  Moncrieff.
	$sql1="select product_id FROM jos_vm_product where product_sku='$Item' ";
			$result= DB_query($sql1,$db); 
                   $myrow1=DB_fetch_array($result);
                  $productid=$myrow1['product_id'];
     $sql = "DELETE FROM jos_vm_product_price WHERE jos_vm_product_price.product_id='". $productid ."' 
		AND  jos_vm_product_price.product_currency='". $_GET['CurrAbrev'] ."'";
		$result = DB_query($sql,$db);


	$sql="DELETE FROM prices
		WHERE prices.stockid = '". $Item ."'
		AND prices.typeabbrev='". $_GET['TypeAbbrev'] ."'
		AND prices.currabrev ='". $_GET['CurrAbrev'] ."'
		AND prices.debtorno=''";

	$result = DB_query($sql,$db);
	prnMsg( _('The selected price has been deleted') . '!','success');

}

//Always do this stuff
if ($InputError ==0){
$sql = "SELECT currencies.currency,
	        	salestypes.sales_type,
			prices.price,
			prices.stockid,
			prices.typeabbrev,
			prices.currabrev
		FROM prices,
			salestypes,
			currencies
		WHERE prices.currabrev=currencies.currabrev
		AND prices.typeabbrev = salestypes.typeabbrev
		AND prices.stockid='$Item'
		AND prices.debtorno=''
		ORDER BY prices.currabrev,
			prices.typeabbrev";

	$result = DB_query($sql,$db);
	echo '<center>';
	echo '<table>';
	echo '<tr><th>' . _('Currency') .
	     '</th><th>' . _('Sales Type') .
			 '</th><th>' . _('Price') .
			 '</th></tr>';

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		/*Only allow access to modify prices if securiy token 5 is allowed */
		if (in_array(5,$_SESSION['AllowedPageSecurityTokens'])) {

			printf("<td>%s</td>
			        <td>%s</td>
				<td align=right>%0.2f</td>
				<td><a href='%s?%s&Item=%s&TypeAbbrev=%s&CurrAbrev=%s&Price=%s&Edit=1'>" . _('Edit') . "</td>
				<td><a href='%s?%s&Item=%s&TypeAbbrev=%s&CurrAbrev=%s&delete=yes' onclick=\"return confirm('" . _('Are you sure you wish to delete this price?') . "');\">" . _('Delete') . '</td></tr>',
				$myrow['currency'],
				$myrow['sales_type'],
				$myrow['price'],
				$_SERVER['PHP_SELF'],
				SID,
				$myrow['stockid'],
				$myrow['typeabbrev'],
				$myrow['currabrev'],
				$myrow['price'],
				$_SERVER['PHP_SELF'],
				SID,
				$myrow['stockid'],
				$myrow['typeabbrev'],
				$myrow['currabrev']);
		} else {
			printf("<td>%s</td>
			        <td>%s</td>
				<td align=right>%0.2f</td></tr>",
				$myrow['currency'],
				$myrow['sales_type'],
				$myrow['price']);
		}

	}
	//END WHILE LIST LOOP
		echo '</table><p><hr>';

	if (DB_num_rows($result) == 0) {
		prnMsg(_('There are no prices set up for this part'),'warn');
	}

	if (isset($_GET['Edit'])){
		echo '<input type=hidden name="OldTypeAbbrev" VALUE="' . $_GET['TypeAbbrev'] .'">';
		echo '<input type=hidden name="OldCurrAbrev" VALUE="' . $_GET['CurrAbrev'] . '">';
		$_POST['CurrAbrev'] = $_GET['CurrAbrev'];
		$_POST['TypeAbbrev'] = $_GET['TypeAbbrev'];
		$_POST['Price'] = $_GET['Price'];
	}

	$SQL = "SELECT currabrev, currency FROM currencies";
	$result = DB_query($SQL,$db);

	echo '<table><tr><td>' . _('Currency') . ':</td><td><select name="CurrAbrev">';
	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['currabrev']==$_POST['CurrAbrev']) {
			echo '<option selected VALUE="';
		} else {
			echo '<option VALUE="';
		}
		echo $myrow['currabrev'] . '">' . $myrow['currency'];
	} //end while loop

	DB_free_result($result);

	echo '</select>	</td></tr><tr><td>' . _('Sales Type Price List') . ':</td><td><select name="TypeAbbrev">';

	$SQL = "SELECT typeabbrev, sales_type FROM salestypes";
	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['typeabbrev']==$_POST['TypeAbbrev']) {
			echo '<option selected VALUE="';
		} else {
			echo '<option VALUE="';
		}
		echo $myrow['typeabbrev'] . '">' . $myrow['sales_type'];

	} //end while loop

	DB_free_result($result);
	?>

	</select>
	</td></tr>

	<tr><td><?php echo _('Price'); ?>:</td>
	<td>
	<input type="Text" class=number name="Price" size=12 maxlength=11 value=
	<?php if(isset($_POST['Price'])) {echo $_POST['Price'];}?>>
	
	</td></tr>
	<TR> <TD> Online Shoping Cart</TD>
        <TD>
	
	<SELECT NAME='pub'>
      

	<OPTION value='n'> NO</OPTION>
      <?php
		//Jomvm check 
		 $sql1="select product_publish FROM jos_vm_product where product_sku='$Item' ";
                        $result= DB_query($sql1,$db);
                 	  $myrow1=DB_fetch_array($result);
                        $product_pub=$myrow1['product_publish'];
 
		if($product_pub=='y'){

			echo '<OPTION value="Y" selected >YES </OPTION>';
		}else{ 
			 echo '<OPTION value="Y" >YES </OPTION>';
		}

		
	?>
	
        </SELECT>
        </TD></TR>

	</table>
	<div class="centre">
	<input type="Submit" name="submit" value="<?php echo _('Enter') . '/' . _('Amend Price'); ?>">
	</div>
	
<?php
 }
echo '</form>';
include('includes/footer.inc');
?>
