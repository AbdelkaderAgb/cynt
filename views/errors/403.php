<?php
/**
 * 403 Forbidden Error Page
 * 
 * @package CYN_Tourism
 */

http_response_code(403);

// Try to load language if available
$lang = [];
if (file_exists(__DIR__ . '/../languages/en.php')) {
    $lang = require __DIR__ . '/../languages/en.php';
}

function __e($key, $default = '') {
    global $lang;
    return isset($lang[$key]) ? $lang[$key] : $default;
}

$pageTitle = __e('access_denied', 'Access Denied');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - <?php echo htmlspecialchars($pageTitle); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }
        
        .error-container {
            background: white;
            border-radius: 20px;
            padding: 60px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
        }
        
        .error-code {
            font-size: 120px;
            font-weight: 700;
            color: #f5576c;
            line-height: 1;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .error-title {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }
        
        .error-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .error-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
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
            background: #f5576c;
            color: white;
        }
        
        .btn-primary:hover {
            background: #e0455a;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(245, 87, 108, 0.4);
        }
        
        .btn-secondary {
            background: #f3f4f6;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #e5e7eb;
        }
        
        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 480px) {
            .error-container {
                padding: 40px 30px;
            }
            
            .error-code {
                font-size: 80px;
            }
            
            .error-title {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">🚫</div>
        <div class="error-code">403</div>
        <h1 class="error-title"><?php echo htmlspecialchars(__e('access_denied', 'Access Denied')); ?></h1>
        <p class="error-message">
            <?php echo htmlspecialchars(__e('access_denied_message', 'You do not have permission to access this page. Please contact your administrator if you believe this is an error.')); ?>
        </p>
        <div class="error-actions">
            <a href="/" class="btn btn-primary">
                <span>🏠</span> <?php echo htmlspecialchars(__e('go_home', 'Go to Homepage')); ?>
            </a>
            <a href="javascript:history.back()" class="btn btn-secondary">
                <span>←</span> <?php echo htmlspecialchars(__e('go_back', 'Go Back')); ?>
            </a>
        </div>
    </div>
</body>
</html>
