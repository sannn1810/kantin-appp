<?php
session_start();
session_destroy();
header("Location: order.php");
exit;
