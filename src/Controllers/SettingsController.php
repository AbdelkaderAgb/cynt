<?php
/**
 * CYN Tourism — SettingsController
 */
class SettingsController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();

        $settings = [];
        $rows = Database::fetchAll("SELECT setting_key, setting_value, setting_group FROM settings ORDER BY setting_group, setting_key");
        foreach ($rows as $r) {
            $settings[$r['setting_group']][$r['setting_key']] = $r['setting_value'];
        }

        $this->view('settings/index', [
            'settings'   => $settings,
            'pageTitle'  => __('system_settings') ?: 'System Settings',
            'activePage' => 'settings',
        ]);
    }

    public function update(): void
    {
        Auth::requireAdmin();
        $this->requireCsrf();

        foreach ($_POST as $key => $value) {
            if ($key === '_token') continue;
            Database::execute(
                "INSERT INTO settings (setting_key, setting_value, setting_group)
                 VALUES (?, ?, 'general')
                 ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value, updated_at = datetime('now')",
                [trim($key), trim($value)]
            );
        }

        header('Location: ' . url('settings') . '?saved=1');
        exit;
    }

    public function email(): void
    {
        Auth::requireAdmin();
        $this->requireCsrf();

        $config = Database::fetchOne("SELECT * FROM email_config LIMIT 1") ?? [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'smtp_host'            => $_POST['smtp_host'] ?? '',
                'smtp_port'            => (int)($_POST['smtp_port'] ?? 587),
                'smtp_username'        => $_POST['smtp_username'] ?? '',
                'smtp_password'        => $_POST['smtp_password'] ?? '',
                'from_email'           => $_POST['from_email'] ?? '',
                'from_name'            => $_POST['from_name'] ?? '',
                'enable_notifications' => isset($_POST['enable_notifications']) ? 1 : 0,
                'enable_reminders'     => isset($_POST['enable_reminders']) ? 1 : 0,
            ];

            if (!filter_var($data['from_email'], FILTER_VALIDATE_EMAIL)) {
                header('Location: ' . url('settings/email') . '?error=invalid_email');
                exit;
            }

            if (!empty($config['id'])) {
                $sets = []; $params = [];
                foreach ($data as $k => $v) { $sets[] = "$k = ?"; $params[] = $v; }
                $params[] = $config['id'];
                Database::execute("UPDATE email_config SET " . implode(', ', $sets) . " WHERE id = ?", $params);
            } else {
                Database::getInstance()->insert('email_config', $data);
            }

            header('Location: ' . url('settings/email') . '?saved=1');
            exit;
        }

        $this->view('settings/email', [
            'config'     => $config,
            'pageTitle'  => __('email_settings') ?: 'Email Settings',
            'activePage' => 'settings',
        ]);
    }
}
