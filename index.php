<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors',true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUL Contact Tracker</title>
<?php
include('config.php'); // defines $pdo
include('bootstrap.php');
include('ui_scripts.php');
?>
<link rel="stylesheet" href="styles.css" />
</head>
<body>

<?php
/*** Handle Form Submit ***/

if (isset($_REQUEST['formname'])) {
  $alert = HandleForm();
}

/*** NavBar ***/

if (isset($_SESSION['username'])) {
  LoginBar($_SESSION['username']);
}
else { 
  LoginBar();
}

/*** Main Body ***/

if (! isset($_SESSION['username'])) {
  print '<div class="container">'.PHP_EOL;
  print 'Login (above) to record comings and goings';
  print '</div>';
}
else { 
  if (isset($alert)) { print '<div class="container">'.$alert.'</div>'; }
  DisplayMain();
}
?>
<footer class="footer bg-light">
<div class="container">
Miami University Libraries - Contact Tracker
</div>
</footer>

</body>
</html>
