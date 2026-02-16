<?php
/**
 * CYN Tourism — UserController
 */
class UserController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;
        $search  = $_GET['search'] ?? '';

        $where  = [];
        $params = [];
        if ($search) {
            $where[]  = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
            $s = "%$search%";
            $params[] = $s; $params[] = $s; $params[] = $s;
        }
        $wc = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM users $wc", $params)['c'] ?? 0);
        $users = Database::fetchAll(
            "SELECT id, first_name, last_name, email, role, status, last_login, created_at
             FROM users $wc ORDER BY created_at DESC LIMIT $perPage OFFSET $offset",
            $params
        );

        $this->view('users/index', [
            'users'      => $users,
            'total'      => $total,
            'page'       => $page,
            'pages'      => (int)ceil($total / $perPage),
            'search'     => $search,
            'pageTitle'  => 'Kullanıcı Yönetimi',
            'activePage' => 'users',
        ]);
    }

    public function create(): void
    {
        Auth::requireAdmin();
        $this->view('users/form', [
            'user'       => [],
            'isEdit'     => false,
            'pageTitle'  => 'Yeni Kullanıcı',
            'activePage' => 'users',
        ]);
    }

    public function store(): void
    {
        Auth::requireAdmin();

        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name'  => trim($_POST['last_name'] ?? ''),
            'email'      => trim($_POST['email'] ?? ''),
            'role'       => $_POST['role'] ?? 'viewer',
            'status'     => $_POST['status'] ?? 'active',
        ];

        $id = (int)($_POST['id'] ?? 0);

        if (!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        if ($id) {
            $sets = []; $params = [];
            foreach ($data as $k => $v) { $sets[] = "$k = ?"; $params[] = $v; }
            $params[] = $id;
            Database::execute("UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?", $params);
        } else {
            if (empty($data['password'])) {
                $data['password'] = password_hash('changeme123', PASSWORD_DEFAULT);
            }
            $data['created_at'] = date('Y-m-d H:i:s');
            Database::getInstance()->insert('users', $data);
        }

        header('Location: ' . url('users') . '?saved=1');
        exit;
    }

    public function edit(): void
    {
        Auth::requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $user = Database::fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) { header('Location: ' . url('users')); exit; }

        $this->view('users/form', [
            'user'       => $user,
            'isEdit'     => true,
            'pageTitle'  => 'Düzenle: ' . $user['first_name'],
            'activePage' => 'users',
        ]);
    }

    public function profile(): void
    {
        $userId = $_SESSION['user_id'];
        $user = Database::fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);

        $this->view('users/profile', [
            'user'       => $user,
            'pageTitle'  => 'Profilim',
            'activePage' => 'profile',
        ]);
    }

    public function updateProfile(): void
    {
        $userId = $_SESSION['user_id'];
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name'  => trim($_POST['last_name'] ?? ''),
            'phone'      => trim($_POST['phone'] ?? ''),
        ];

        if (!empty($_POST['password']) && !empty($_POST['password_confirm'])) {
            if ($_POST['password'] === $_POST['password_confirm']) {
                $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
        }

        $sets = []; $params = [];
        foreach ($data as $k => $v) { $sets[] = "$k = ?"; $params[] = $v; }
        $params[] = $userId;
        Database::execute("UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?", $params);

        $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
        header('Location: ' . url('profile') . '?saved=1');
        exit;
    }
}
