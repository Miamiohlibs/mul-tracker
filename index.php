<?php
error_reporting(E_ALL);
ini_set('display_errors',true);

session_start();
include('config.php'); // defines $pdo
include('bootstrap.php');

/*** Handle Form Submit ***/

if (isset($_REQUEST['formname'])) {
  HandleForm();
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
  DisplayMain();
}
/*** Scripts ***/

function LoginBar($user = null) { 
  print '<nav class="navbar bg-light navbar-light">';

  if (is_null($user)) {
    $select = SelectUser();
    
    print '<span class="ml-auto">';
    print '<form method="POST" class="form-inline">'.PHP_EOL;
    print '<input type="hidden" name="formname" value="login">'.PHP_EOL;
    print '<label for="username" class="mr-1">Login as:</label>';
    print $select;
    print '<input type="submit" class="btn btn-primary btn-sm py-0">';
    print '</form>'.PHP_EOL;
    print '</span>';
  }

  else { 
    print '<span class="ml-auto"><form class="form-inline"><input type="hidden" name="formname" value="logout">';
    print 'Logging information for: <span class="logging-name">'.$_SESSION['display_name'];
    print '<input type="submit" class="btn btn-sm btn-danger py-0" value="Logout" / ></form></span>';
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

  if ($_REQUEST['formname'] == 'startUserSession') {
    StartUserSession();
  }
}

function DisplayMain () {
  print '<div class="container">'.PHP_EOL;
  $currUserSession = GetUserSession();
  if ($currUserSession === false) { 
    include("./forms/startUserSession.php");
  }
  else {
    $time1 = new DateTime($currUserSession['time_in'], new DateTimeZone(DB_TIMEZONE));
    $time2 = new DateTime('now', new DateTimeZone(DB_TIMEZONE));
    //  print (date_format($time1, 'Y-m-d H:i:s'));
    // print (date_format($time2, 'Y-m-d H:i:s'));

    $timediff = $time1->diff($time2);
    $duration = $timediff->format('%y year %m month %d days %h hour %i minute %s second');
    print 'You&apos;ve been in '.$currUserSession['building'].' since '.$currUserSession['time_in']. ' <br>' . $duration .PHP_EOL;
    //    print_r($currUserSession);
  }
  print '</div>';
}

function GetUserSession() { 
  global $pdo;
  $q = "SELECT * FROM sessions WHERE username = ? AND time_out IS NULL";
  $stmt = $pdo->prepare($q);
  $stmt->bindValue(1, $_SESSION['username']);
  $stmt->execute();
  $rows = $stmt->fetch(PDO::FETCH_ASSOC);
  if (sizeof($rows) > 0) {
    return $rows;
  }
  else {
    return false;
  }
}

function StartUserSession () {
  try {
    global $pdo;
    $q = "INSERT INTO `sessions` (`id`, `username`, `time_in`, `time_out`, `building`) VALUES (NULL, ? , now(), NULL, ?)";
    $bldg = "King";
    $stmt = $pdo->prepare($q);
    $stmt->bindValue(1, $_SESSION['username']);
    $stmt->bindValue(2, $bldg);
    $stmt->execute();
    $time = date('Y-m-d H:i:s');
    print '<div class="alert alert-success">Recorded entering '.$bldg.' at '.$time.'</div>';
  } catch (PDOException $e) {
    print '<div class="alert alert-danger">';
    print_r ($e);
    print '</div>';
  }
}
?>