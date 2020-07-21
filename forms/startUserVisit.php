<form method="POST">
   <input type="hidden" name="formname" value="startUserVisit" /> 
   <input type="hidden" name="username" value="<?php echo($_SESSION['username']); ?>" />
<?php
   foreach (BUILDINGS as $bldg) {
   print '<input type="submit" class="btn btn-success mb-3 col-sm-12" name="enterButton" value="Enter '. $bldg .' now" />';
 }
?>
</form>