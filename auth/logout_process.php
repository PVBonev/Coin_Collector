<?php
session_start();
session_destroy();
header("Location: ../login.php");
exit;//to test the code
?>

