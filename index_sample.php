<?php
// Start session
session_start();

// Include maintenance check (must be at the top after session_start)
include 'maintenance_check.php';

// Rest of your code for the index page...
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace - Home</title>
    <!-- Your CSS includes -->
</head>

<body>
    <!-- Your page content -->
    <h1>Welcome to Marketplace</h1>
    <p>This page will only be visible if the site is not in maintenance mode.</p>

    <!-- Rest of your page content -->
</body>

</html>