<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access - BloodLife</title>
    <link rel="stylesheet" href="login.css">
    <style>
        .error-container {
            text-align: center;
            padding: 50px 20px;
        }

        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .error-code {
            font-size: 48px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 10px;
        }

        .error-message {
            font-size: 20px;
            color: #666;
            margin-bottom: 30px;
        }

        .error-description {
            font-size: 16px;
            color: #999;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-card">
            <a href="index.html" class="home-link">
                <span class="logo-icon">🩸</span>
                <span class="logo-text">BloodLife</span>
            </a>

            <div class="error-container">
                <div class="error-icon">🚫</div>
                <div class="error-code">403</div>
                <div class="error-message">Unauthorized Access</div>
                <div class="error-description">
                    You don't have permission to access this page.<br>
                    Please login with appropriate credentials.
                </div>

                <div style="display: flex; gap: 10px; justify-content: center;">
                    <a href="login.php" class="btn btn-primary">Go to Login</a>
                    <a href="index.html" class="btn btn-secondary" style="background: #6c757d; color: white; text-decoration: none;">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
