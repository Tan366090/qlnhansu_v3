<?php
$loadingState = LoadingState::getInstance();
?>

<?php if ($loadingState->isLoading()): ?>
    <div class="loading-overlay" id="loading-overlay">
        <div class="loading-container">
            <?php if ($loadingState->getConfig()['showSpinner']): ?>
                <div class="loading-spinner"></div>
            <?php endif; ?>

            <?php if ($loadingState->getConfig()['showMessage']): ?>
                <div class="loading-message">
                    <?php echo htmlspecialchars($loadingState->getConfig()['message']); ?>
                </div>
            <?php endif; ?>

            <?php if ($loadingState->getConfig()['showProgress']): ?>
                <div class="loading-progress">
                    <div class="progress-bar"></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: <?php echo $loadingState->getConfig()['overlayColor']; ?>;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: <?php echo $loadingState->getConfig()['zIndex']; ?>;
        }

        .loading-container {
            text-align: center;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            min-width: 200px;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid <?php echo $loadingState->getConfig()['spinnerColor']; ?>;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        .loading-message {
            margin: 10px 0;
            color: #333;
            font-size: 16px;
        }

        .loading-progress {
            width: 100%;
            height: 4px;
            background-color: #f3f3f3;
            border-radius: 2px;
            margin-top: 10px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background-color: <?php echo $loadingState->getConfig()['progressColor']; ?>;
            width: 0;
            transition: width 0.3s ease;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 576px) {
            .loading-container {
                width: 90%;
                max-width: 300px;
            }
        }
    </style>

    <script>
        // Cập nhật thanh tiến trình
        function updateProgress(progress) {
            const progressBar = document.querySelector('.progress-bar');
            if (progressBar) {
                progressBar.style.width = progress + '%';
            }
        }

        // Cập nhật thông báo
        function updateMessage(message) {
            const messageElement = document.querySelector('.loading-message');
            if (messageElement) {
                messageElement.textContent = message;
            }
        }

        // Ẩn loading overlay
        function hideLoading() {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) {
                overlay.style.opacity = '0';
                setTimeout(() => {
                    overlay.style.display = 'none';
                }, 300);
            }
        }

        // Hiển thị loading overlay
        function showLoading() {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) {
                overlay.style.display = 'flex';
                setTimeout(() => {
                    overlay.style.opacity = '1';
                }, 10);
            }
        }
    </script>
<?php endif; ?> 