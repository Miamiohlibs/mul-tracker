<?php
    print '<span class="ml-auto"><form class="form-inline" method="POST"><input type="hidden" name="formname" value="logout">';
    print 'Logging info for: <span class="logging-name ml-1">'.$_SESSION['display_name'];
    print '<input type="submit" class="btn btn-sm btn-danger ml-2 py-0" value="Logout" / ></form></span>';
?>