<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ATLAS') }} - Login</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Font Awesome for Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Figtree', sans-serif;
                overflow-x: hidden;
            }

            /* Full-screen background with overlay */
            .login-container {
                min-height: 100vh;
                width: 100%;
                background-size: cover;
                background-position: center;
                background-attachment: fixed;
                display: flex;
                justify-content: center;
                align-items: center;
                position: relative;
                overflow: auto;
                background-color: #a8e6e3;
                padding: 20px 0;
            }

            /* Strong cool-toned overlay */
            .login-container::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(13, 110, 253, 0.25);
                pointer-events: none;
            }

            .login-overlay {
                position: relative;
                z-index: 10;
                width: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 20px;
            }

            .login-card {
                background: rgba(255, 255, 255, 0.08);
                backdrop-filter: blur(10px);
                border-radius: 30px;
                padding: 40px 45px;
                width: 100%;
                max-width: 420px;
                box-shadow: 0 8px 32px rgba(31, 38, 135, 0.3);
                border: 1px solid rgba(255, 255, 255, 0.18);
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 25px;
            }

            /* Profile circular placeholder */
           .profile-circle {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    /* Removed the rgba background so it doesn't show behind the image */
    background: transparent; 
    display: flex;
    justify-content: center;
    align-items: center; /* Moved this here from the img block */
}

.profile-circle img {
    width: 100%;
    height: 120%;
    border-radius: 50%;
    object-fit: cover;
   
    flex-shrink: 0;
    padding: 0; /* Changed from 8px to 0 to remove the weird gap */
}

            /* Title text */
            .login-title {
                text-align: center;
                color: white;
                font-size: 16px;
                font-weight: 700;
                letter-spacing: 0.5px;
                line-height: 1.4;
            }

            /* Form container */
            .login-form {
                width: 100%;
                display: flex;
                flex-direction: column;
                gap: 20px;
            }

            /* Input fields styling */
            .input-group {
                position: relative;
                display: flex;
                align-items: center;
                gap: 0;
            }

            .input-icon {
                position: absolute;
                left: 22px;
                color: #999;
                font-size: 18px;
                pointer-events: none;
                z-index: 2;
            }

            .form-input {
                width: 100%;
                padding: 16px 16px 16px 60px;
                border: none;
                border-radius: 50px;
                background: rgba(255, 255, 255, 0.95);
                font-size: 15px;
                color: #333;
                font-weight: 500;
                transition: all 0.3s ease;
                outline: none;
            }

            .form-input::placeholder {
                color: #aaa;
                font-weight: 400;
            }

            .form-input:focus {
                background: white;
                box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.3);
                transform: translateY(-2px);
            }

            /* Login button */
            .login-btn {
                width: 100%;
                padding: 16px 32px;
                background: linear-gradient(135deg, #3d3d3d 0%, #2a2a2a 100%);
                color: white;
                border: none;
                border-radius: 50px;
                font-size: 16px;
                font-weight: 700;
                letter-spacing: 1px;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                margin-top: 10px;
            }

            .login-btn:hover {
                background: linear-gradient(135deg, #2a2a2a 0%, #1a1a1a 100%);
                transform: translateY(-3px);
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            }

            .login-btn:active {
                transform: translateY(-1px);
            }

            /* Support links */
            .support-links {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 12px;
                margin-top: 15px;
            }

            .support-link {
                color: white;
                font-size: 13px;
                font-weight: 500;
                text-decoration: none;
                transition: all 0.3s ease;
                position: relative;
            }

            .support-link::after {
                content: '';
                position: absolute;
                bottom: -2px;
                left: 0;
                width: 0;
                height: 2px;
                background: white;
                transition: width 0.3s ease;
            }

            .support-link:hover {
                opacity: 0.8;
            }

            .support-link:hover::after {
                width: 100%;
            }

            /* Error messages styling */
            .error-message {
                color: #ff6b6b;
                font-size: 13px;
                margin-top: 8px;
                text-align: center;
            }

            /* Session status */
            .session-status {
                color: white;
                font-size: 14px;
                text-align: center;
                margin-bottom: 15px;
                padding: 12px;
                background: rgba(0, 200, 100, 0.2);
                border-radius: 8px;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .login-card {
                    padding: 40px 30px;
                    max-width: 100%;
                    margin: 0 20px;
                }

                .profile-circle {
                    width: 100px;
                    height: 100px;
                }

                .login-title {
                    font-size: 14px;
                }

                .form-input {
                    padding: 13px 13px 13px 50px;
                    font-size: 14px;
                }

                .login-btn {
                    padding: 14px 28px;
                    font-size: 15px;
                }
            }
        </style>
    </head>
    <body>
        <div class="login-container" style="background-image: url('{{ Vite::asset('resources/images/DENR_BG.jpg') }}');">
            <div class="login-overlay">
                @yield('content')
            </div>
        </div>
    </body>
</html>
