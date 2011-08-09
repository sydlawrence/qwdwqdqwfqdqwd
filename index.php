<?php
session_start();
if (isset($_SESSION['fb_user'])) {
  header('Location: check.php');}
else {
  header('Location: fblogin.php');}

}
