<?php
    session_start();
    include(dirname( __FILE__ ).'/phptextClass.php');
    /*create class object*/
    $phptextObj = new phptextClass();	
    /*phptext function to genrate image with text*/
    $phptextObj->phpcaptcha('#162453','#fff',100,50,10,25);	
 ?>