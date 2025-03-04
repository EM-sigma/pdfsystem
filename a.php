<?php

// Function to hash a password (using password_hash)
function hash_password($password) {
    // PASSWORD_DEFAULT is recommended; it uses bcrypt and will adapt 
    // to stronger algorithms in the future if they become available.
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    return $hashed_password;
}

// Function to verify a password against a hash
function verify_password($password, $hashed_password) {
    if (password_verify($password, $hashed_password)) {
        return true; // Password matches
    } else {
        return false; // Password does not match
    }
}


// Example usage (during user registration or password update):
$plain_password = "1234";  // The user's entered password
$hashed_password = hash_password($plain_password);
echo "Hashed password: " . $hashed_password . "<br>";


// Example usage (during login):
$entered_password = "1234"; // The user's entered password
$stored_hashed_password = $hashed_password; // Retrieve this from your database

if (verify_password($entered_password, $stored_hashed_password)) {
    echo "Password verified successfully!";
    // Proceed with login
} else {
    echo "Incorrect password.";
}

?>