<?php
session_start();
HandleOverlapSubmit();

function HandleOverlapSubmit() {
    if (array_key_exists('username', $_REQUEST)) {
        list ($_SESSION['username'], $_SESSION['display_name']) = preg_split ('/\:\:/', $_REQUEST['username']);
    }
    if (array_key_exists('startRange', $_REQUEST)) {
        $_SESSION['startRange'] = $_REQUEST['startRange'];
    } 
    else { 
        $_SESSION['startRange'] = date("Y-m-d",strtotime("-3 weeks"));
    }
    if (array_key_exists('endRange', $_REQUEST)) {
        $_SESSION['endRange'] = $_REQUEST['endRange'];
    } 
    else { 
        $_SESSION['endRange'] = date("Y-m-d");
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUL Contact Tracker: Admin Console</title>
<?php
include('../config.php');
include('../ui_scripts.php');
include('../bootstrap.php');
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" />
<script>
$(document).ready(function() {
    $('.datepicker').datepicker({
            format: 'yyyy-mm-dd'
        });
    });
</script>
<link rel="stylesheet" href="../styles.css" />
</head>
<body>
<nav class="navbar bg-light navbar-light mb-2">
<div class="navbar-brand">MUL Tracker Admin Console</div>
</nav>
<div class="container">
<form method="POST" class="form form-inline mb-3 mt-3">
<input type="hidden" name="formname" value="adminOverlap">
<div class="input-group mr-2">
<div class="input-group-prepend">
<label for="username" class="input-group-text">View overlap with user</label> <?php print(SelectUser($_SESSION['username']));?>
</div>
</div>

<div class="input-group mr-2">
<div class="input-group-prepend">
<label for="startRange" class="input-group-text">From:</label>
<input type="text" id="startRange" name="startRange" placeholder="Start Date" value="<?php print($_SESSION['startRange']); ?>" class="form-control datepicker">
</div>
</div>

<div class="input-group mr-3">
<div class="input-group-prepend">
<label for="endRange" class="input-group-text">To:</label>
<input type="text" id="startRange" name="endRange" placeholder="End Date" value="<?php print($_SESSION['endRange']); ?>" class="form-control datepicker">
</div>
</div>

<input type="submit" class="btn btn-primary py-1" value="Find Overlaps"/>
</form>

<form class="form" method="POST">
<input type="hidden" name="formname" value="GetNow">
<input type="submit" class="btn btn-danger w-100" value="Who is in the building now?">
</form>
</div>


<div class="main container">
<?php
    if ($_REQUEST['formname'] == 'adminOverlap') {
        $rows = GetOverlap($_SESSION['username']);
        if (sizeof($rows) >0) {
            print (OverlapTable($rows));
        } else { 
            print '<div class="alert alert-info">No overlap results found for <b>'.$_SESSION['display_name'].'</b>.</div>'.PHP_EOL;
        }
    }
elseif ($_REQUEST['formname'] == 'GetNow') {
    $rows = GetNow();
/*
    print ('<pre>');
    var_dump($rows);
    print ('</pre>');
*/
    $currBuilding = '';
    foreach ($rows as $row) {
        if ($row['building'] != $currBuilding) { 
            print '<h2>'.$row['building'].'</h2>';
            $currBuilding = $row['building'];
        }
        print '<li>'.$row['name'].'</li>';
    }
}
    ?>
</div>
</body>
</html>


<?php
function GetNow() {
    global $pdo;
    $q = 'SELECT name,building,time_in FROM sessions,users WHERE time_out IS NULL AND users.username = sessions.username ORDER BY building';
    return ($pdo->query($q)->fetchAll(PDO::FETCH_ASSOC));

}


function GetOverlap($user) {
// https://stackoverflow.com/questions/6571538/checking-a-table-for-time-overlap
    print '<h1 class="h2 mb-4">Getting building overlaps for: '.$_SESSION['display_name'].'</h1>'.PHP_EOL;
$q ="SELECT a.username as subject_name, a.time_in as subject_in, a.time_out as subject_out, a.building as building, b.building as b_building, b.username as cmp_name, b.time_in as cmp_in, b.time_out as cmp_out
FROM sessions  a
JOIN sessions b on a.time_in <= b.time_out
    and a.time_out >= b.time_in
    and a.username != b.username
    WHERE a.username = ? 
    AND a.building = b.building
    AND (a.time_in BETWEEN ? AND ? 
    OR   a.time_out BETWEEN ? AND ? )";

    print '<!--div class="alert alert-info">'.$q.'</div-->'.PHP_EOL;
    
    global $pdo;
    $stmt = $pdo->prepare($q); 
    $stmt->bindValue(1, $_SESSION['username']);
    $stmt->bindValue(2, $_SESSION['startRange'] . ' 00:00:00');
    $stmt->bindValue(3, $_SESSION['endRange']   . ' 23:59:59');
    $stmt->bindValue(4, $_SESSION['startRange'] . ' 00:00:00');
    $stmt->bindValue(5, $_SESSION['endRange']   . ' 23:59:59');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return($rows);
}

function OverlapTable($rows) {
    $names = GetNames();
    $fmt = 'M d H:i';
    $table = '<table class="table overlap">';
    $table .= '<thead>';
    $table .= '<tr><th class="subject">Subject Name</th> <th class="subject">Building</th> <th class="subject">Subject In</th> <th class="subject">Subject Out</th> <th class="coworker">Co-Worker Name</th> <th class="coworker">Co-Worker In</th> <th class="coworker">Co-Worker Out</th></tr>'.PHP_EOL;
    $table .= '</thead><tbody>';
    foreach ($rows as $r) {
        $subj_user = $r['subject_name'];
        $subj_name = $names[$subj_user];
        $cmp_user  = $r['cmp_name'];
        $cmp_name  = $names[$cmp_user];
        $table .= '<tr><td class="subject">'.$subj_name.'</td> <td class="subject">'.$r['building'].'</td> <td class="subject">'.date($fmt, strtotime($r['subject_in'])).'</td> <td class="subject">'.date($fmt, strtotime($r['subject_out'])).'</td> <td class="coworker">'.$cmp_name.'</td> <td class="coworker">'.date($fmt, strtotime($r['cmp_in'])).'</td> <td class="coworker">'.date($fmt, strtotime($r['cmp_out'])).'</td></tr>'.PHP_EOL;
    }
    $table .= '</tbody></table>';
    return $table;
}

function GetNames() {
    global $pdo;
    $q = "SELECT * FROM users";
    $stmt = $pdo->query($q);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $arr = [];
    foreach ($rows as $r) {
        $name = $r['name'];
        $username = $r['username'];
        $arr[$username] = $name;
    }
    return $arr;
}
?>
