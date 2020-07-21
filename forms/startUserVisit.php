<form method="POST">
   <input type="hidden" name="formname" value="startUserVisit" /> 
   <input type="hidden" name="username" value="<?php echo($_SESSION['username']); ?>" />
   <input type="submit" class="btn btn-success" value="Enter building now" />
</form>