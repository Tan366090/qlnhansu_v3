<?php
$errorTitle = $errorTitle ?? 'Đã xảy ra lỗi';
$errorMessage = $errorMessage ?? 'Xin lỗi, đã có lỗi xảy ra trong quá trình xử lý.';
$errorDetails = $errorDetails ?? '';
$errorCode = $errorCode ?? 500;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($errorTitle); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .error-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 600px;
            width: 90%;
            text-align: center;
        }
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .error-title {
            color: #343a40;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        .error-message {
            color: #6c757d;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        .error-details {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            text-align: left;
            font-family: monospace;
            font-size: 0.9rem;
            color: #495057;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
            border: none;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            border: none;
        }
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        @media (max-width: 576px) {
            .error-container {
                padding: 1rem;
            }
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1 class="error-title"><?php echo htmlspecialchars($errorTitle); ?></h1>
        <p class="error-message"><?php echo htmlspecialchars($errorMessage); ?></p>
        
        <?php if (!empty($errorDetails) && (defined('APP_ENV') && APP_ENV === 'development')): ?>
            <div class="error-details">
                <strong>Chi tiết lỗi:</strong><br>
                <?php echo nl2br(htmlspecialchars($errorDetails)); ?>
            </div>
        <?php endif; ?>

        <div class="action-buttons">
            <a href="/" class="btn btn-primary">Về trang chủ</a>
            <a href="javascript:history.back()" class="btn btn-secondary">Quay lại</a>
        </div>
    </div>
</body>
</html> 