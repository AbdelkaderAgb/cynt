<?php
/**
 * CYN Tourism - Auth Controller
 * 
 * Handles login, logout, and entry point routing.
 * 
 * @package CYN_Tourism
 * @version 3.0.0
 */

class AuthController extends Controller
{
    /**
     * Entry point — redirect based on auth status
     */
    public function index(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        } else {
            $this->redirect('/login');
        }
    }

    /**
     * Show login page and handle login POST
     */
    public function login(): void
    {
        // Already logged in? Go to dashboard
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = Input::email($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $errors[] = __('fill_all_fields') ?: 'Please fill in all fields';
            } else {
                $result = Auth::login($email, $password);
                if ($result['success']) {
                    $this->redirect('/dashboard');
                } else {
                    $errors[] = $result['message'];
                }
            }
        }

        $currentLang = function_exists('getCurrentLang') ? getCurrentLang() : 'en';
        $pageTitle = __('login');

        $this->viewStandalone('auth/login', compact('errors', 'currentLang', 'pageTitle'));
    }

    /**
     * Log the user out
     */
    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
