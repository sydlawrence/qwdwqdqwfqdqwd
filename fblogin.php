<?php

include('facebook.php');

$fb = new Facebook();

if (isset($_GET['code'])) {
  $token = $fb->getAccessToken();
  
  $url = "https://graph.facebook.com/me?access_token=".$token;

  $data = json_decode(file_get_contents($url));  
  
//  echo "<pre>User: ".print_r($data,true)."</pre>";
//  echo "<pre>Access Token: ".print_r($token,true)."</pre>";


  $_SESSION['fb_user'] = $data;
  $_SESSION['fb_access_token'] = $token;
  
  
  header('Location: check.php');
}
else {

  header('Location: '.$fb->getLoginUrl());
}

