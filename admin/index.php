<?php
session_start();
if (array_key_exists('username', $_REQUEST)) {
    list ($_SESSION['username'], $_SESSION['display_name']) = preg_split ('/\:\:/', $_REQUEST['username']);
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
<link rel="stylesheet" href="styles.css" />
</head>
<body>
<nav class="navbar bg-light navbar-light mb-2">

</nav>
<div class="container">
<form method="POST">
<input type="hidden" name="formname" value="adminOverlap">
<label for="username">View overlap with user</label> <?php print(SelectUser($_SESSION['username']));?>
<input type="submit" class="btn btn-primary btn-sm py-1" />
</form>
</div>

<div class="main container">
    <?php
    if ($_REQUEST['formname'] == 'adminOverlap') {
        GetOverlap($_SESSION['username']);
    }
    ?>
</div>



<?php
function GetOverlap($user, $start=null, $end=null) {
// https://stackoverflow.com/questions/6571538/checking-a-table-for-time-overlap
    print '<h2>Getting building overlaps for: '.$_SESSION['display_name'].'</h2>'.PHP_EOL;
$q ="SELECT a.username as subject_name, a.time_in as subject_in, a.time_out as subject_out, a.building as building, b.username as cmp_name, b.time_in as cmp_in, b.time_out as subj_out
FROM sessions  a
JOIN sessions b on a.time_in <= b.time_out
    and a.time_out >= b.time_in
    and a.username != b.username
    WHERE a.username = ?";

    print '<div class="alert alert-info">'.$q.'</div>'.PHP_EOL;
    
    global $pdo;
    $stmt = $pdo->prepare($q); 
    $stmt->bindValue(1, $_SESSION['username']);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print '<pre>';
    print_r($rows);
    print '</pre>';
}

?>
</body>
</html>