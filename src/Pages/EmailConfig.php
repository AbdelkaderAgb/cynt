<?php
/**
 * CYN Tourism - Email Configuration
 * Configure SMTP and email settings
 */

require_once dirname(__DIR__, 2) . '/config/config.php'; require_once dirname(__DIR__, 2) . '/src/Core/Auth.php';

Auth::requireAuth();
Auth::requireAdmin();

$success = '';
$error = '';

// Load current config
$config = Database::fetchOne("SELECT * FROM email_config LIMIT 1") ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'smtp_host' => $_POST['smtp_host'] ?? '',
        'smtp_port' => (int)($_POST['smtp_port'] ?? 587),
        'smtp_username' => $_POST['smtp_username'] ?? '',
        'smtp_password' => $_POST['smtp_password'] ?? '',
        'from_email' => $_POST['from_email'] ?? '',
        'from_name' => $_POST['from_name'] ?? '',
        'enable_notifications' => isset($_POST['enable_notifications']) ? 1 : 0,
        'enable_reminders' => isset($_POST['enable_reminders']) ? 1 : 0
    ];

    if (!empty($config['id'])) {
        Database::execute(
            "UPDATE email_config SET smtp_host = ?, smtp_port = ?, smtp_username = ?, smtp_password = ?,
             from_email = ?, from_name = ?, enable_notifications = ?, enable_reminders = ? WHERE id = ?",
            [$data['smtp_host'], $data['smtp_port'], $data['smtp_username'], $data['smtp_password'],
             $data['from_email'], $data['from_name'], $data['enable_notifications'], $data['enable_reminders'], $config['id']]
        );
    } else {
        Database::execute(
            "INSERT INTO email_config (smtp_host, smtp_port, smtp_username, smtp_password, from_email, from_name, enable_notifications, enable_reminders)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$data['smtp_host'], $data['smtp_port'], $data['smtp_username'], $data['smtp_password'],
             $data['from_email'], $data['from_name'], $data['enable_notifications'], $data['enable_reminders']]
        );
    }

    $success = 'Email ayarlari kaydedildi.';
    $config = array_merge($config, $data);
}

$pageTitle = 'Email Ayarlari';
$activePage = 'settings';
// TODO: Convert to MVC view -- include 'header.php';
?>

<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1 class="page-title">Email Ayarlari</h1>
            <p class="page-subtitle">SMTP ve bildirim ayarlarini yapilandirin</p>
        </div>
    </div>
</div>

<?php if ($success): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <h3 style="margin-bottom: 1.5rem;">SMTP Ayarlari</h3>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">SMTP Sunucu</label>
                    <input type="text" name="smtp_host" class="form-control" value="<?php echo htmlspecialchars($config['smtp_host'] ?? ''); ?>" placeholder="smtp.gmail.com">
                </div>
                <div class="form-group">
                    <label class="form-label">SMTP Port</label>
                    <input type="number" name="smtp_port" class="form-control" value="<?php echo $config['smtp_port'] ?? 587; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">SMTP Kullanici Adi</label>
                    <input type="text" name="smtp_username" class="form-control" value="<?php echo htmlspecialchars($config['smtp_username'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">SMTP Sifre</label>
                    <input type="password" name="smtp_password" class="form-control" value="<?php echo htmlspecialchars($config['smtp_password'] ?? ''); ?>">
                </div>
            </div>

            <h3 style="margin: 1.5rem 0;">Gonderici Ayarlari</h3>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Gonderici Email</label>
                    <input type="email" name="from_email" class="form-control" value="<?php echo htmlspecialchars($config['from_email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Gonderici Adi</label>
                    <input type="text" name="from_name" class="form-control" value="<?php echo htmlspecialchars($config['from_name'] ?? ''); ?>">
                </div>
            </div>

            <h3 style="margin: 1.5rem 0;">Bildirim Ayarlari</h3>
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="enable_notifications" <?php echo ($config['enable_notifications'] ?? 0) ? 'checked' : ''; ?>>
                    <span>Email bildirimlerini etkinlestir</span>
                </label>
            </div>
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="enable_reminders" <?php echo ($config['enable_reminders'] ?? 0) ? 'checked' : ''; ?>>
                    <span>Otomatik hatirlatma email'lerini etkinlestir</span>
                </label>
            </div>

            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Kaydet
                </button>
                <a href="send-test-email.php" class="btn btn-secondary">
                    <i class="fas fa-envelope"></i> Test Email Gonder
                </a>
            </div>
        </form>
    </div>
</div>

<?php // TODO: Convert to MVC view -- include 'footer.php'; ?>
