<?php
/**
 * Maintenance Mode Page
 * 
 * @package CYN_Tourism
 */

http_response_code(503);
header('Retry-After: 3600'); // Suggest retry after 1 hour

// Try to load language if available
$lang = [];
if (file_exists(__DIR__ . '/../languages/en.php')) {
    $lang = require __DIR__ . '/../languages/en.php';
}

function __e($key, $default = '') {
    global $lang;
    return isset($lang[$key]) ? $lang[$key] : $default;
}

$pageTitle = __e('maintenance_title', 'Under Maintenance');

// Check if admin access is allowed
$allowAdmin = false;
if (file_exists(__DIR__ . '/../config.php')) {
    require_once __DIR__ . '/../config.php';
    if (defined('MAINTENANCE_ALLOW_ADMIN') && MAINTENANCE_ALLOW_ADMIN) {
        // Check if user is logged in as admin
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            $allowAdmin = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }
        
        .maintenance-container {
            background: white;
            border-radius: 20px;
            padding: 60px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 90%;
        }
        
        .maintenance-icon {
            font-size: 100px;
            margin-bottom: 30px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
        
        .maintenance-title {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
        }
        
        .maintenance-message {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .maintenance-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: left;
        }
        
        .maintenance-details h3 {
            font-size: 16px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .maintenance-details ul {
            list-style: none;
            padding-left: 0;
        }
        
        .maintenance-details li {
            padding: 5px 0;
            color: #666;
            font-size: 14px;
        }
        
        .maintenance-details li:before {
            content: "✓ ";
            color: #22c55e;
            font-weight: bold;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            width: 70%;
            animation: progress 2s ease-in-out infinite;
        }
        
        @keyframes progress {
            0% {
                width: 0%;
            }
            50% {
                width: 70%;
            }
            100% {
                width: 100%;
            }
        }
        
        .contact-info {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .contact-info h4 {
            font-size: 14px;
            color: #888;
            margin-bottom: 10px;
        }
        
        .contact-info p {
            font-size: 14px;
            color: #666;
        }
        
        .admin-login {
            margin-top: 20px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a6fd6;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .estimated-time {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 480px) {
            .maintenance-container {
                padding: 40px 30px;
            }
            
            .maintenance-title {
                font-size: 24px;
            }
            
            .maintenance-message {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-icon">🔧</div>
        <h1 class="maintenance-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
        
        <div class="estimated-time">
            ⏱️ <?php echo __e('estimated_time', 'Estimated completion: Soon'); ?>
        </div>
        
        <p class="maintenance-message">
            <?php echo htmlspecialchars(__e('maintenance_message', 'We are currently performing scheduled maintenance to improve our services. We will be back shortly.')); ?>
        </p>
        
        <div class="progress-bar">
            <div class="progress-bar-fill"></div>
        </div>
        
        <div class="maintenance-details">
            <h3>📝 <?php echo __e('what_we_are_doing', 'What we are working on:'); ?></h3>
            <ul>
                <li><?php echo __e('maintenance_item_1', 'System updates and improvements'); ?></li>
                <li><?php echo __e('maintenance_item_2', 'Security enhancements'); ?></li>
                <li><?php echo __e('maintenance_item_3', 'Performance optimization'); ?></li>
                <li><?php echo __e('maintenance_item_4', 'Database maintenance'); ?></li>
            </ul>
        </div>
        
        <div class="contact-info">
            <h4><?php echo __e('need_help', 'Need urgent assistance?'); ?></h4>
            <p>
                <?php if (defined('COMPANY_EMAIL')): ?>
                    📧 <?php echo COMPANY_EMAIL; ?><br>
                <?php endif; ?>
                <?php if (defined('COMPANY_PHONE')): ?>
                    📞 <?php echo COMPANY_PHONE; ?>
                <?php endif; ?>
            </p>
        </div>
        
        <?php if ($allowAdmin): ?>
        <div class="admin-login">
            <a href="/login.php" class="btn btn-primary">
                <span>🔐</span> <?php echo __e('admin_access', 'Admin Access'); ?>
            </a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
