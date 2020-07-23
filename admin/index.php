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
<form>
<label for="username">View overlap with user</label> <?php print(SelectUser($_SESSION['username']));?>
<input type="submit" class="btn btn-primary btn-sm py-1" />
</form>
</div>

<?php
function GetOverlap($user, $start, $end) {
// https://stackoverflow.com/questions/6571538/checking-a-table-for-time-overlap
$q = "SELECT *
FROM sessions  a
JOIN sessions b on a.time_in <= b.time_out
    and a.time_out >= b.time_in
    and a.username != b.username
    and a.building = b.building
    WHERE a.username = ?";
}

?>
</body>
</html>