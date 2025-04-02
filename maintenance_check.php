<?php
// This file should be included at the top of all front-end pages (except admin pages)
// to check if the site is in maintenance mode

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to check if current user is an admin
function is_admin()
{
    return isset($_SESSION['id_user']) && isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

// Function to check maintenance mode
function check_maintenance_mode()
{
    global $conn;

    // Skip checks for admin pages
    $request_uri = $_SERVER['REQUEST_URI'];
    if (strpos($request_uri, '/admin/') !== false) {
        return false;
    }

    // Skip checks for admin users
    if (is_admin()) {
        return false;
    }

    try {
        // Check if settings table exists
        $result = $conn->query("SHOW TABLES LIKE 'site_settings'");
        if ($result && $result->num_rows > 0) {
            // Get maintenance mode status
            $query = "SELECT setting_value FROM site_settings WHERE setting_name = 'maintenance_mode'";
            $result = $conn->query($query);

            if ($result && $row = $result->fetch_assoc()) {
                return $row['setting_value'] == '1';
            }
        }

        return false;
    } catch (Exception $e) {
        // On error, don't block access
        return false;
    }
}

// Function to get maintenance message
function get_maintenance_message()
{
    global $conn;

    try {
        $query = "SELECT setting_value FROM site_settings WHERE setting_name = 'maintenance_message'";
        $result = $conn->query($query);

        if ($result && $row = $result->fetch_assoc()) {
            return $row['setting_value'];
        }

        return "We're currently performing maintenance. Please check back soon.";
    } catch (Exception $e) {
        return "We're currently performing maintenance. Please check back soon.";
    }
}

// Function to get expected uptime
function get_expected_uptime()
{
    global $conn;

    try {
        $query = "SELECT setting_value FROM site_settings WHERE setting_name = 'maintenance_expected_uptime'";
        $result = $conn->query($query);

        if ($result && $row = $result->fetch_assoc()) {
            return $row['setting_value'];
        }

        return "";
    } catch (Exception $e) {
        return "";
    }
}

// Include database connection
if (!isset($conn)) {
    try {
        include 'koneksi.php';
    } catch (Exception $e) {
        // If can't connect to database, assume site is not in maintenance mode
        die("Error connecting to database: " . $e->getMessage());
    }
}

// Check if site is in maintenance mode
if (check_maintenance_mode()) {
    // Get maintenance message and expected uptime
    $maintenance_message = get_maintenance_message();
    $expected_uptime = get_expected_uptime();

    // Display maintenance page
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Site Maintenance</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            body {
                background-color: #f8f9fa;
                font-family: 'Arial', sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100vh;
                margin: 0;
                color: #333;
            }

            .maintenance-container {
                max-width: 600px;
                background-color: #fff;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
                text-align: center;
            }

            .maintenance-icon {
                font-size: 4rem;
                color: #dc3545;
                margin-bottom: 20px;
            }

            .maintenance-title {
                font-size: 2rem;
                margin-bottom: 20px;
            }

            .maintenance-message {
                margin-bottom: 20px;
                line-height: 1.6;
            }

            .maintenance-expected {
                font-weight: bold;
                margin-top: 20px;
            }

            .login-link {
                margin-top: 30px;
            }
        </style>
    </head>

    <body>
        <div class="maintenance-container">
            <div class="maintenance-icon">
                <i class="fas fa-tools"></i>
            </div>
            <h1 class="maintenance-title">Site Under Maintenance</h1>
            <div class="maintenance-message">
                <?php echo nl2br(htmlspecialchars($maintenance_message)); ?>
            </div>

            <?php if (!empty($expected_uptime)): ?>
                <div class="maintenance-expected">
                    Expected to be back online:<br>
                    <?php echo date('F j, Y, g:i a', strtotime($expected_uptime)); ?>
                </div>
            <?php endif; ?>

            <div class="login-link">
                <a href="admin/login.php" class="btn btn-outline-primary">Admin Login</a>
            </div>
        </div>
    </body>

    </html>
<?php
    exit();
}
?>