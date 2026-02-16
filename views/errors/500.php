<?php
/**
 * 500 Internal Server Error Page
 * 
 * @package CYN_Tourism
 */

http_response_code(500);

// Try to load language if available
$lang = [];
if (file_exists(__DIR__ . '/../languages/en.php')) {
    $lang = require __DIR__ . '/../languages/en.php';
}

function __e($key, $default = '') {
    global $lang;
    return isset($lang[$key]) ? $lang[$key] : $default;
}

$pageTitle = __e('server_error', 'Server Error');

// Log the error if possible
if (file_exists(__DIR__ . '/../Logger.php')) {
    require_once __DIR__ . '/../Logger.php';
    Logger::error('500 Internal Server Error', [
        'uri' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'unknown',
        'method' => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'unknown',
        'referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'none'
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - <?php echo htmlspecialchars($pageTitle); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
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
            color: #ee5a24;
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
            background: #ee5a24;
            color: white;
        }
        
        .btn-primary:hover {
            background: #d94d1a;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(238, 90, 36, 0.4);
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
        
        .support-info {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 14px;
            color: #888;
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
        <div class="error-icon">⚠️</div>
        <div class="error-code">500</div>
        <h1 class="error-title"><?php echo htmlspecialchars(__e('server_error', 'Server Error')); ?></h1>
        <p class="error-message">
            <?php echo htmlspecialchars(__e('server_error_message', 'Something went wrong on our end. We are working to fix the issue. Please try again later.')); ?>
        </p>
        <div class="error-actions">
            <a href="/" class="btn btn-primary">
                <span>🏠</span> <?php echo htmlspecialchars(__e('go_home', 'Go to Homepage')); ?>
            </a>
            <a href="javascript:location.reload()" class="btn btn-secondary">
                <span>🔄</span> <?php echo htmlspecialchars(__e('refresh', 'Refresh Page')); ?>
            </a>
        </div>
        <div class="support-info">
            <p>If the problem persists, please contact support.</p>
            <p>Error ID: <?php echo uniqid('ERR_'); ?></p>
        </div>
    </div>
</body>
</html>
