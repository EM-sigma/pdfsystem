<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
session_destroy();
header("Location: /pdfsystem/"); // Redirect to login page
exit();
?>  