<?php
/*** Scripts ***/

function LoginBar($user = null) { 
  print '<nav class="navbar bg-light navbar-light mb-2">';

  if (is_null($user)) {
    include('forms/loginForm.php');
  }

  else { 
    include('forms/logoutForm.php');
  }

  print '</nav>';
}

function SelectUser($remember_name='') {
  $opts = '';
  global $pdo;
  $q = "SELECT * FROM users ORDER BY name ASC";
  $stmt = $pdo->query($q);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach ($rows as $row) {
      if ($remember_name !== '' && ($row['username'] == $_SESSION['username'])) {
          $s = ' selected';
      } else { 
          $s = ''; 
      }

    $opts .= '<option value="'.$row['username'].'::'.$row['name'].'"'.$s.'>'.$row['name'].'</option>'.PHP_EOL;
  }
  return '<select name="username" class="form-control">'.$opts.'</select>'.PHP_EOL;
}


function HandleForm() {
  //  print_r ($_REQUEST);
  $alert = ''; 
  if ($_REQUEST['formname'] == 'login') {
    list ($_SESSION['username'], $_SESSION['display_name']) = preg_split ('/\:\:/', $_REQUEST['username']);
  }

  elseif ($_REQUEST['formname'] == 'logout') {
    session_destroy();
    session_start();
  }

  elseif ($_REQUEST['formname'] == 'startUserVisit') {
    $alert = StartUserVisit();
  }

  elseif ($_REQUEST['formname'] == 'userExit') {
    $alert = EndUserVisit($_REQUEST['visitId'], $_REQUEST['building']);
  }

  return $alert;
}

function DisplayMain () {
  print '<div class="container">'.PHP_EOL;
  $currUserVisit = GetUserVisit();
  if ($currUserVisit === false) { 
    include("./forms/startUserVisit.php");
  }
  else {
    $time1 = new DateTime($currUserVisit['time_in'], new DateTimeZone(DB_TIMEZONE));
    $time2 = new DateTime('now', new DateTimeZone(DB_TIMEZONE));
    //  print (date_format($time1, 'Y-m-d H:i:s'));
    // print (date_format($time2, 'Y-m-d H:i:s'));

    $timediff = $time1->diff($time2);
    //    $duration = $timediff->format('%y year %m month %d days %h hour %i minute %s second');
    $duration = $timediff->format('%d days %h hour %i minute %s second');
    $refresh = '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrow-clockwise" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M3.17 6.706a5 5 0 0 1 7.103-3.16.5.5 0 1 0 .454-.892A6 6 0 1 0 13.455 5.5a.5.5 0 0 0-.91.417 5 5 0 1 1-9.375.789z"/>
  <path fill-rule="evenodd" d="M8.147.146a.5.5 0 0 1 .707 0l2.5 2.5a.5.5 0 0 1 0 .708l-2.5 2.5a.5.5 0 1 1-.707-.708L10.293 3 8.147.854a.5.5 0 0 1 0-.708z"/>
</svg>';
    print 'You&apos;ve been in <span class="location">'.$currUserVisit['building'].'</span> since '.$currUserVisit['time_in']. ' <br>' . $duration .PHP_EOL;
    print '<a href="./" class="btn btn-primary btn-sm p-1">'.$refresh.'<span class="sr-only">Refresh Timer</span></a>';
    print '<form method="POST">';
    print '<input type="hidden" name="formname" value="userExit">';
    print '<input type="hidden" name="building" value="'.$currUserVisit['building'].'">';
    print '<input type="hidden" name="visitId" value="'.$currUserVisit['id'].'">';
    print '<input type="submit" class="btn btn-warning col-sm-12 mt-5" value="Exit '.$currUserVisit['building'].'" />';    
    print '</form>';
  }
  print '</div>';
}

function GetUserVisit() { 
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

function StartUserVisit () {
  try {
    global $pdo;
    $q = "INSERT INTO `sessions` (`username`, `time_in`, `time_out`, `building`) VALUES (? , now(), NULL, ?)";
    if (preg_match('/Enter (.*) now/', $_REQUEST['enterButton'], $m)) {
      $bldg = $m[1];
    }
    $stmt = $pdo->prepare($q);
    $stmt->bindValue(1, $_SESSION['username']);
    $stmt->bindValue(2, $bldg);
    $time = date('Y-m-d H:i:s');
    $stmt->execute();
    if ($stmt->rowCount() == 1) {
        $alert = '<div class="alert alert-success">Recorded entering '.$bldg.' at '.$time.'</div>';
    }
    else {
        $alert = '<div class="alert alert-danger">ERROR: Failed to record entering '.$bldg.' at '.$time.'</div>';
    }
  } catch (PDOException $e) {
    $alert =  '<div class="alert alert-danger">';
    $alert .= print_r ($e, TRUE);
    $alert .= print '</div>';
  }
  return $alert;
}

function EndUserVisit($id, $bldg) {
  try {
    global $pdo;
    $q = 'UPDATE sessions SET time_out = now() where id = ?';
    $stmt = $pdo->prepare($q);
    $stmt->bindValue(1, $id);
    $stmt->execute();
    $time = date('Y-m-d H:i:s');
    if ($stmt->rowCount() == 1) {
        $alert = '<div class="alert alert-success">Recorded exiting '.$bldg.' at '.$time.'</div>';
    }
    else {
        $alert = '<div class="alert alert-danger">Failed to record exiting '.$bldg.' at '.$time.'</div>';
    }
  } catch (PDOException $e) {
    $alert = '<div class="alert alert-danger">';
    $alert .= print_r ($e, TRUE);
    $alert .= print '</div>';
  }
  return $alert;
}

?>