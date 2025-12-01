<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kisora Shop')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh;
            background: linear-gradient(180deg, #E3F2FD 0%, #BBDEFB 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-container {
            width: 100%;
            max-width: 1200px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            min-height: 100vh;
        }

        .auth-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .back-button {
            position: absolute;
            top: 30px;
            left: 30px;
            z-index: 10;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 10px 20px;
            text-decoration: none;
            color: #333;
            font-size: 14px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 1);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .welcome-text {
            position: absolute;
            top: 50%;
            right: 50px;
            transform: translateY(-50%);
            z-index: 1;
            color: #1976D2;
            font-size: 32px;
            font-weight: 600;
            text-align: right;
            max-width: 300px;
            line-height: 1.3;
        }

        .auth-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            z-index: 2;
            position: relative;
        }

        .auth-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-size: 14px;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f9f9f9;
        }

        .form-input:focus {
            outline: none;
            border-color: #87CEEB;
            background: white;
            box-shadow: 0 0 0 3px rgba(135, 206, 235, 0.1);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .form-control.is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
        }

        .form-checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .form-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 10px;
            cursor: pointer;
        }

        .form-checkbox label {
            color: #555;
            font-size: 14px;
            cursor: pointer;
        }

        .auth-link {
            text-align: center;
            margin: 20px 0;
            color: #666;
            font-size: 14px;
        }

        .auth-link a {
            color: #87CEEB;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .auth-link a:hover {
            color: #6BB6D6;
        }

        .auth-button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #87CEEB 0%, #6BB6D6 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .auth-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(135, 206, 235, 0.3);
            background: linear-gradient(135deg, #6BB6D6 0%, #5AA5C4 100%);
        }

        .auth-button:active {
            transform: translateY(0);
        }

        .error-message {
            color: #d32f2f;
            font-size: 13px;
            margin-top: 5px;
        }

        .gender-group {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .gender-option {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .gender-option input[type="radio"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .gender-option label {
            color: #555;
            font-size: 14px;
            cursor: pointer;
        }

        .date-input-wrapper {
            position: relative;
        }

        .date-input-wrapper input {
            padding-right: 45px;
        }

        .calendar-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            pointer-events: none;
            font-size: 18px;
        }

        @media (max-width: 768px) {
            .welcome-text {
                display: none;
            }

            .auth-card {
                padding: 30px 20px;
            }

            .back-button {
                top: 20px;
                left: 20px;
                padding: 8px 16px;
                font-size: 12px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="auth-container">
        
        <div class="welcome-text">
            Welcome to Kisora Shop
        </div>

        <div class="auth-card">
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>

