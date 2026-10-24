<?php
session_start();
require_once '../config.php';
requireAdmin();

$page_title = 'Site Settings';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $settings = [
        'site_name' => sanitize($_POST['site_name']),
        'site_email' => sanitize($_POST['site_email']),
        'site_phone' => sanitize($_POST['site_phone']),
        'site_address' => sanitize($_POST['site_address']),
        'currency' => sanitize($_POST['currency']),
        'tax_rate' => (float)$_POST['tax_rate'],
        'shipping_cost' => (float)$_POST['shipping_cost'],
        'free_shipping_threshold' => (float)$_POST['free_shipping_threshold']
    ];
    
    foreach ($settings as $key => $value) {
        $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $key, $value, $value);
        $stmt->execute();
    }
    
    setMessage('Settings updated successfully', 'success');
    redirect('settings.php');
}

// Get current settings
$settings_result = $conn->query("SELECT * FROM settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

include 'includes/header.php';
?>

<h2 style="margin-bottom: 2rem;">Site Settings</h2>

<div style="max-width: 800px;">
    <form method="POST" class="admin-form">
        <!-- General Settings -->
        <div style="background: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #f0f0f0;">
                <i class="fas fa-cog"></i> General Settings
            </h3>
            
            <div class="form-group">
                <label>Site Name *</label>
                <input type="text" name="site_name" value="<?php echo $settings['site_name'] ?? 'RoseClay'; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Site Email *</label>
                <input type="email" name="site_email" value="<?php echo $settings['site_email'] ?? 'info@roseclay.com'; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Site Phone *</label>
                <input type="text" name="site_phone" value="<?php echo $settings['site_phone'] ?? '+1 (555) 123-4567'; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Site Address *</label>
                <textarea name="site_address" rows="3" required><?php echo $settings['site_address'] ?? '123 Clay Street, California, USA 90001'; ?></textarea>
            </div>
        </div>
        
        <!-- Currency Settings -->
        <div style="background: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #f0f0f0;">
                <i class="fas fa-dollar-sign"></i> Currency & Pricing
            </h3>
            
            <div class="form-group">
                <label>Default Currency *</label>
                <select name="currency" required>
                    <option value="USD" <?php echo ($settings['currency'] ?? 'USD') == 'USD' ? 'selected' : ''; ?>>USD - US Dollar</option>
                    <option value="INR" <?php echo ($settings['currency'] ?? 'USD') == 'INR' ? 'selected' : ''; ?>>INR - Indian Rupee</option>
                    <option value="EUR" <?php echo ($settings['currency'] ?? 'USD') == 'EUR' ? 'selected' : ''; ?>>EUR - Euro</option>
                    <option value="GBP" <?php echo ($settings['currency'] ?? 'USD') == 'GBP' ? 'selected' : ''; ?>>GBP - British Pound</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Tax Rate (%) *</label>
                <input type="number" name="tax_rate" step="0.01" value="<?php echo $settings['tax_rate'] ?? '10'; ?>" required>
                <small style="color: #999;">Enter tax rate as percentage (e.g., 10 for 10%)</small>
            </div>
        </div>
        
        <!-- Shipping Settings -->
        <div style="background: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #f0f0f0;">
                <i class="fas fa-shipping-fast"></i> Shipping Settings
            </h3>
            
            <div class="form-group">
                <label>Standard Shipping Cost (₹) *</label>
                <input type="number" name="shipping_cost" step="0.01" value="<?php echo $settings['shipping_cost'] ?? '0'; ?>" required>
                <small style="color: #999;">Set to 0 for free shipping</small>
            </div>
            
            <div class="form-group">
                <label>Free Shipping Threshold (₹) *</label>
                <input type="number" name="free_shipping_threshold" step="0.01" value="<?php echo $settings['free_shipping_threshold'] ?? '50'; ?>" required>
                <small style="color: #999;">Orders above this amount get free shipping</small>
            </div>
        </div>
        
        <!-- System Information -->
        <div style="background: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #f0f0f0;">
                <i class="fas fa-info-circle"></i> System Information
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <strong>PHP Version:</strong><br>
                    <span style="color: #666;"><?php echo phpversion(); ?></span>
                </div>
                <div>
                    <strong>MySQL Version:</strong><br>
                    <span style="color: #666;"><?php echo $conn->server_info; ?></span>
                </div>
                <div>
                    <strong>Server Software:</strong><br>
                    <span style="color: #666;"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span>
                </div>
                <div>
                    <strong>Upload Max Size:</strong><br>
                    <span style="color: #666;"><?php echo ini_get('upload_max_filesize'); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Save Button -->
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn-admin btn-admin-primary">
                <i class="fas fa-save"></i> Save Settings
            </button>
            <a href="index.php" class="btn-admin btn-admin-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
