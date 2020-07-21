<?php
    $select = SelectUser();
    
    print '<span class="ml-auto">';
    print '<form method="POST" class="form-inline">'.PHP_EOL;
    print '<input type="hidden" name="formname" value="login">'.PHP_EOL;
    print '<label for="username" class="mr-1">Login as:</label>';
    print $select;
    print '<input type="submit" class="btn btn-primary btn-sm py-0">';
    print '</form>'.PHP_EOL;
    print '</span>';
?>