<?php
session_start();
session_destroy();
header("Location: /voting_system/login.php");
exit;