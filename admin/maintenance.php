<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

try {
    include '../koneksi.php';
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Get maintenance mode settings
$maintenanceMode = false;
$maintenanceMessage = "We're currently performing maintenance. Please check back soon.";
$expectedUptime = "";

// Check if settings table exists
$result = $conn->query("SHOW TABLES LIKE 'site_settings'");
if ($result->num_rows == 0) {
    // Create settings table if it doesn't exist
    $conn->query("CREATE TABLE site_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_name VARCHAR(50) NOT NULL UNIQUE,
        setting_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
}

// Get current settings
$query = "SELECT * FROM site_settings WHERE setting_name IN ('maintenance_mode', 'maintenance_message', 'maintenance_expected_uptime')";
$result = $conn->query($query);

$settings = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_name']] = $row['setting_value'];
    }
}

if (isset($settings['maintenance_mode'])) {
    $maintenanceMode = ($settings['maintenance_mode'] == '1');
}

if (isset($settings['maintenance_message'])) {
    $maintenanceMessage = $settings['maintenance_message'];
}

if (isset($settings['maintenance_expected_uptime'])) {
    $expectedUptime = $settings['maintenance_expected_uptime'];
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update maintenance mode
    $newMode = isset($_POST['maintenance_mode']) ? '1' : '0';
    $newMessage = $_POST['maintenance_message'];
    $newUptime = $_POST['expected_uptime'];

    // Update or insert maintenance mode
    $stmt = $conn->prepare("INSERT INTO site_settings (setting_name, setting_value) VALUES ('maintenance_mode', ?) 
                          ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->bind_param("ss", $newMode, $newMode);
    $stmt->execute();

    // Update or insert maintenance message
    $stmt = $conn->prepare("INSERT INTO site_settings (setting_name, setting_value) VALUES ('maintenance_message', ?) 
                          ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->bind_param("ss", $newMessage, $newMessage);
    $stmt->execute();

    // Update or insert expected uptime
    $stmt = $conn->prepare("INSERT INTO site_settings (setting_name, setting_value) VALUES ('maintenance_expected_uptime', ?) 
                          ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->bind_param("ss", $newUptime, $newUptime);
    $stmt->execute();

    $maintenanceMode = ($newMode == '1');
    $maintenanceMessage = $newMessage;
    $expectedUptime = $newUptime;

    $success_message = "Maintenance settings updated successfully!";
}

// Include header
include 'templates/header.php';
?>

<!-- Page content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="page-heading">
        <h1>Maintenance Mode</h1>
    </div>

    <?php if ($maintenanceMode): ?>
        <div class="maintenance-banner">
            <i class="fas fa-exclamation-triangle"></i> Website is currently in MAINTENANCE MODE - Users cannot access the site
        </div>
    <?php endif; ?>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <!-- Maintenance Settings -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Maintenance Mode Settings</h6>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="form-group mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="maintenanceToggle" name="maintenance_mode" <?php echo $maintenanceMode ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="maintenanceToggle">
                                    <strong>Enable Maintenance Mode</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                When maintenance mode is enabled, regular users cannot access the site. Only administrators can log in.
                            </small>
                        </div>

                        <div class="form-group mb-4">
                            <label for="maintenance_message"><strong>Maintenance Message</strong></label>
                            <textarea class="form-control" id="maintenance_message" name="maintenance_message" rows="3"><?php echo htmlspecialchars($maintenanceMessage); ?></textarea>
                            <small class="form-text text-muted">
                                This message will be displayed to users when they try to access the site during maintenance.
                            </small>
                        </div>

                        <div class="form-group mb-4">
                            <label for="expected_uptime"><strong>Expected Uptime</strong></label>
                            <input type="datetime-local" class="form-control" id="expected_uptime" name="expected_uptime" value="<?php echo $expectedUptime; ?>">
                            <small class="form-text text-muted">
                                When do you expect the site to be back online? (Optional)
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Preview</h6>
                </div>
                <div class="card-body">
                    <div class="maintenance-preview">
                        <div class="text-center mb-4">
                            <i class="fas fa-tools fa-4x text-primary mb-3"></i>
                            <h3>Maintenance Mode</h3>
                            <p class="maintenance-message"><?php echo nl2br(htmlspecialchars($maintenanceMessage)); ?></p>

                            <?php if (!empty($expectedUptime)): ?>
                                <p>
                                    <strong>Expected to be back online:</strong><br>
                                    <?php echo date('F j, Y, g:i a', strtotime($expectedUptime)); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Maintenance Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="maintenance-tips">
                        <li>Only use maintenance mode when necessary.</li>
                        <li>Inform users in advance about planned maintenance, if possible.</li>
                        <li>Keep maintenance periods as short as possible.</li>
                        <li>Provide clear information about when the site will be back online.</li>
                        <li>Test any major changes in a staging environment before turning off maintenance mode.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .maintenance-preview {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
    }

    .maintenance-message {
        margin-top: 15px;
        font-size: 16px;
    }

    .maintenance-tips {
        padding-left: 20px;
    }

    .maintenance-tips li {
        margin-bottom: 10px;
    }
</style>

<?php
// Include footer
include 'templates/footer.php';
?>