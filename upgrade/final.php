<?php
session_start();
include('../phpincludes/sitePaths.php');
include("../phpincludes/connection.php");
include("../phpincludes/class.phpmailer.php");
include("../phpincludes/class.smtp.php");
include("../phpincludes/GenericClass.php");
$uid=base64_decode(base64_decode($_POST["udf1"]));
$status=$_POST["status"];
$firstname=$_POST["firstname"];
$amount=$_POST["amount"];
$txnid=$_POST["txnid"];
$posted_hash=$_POST["hash"];
$key=$_POST["key"];
$productinfo=$_POST["productinfo"];
$email=$_POST["email"];

$salt="FQlPatq4";

if(isset($_POST["additionalCharges"])) {
       $additionalCharges=$_POST["additionalCharges"];
        $retHashSeq = $additionalCharges.'|'.$salt.'|'.$status.'|||||||||||'.$email.'|'.$_POST["udf1"].'|'.$firstname.'|'.$productinfo.'|'.$amount.'|'.$txnid.'|'.$key;
        
                  }
	else {	  

        $retHashSeq = $salt.'|'.$status.'||||||||||'.$_POST["udf1"].'|'.$email.'|'.$firstname.'|'.$productinfo.'|'.$amount.'|'.$txnid.'|'.$key;

         }
		 $hash = hash("sha512", $retHashSeq);

       if ($hash != $posted_hash) {
	       echo "Invalid Transaction. Please try again";
		   }
	   else { 

			   	/* add details in db*/	   
				$now=date("Y-m-d H:i:s");
				$ToDate=date("Y-m-d H:i:s", strtotime('+365 days'));
				$Package=base64_decode(base64_decode($productinfo));
				if($_POST['status']=='success')
					$flag='Y';
				else
					$flag='N';
					
				mysql_query("update ec_pricing SET Amount='$amount',FromDate='$now',ToDate='$ToDate',Package='$Package',Payment='$flag',PaymentTransactionId='".$_POST['txnid']."',PayuMoneyId='".$_POST['payuMoneyId']."',PaymentMode='".$_POST['mode']."',PaymentUserEmail='$email',PaymentUserPhone='".$_POST['phone']."',PaymentPayUStatus='".$_POST['unmappedstatus']."',PaymentStatus='".$_POST['status']."',PaymentError='".$_POST['error']."',PaymentErrorMessage='".$_POST['error_Message']."',PaymentDate='$now' where UserId='$uid'"); 
				
				mysql_query("update ec_users SET Package='$Package' where Id='$uid'");

			/* add details in db*/	 
			
		if($_POST['status']=='success')
		{
	/* send invoice*/
				
	$obj=new GenericClass("ec_users as user");
	$arrData=$obj->getDataLimited("*,price.Id,user.Package,user.Email"," LEFT JOIN ec_company_details as comp ON comp.Id = user.CompanyId LEFT JOIN ec_pricing as price on user.Id=price.UserId LEFT JOIN  ec_packages as package on user.Package=package.Id where user.Id=".$uid,false);
		
	
		$InvoiceId='EWR'.sprintf('%04u', $arrData[0]['Id']).date("dmy");
		$Date=date("Y-m-d H:i:s");
		$Name=$arrData[0]['Name'];
		$Address=$arrData[0]['Address'];
		$Package= array_search($arrData[0]['Package'],array('Basic'=>'1','Standard'=>'2','Premium'=>'3'));
		$FinalPrice=$arrData[0]['FinalPrice'];
		$Total=$arrData[0]['FinalPrice'];
		$Price=$arrData[0]['Price'];
		$Discount=$arrData[0]['Discount'];
		$GrandTotal=$arrData[0]['FinalPrice'];
		
				$fp=fopen("../mailer/Invoice.html","r");
			$message= fread($fp,filesize("../mailer/Invoice.html"));
			$message=str_replace('$InvoiceId', $InvoiceId,$message);
			$message=str_replace('$Date', $Date,$message);
			$message=str_replace('$Name', $Name,$message);
			$message=str_replace('$Address', $Address,$message);
			$message=str_replace('$Package', $Package,$message);
			$message=str_replace('$FinalPrice', $FinalPrice,$message);
			$message=str_replace('$Total', $Total,$message);
			$message=str_replace('$Price', $Price,$message);
			$message=str_replace('$Discount', $Discount,$message);
			$message=str_replace('$GrandTotal', $GrandTotal,$message);
			
				$mail = new phpmailer(); 
				$mail->IsSMTP(); 
				$mail->Mailer = "smtp"; 		
				$mail->SMTPAuth = true;     // turn on SMTP authentication
				$mail->WordWrap = 50; 
				$mail->IsHTML(true); 
				$mail->Subject = "Collocation Payment Invoice";	
				$sendTo=$obj->Email;
				$mail->Body = $message;
				//echo $message; exit;	
				$mail->AddAddress($arrData[0]['Email']);	
				$mail->AddBCC("naresh@dimakhconsultants.com");
				$mail->Send();
			   
				
				/* send invoicce*/
	   }
			
			/* Display data */   
				$_SESSION['UserLogin']['Package']=$Package;
      			$_SESSION['status']=$status;
				$_SESSION['txnid']=$txnid;
				$_SESSION['amount']=$amount;
	  			header('Location:'.$site_path.'upgrade/sucess.php');
				exit;
           /* Display data */   
		   }         
		   
		   
?>	