<?php
session_start();
session_unset();
session_destroy();
header('Location: /bus/src/login.php');
exit;