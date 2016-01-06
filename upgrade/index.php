<?php
session_start();

include("../phpincludes/session.php");
include("../phpincludes/sitePaths.php");
$Amount=base64_decode(base64_decode($_GET['UA']));
$Package=$_GET['UP'];
//
$suceesurl=$site_path.'upgrade/final.php';
$failurl=$site_path.'upgrade/final.php';
$cancel=$site_path.'upgrade/cancel.php';
$uid=base64_encode(base64_encode($_SESSION['UserLogin']['Id']));

// Merchant key here as provided by Payu
$MERCHANT_KEY = "HoKG7X";

// Merchant Salt as provided by Payu
$SALT = "FQlPatq4";

// End point - change to https://secure.payu.in for LIVE mode
$PAYU_BASE_URL = "https://test.payu.in";

$action = '';

$posted = array();


if(empty($posted['txnid'])) {
  // Generate random transaction id
  $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
} else {
  $txnid = $posted['txnid'];
}
$hash = '';
// Hash Sequence

$hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";

$posted['key']=$MERCHANT_KEY ;
$posted['txnid']=$txnid;
$posted['firstname']=$_SESSION['UserLogin']['Name'];
$posted['phone']=$_SESSION['UserLogin']['Contact'];
$posted['productinfo']=$Package;
$posted['service_provider']='payu_paisa';
$posted['amount']=$Amount;
$posted['email']=$_SESSION['UserLogin']['Email'];
$posted['surl']=$suceesurl;
$posted['furl']=$failurl;
$posted['curl']=$cancel;
 $posted['udf1']=$uid;
		 
	
		
	$hashVarsSeq = explode('|', $hashSequence);
    $hash_string = '';	
	foreach($hashVarsSeq as $hash_var) {
      $hash_string .= isset($posted[$hash_var]) ? $posted[$hash_var] : '';
      $hash_string .= '|';
    }

    $hash_string .= $SALT;
/*	print_r($hashSequence);
print_r($hash_string); exit; */
    $hash = strtolower(hash('sha512', $hash_string));
    $action = $PAYU_BASE_URL . '/_payment';
	/**/

?>
<html>
  <head>
  <script>
    var hash = '<?php echo $hash ?>';
    function submitPayuForm() {
      if(hash == '') {
        return;
      }
      var payuForm = document.forms.payuForm;
      payuForm.submit();
    }
  </script>
  </head>
  <body onLoad="submitPayuForm()">

<form action="<?php echo $action; ?>" method="post" name="payuForm">
    <input type="hidden" name="key" value="<?php echo $MERCHANT_KEY ?>" />
    <input type="hidden" name="hash" value="<?php echo $hash ?>"/>
    <input type="hidden" name="txnid" value="<?php echo $txnid ?>" />
    <input type="hidden" name="amount" value="<?= $Amount?>" />
    <input type="hidden" name="email" value="<?= $_SESSION['UserLogin']['Email']?>" />
    <input type="hidden" name="surl" value="<?= $suceesurl?>" />
    <input type="hidden" name="furl" value="<?= $failurl?>" />
    <input type="hidden" name="curl" value="<?= $cancel?>" />
    <input type="hidden" name="udf1" value="<?= $uid?>" />
    <input type="hidden" name="udf2" value="" />
    <input type="hidden" name="udf3" value="" />
    <input type="hidden" name="udf4" value="" />
    <input type="hidden" name="udf5" value="" />
    <input type="hidden" name="pg" value="" />
    <input type="hidden" name="service_provider" value="payu_paisa" />
    <input type="hidden" name="productinfo" value="<?= $Package?>" />
    <input type="hidden" name="firstname" value="<?= $_SESSION['UserLogin']['Name']?>" />
    <input type="hidden" name="phone" value="<?= $_SESSION['UserLogin']['Contact'];?>" />
    
    
  </form> 
</body>
</html>
