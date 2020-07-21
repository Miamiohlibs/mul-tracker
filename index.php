<?php
error_reporting(E_ALL);
ini_set('display_errors',true);

session_start();
include('config.php'); // defines $pdo
include('bootstrap.php');

if (isset($_REQUEST['formname'])) {
  HandleForm();
}

if (isset($_SESSION['username'])) {
  LoginBar($_SESSION['username']);
}
else { 
  LoginBar();
}

function LoginBar($user = null) { 
  print '<nav class="navbar bg-light navbar-light">';

  if (is_null($user)) {
    $select = SelectUser();
    
    print '<span class="ml-auto">';
    print '<form class="form-inline">'.PHP_EOL;
    print '<input type="hidden" name="formname" value="login">'.PHP_EOL;
    print '<label for="username" class="mr-1">Login as:</label>';
    print $select;
    print '<input type="submit" class="btn btn-primary btn-sm py-0">';
    print '</form>'.PHP_EOL;
    print '</span>';
  }

  else { 
  print '<span class="ml-auto">Logging information for: <span class="logging-name">'.$_SESSION['display_name'].'</span> <a href="./?formname=logout" class="btn btn-sm btn-danger py-0">Logout</a></span>';
  }

  print '</nav>';
}

function SelectUser() {
  $opts = '';
  global $pdo;
  $q = "SELECT * FROM users";
  $stmt = $pdo->query($q);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach ($rows as $row) {
    $opts .= '<option value="'.$row['username'].'::'.$row['name'].'">'.$row['name'].'</option>'.PHP_EOL;
  }
  return '<select name="username">'.$opts.'</select>'.PHP_EOL;
}


function HandleForm() {
  //  print_r ($_REQUEST);
  if ($_REQUEST['formname'] == 'login') {
    list ($_SESSION['username'], $_SESSION['display_name']) = preg_split ('/\:\:/', $_REQUEST['username']);
  }

  if ($_REQUEST['formname'] == 'logout') {
    session_destroy();
    session_start();
  }

}
?>