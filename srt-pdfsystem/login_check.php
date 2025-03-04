<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function get_user_role() {  // If you need it
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
}
?>