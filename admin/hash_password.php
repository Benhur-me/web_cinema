<?php
$password = '1234'; // The plain text password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo $hashed_password;
?>
