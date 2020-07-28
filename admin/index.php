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
    $bldgPeople = array();

    foreach ($rows as $row) {
        if ($row['building'] != $currBuilding) { 
            $currBuilding = $row['building'];
            $bldgPeople[$currBuilding] = '';
        }
//        $durationTs = time() - strtotime($row['time_in']);
        $duration = get_time_ago(strtotime($row['time_in']));
        $bldgPeople[$currBuilding] .= '<tr><td>'.$row['name'].'</td><td>'.$row['time_in'].'</td><td>'.$duration.'</td></tr>';
    }

    foreach ($bldgPeople as $bldg => $people) {
        print '<table class="table mb-5 mt-3 w-100">';
        print '<thead><tr class="thead-dark"><th colspan="3" class="h4">'.$bldg.'</th></tr>';
        print '<tr><th width="25%">Name</th><th width="50%">Time In</th><th width="25%">Duration</th></thead><tbody>';
        print $people;
        print '</tbody></table>';
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

function get_time_ago($time_stamp)
//https://stackoverflow.com/questions/2915864/php-how-to-find-the-time-elapsed-since-a-date-time
{
    $time_difference = strtotime('now') - $time_stamp;

    if ($time_difference >= 60 * 60 * 24 * 365.242199)
    {
        /*
         * 60 seconds/minute * 60 minutes/hour * 24 hours/day * 365.242199 days/year
         * This means that the time difference is 1 year or more
         */
        return get_time_ago_string($time_stamp, 60 * 60 * 24 * 365.242199, 'year');
    }
    elseif ($time_difference >= 60 * 60 * 24 * 30.4368499)
    {
        /*
         * 60 seconds/minute * 60 minutes/hour * 24 hours/day * 30.4368499 days/month
         * This means that the time difference is 1 month or more
         */
        return get_time_ago_string($time_stamp, 60 * 60 * 24 * 30.4368499, 'month');
    }
    elseif ($time_difference >= 60 * 60 * 24 * 7)
    {
        /*
         * 60 seconds/minute * 60 minutes/hour * 24 hours/day * 7 days/week
         * This means that the time difference is 1 week or more
         */
        return get_time_ago_string($time_stamp, 60 * 60 * 24 * 7, 'week');
    }
    elseif ($time_difference >= 60 * 60 * 24)
    {
        /*
         * 60 seconds/minute * 60 minutes/hour * 24 hours/day
         * This means that the time difference is 1 day or more
         */
        return get_time_ago_string($time_stamp, 60 * 60 * 24, 'day');
    }
    elseif ($time_difference >= 60 * 60)
    {
        /*
         * 60 seconds/minute * 60 minutes/hour
         * This means that the time difference is 1 hour or more
         */
        return get_time_ago_string($time_stamp, 60 * 60, 'hour');
    }
    else
    {
        /*
         * 60 seconds/minute
         * This means that the time difference is a matter of minutes
         */
        return get_time_ago_string($time_stamp, 60, 'minute');
    }
}

function get_time_ago_string($time_stamp, $divisor, $time_unit)
{
    $time_difference = strtotime("now") - $time_stamp;
    $time_units      = floor($time_difference / $divisor);

    settype($time_units, 'string');

    if ($time_units === '0')
    {
        return 'less than 1 ' . $time_unit . ' ago';
    }
    elseif ($time_units === '1')
    {
        return '1 ' . $time_unit . ' ago';
    }
    else
    {
        /*
         * More than "1" $time_unit. This is the "plural" message.
         */
        // TODO: This pluralizes the time unit, which is done by adding "s" at the end; this will not work for i18n!
        return $time_units . ' ' . $time_unit . 's ago';
    }
}


?>
