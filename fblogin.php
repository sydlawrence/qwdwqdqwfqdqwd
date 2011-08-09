<?php

include('facebook.php');

$fb = new Facebook();





if ($token = $fb->getAccessToken()) {

  $user = $fb->getUser();
  
  print_r($user);  
  
  
  
  echo "<pre>".print_r($token,true)."</pre>";
}
else {
  header('Location = "'.$fb->getLoginUrl().'"');
}

