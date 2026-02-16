<?php
/**
 * CYN Tourism — NotificationController
 */
class NotificationController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $db = Database::getInstance()->getConnection();
        $user = $this->user();
        $userId = $user['id'] ?? 0;

        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
        $stmt->execute([$userId]);
        $notifications = $stmt->fetchAll();

        $unreadStmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $unreadStmt->execute([$userId]);
        $unreadCount = (int)$unreadStmt->fetchColumn();

        $this->view('notifications/index', [
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
            'pageTitle'     => __('notifications') ?: 'Notifications',
            'activePage'    => 'notifications',
        ]);
    }

    public function markRead(): void
    {
        $this->requireAuth();

        $db = Database::getInstance()->getConnection();
        $user = $this->user();
        $id = (int)($_GET['id'] ?? 0);

        if ($id) {
            $stmt = $db->prepare("UPDATE notifications SET is_read = 1, read_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user['id']]);
        }

        header('Location: ' . url('notifications'));
        exit;
    }

    public function markAllRead(): void
    {
        $this->requireAuth();

        $db = Database::getInstance()->getConnection();
        $user = $this->user();

        $stmt = $db->prepare("UPDATE notifications SET is_read = 1, read_at = CURRENT_TIMESTAMP WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user['id']]);

        header('Location: ' . url('notifications'));
        exit;
    }
}