<?php

include('facebook.php');

$fb = new Facebook();





if (isset($_GET['code'])) {
  $token = $fb->getAccessToken();
  $user = $fb->getUser();  
  
  echo "<pre>User: ".print_r($user,true)."</pre>";
  
  
  echo "<pre>Token: ".print_r($token,true)."</pre>";
}
else {


  header('Location: '.$fb->getLoginUrl());
}

