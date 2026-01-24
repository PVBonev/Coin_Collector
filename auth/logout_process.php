<?php
session_start();
session_destroy();
header("Location: ../countries.php");
exit;//to test the codes
?>

