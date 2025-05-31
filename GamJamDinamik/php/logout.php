<?php
session_start();
session_destroy();
header("Location: ../WebSayfası/index.html");
exit();
?>