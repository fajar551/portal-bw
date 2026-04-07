<!-- resources/views/errors/404.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found</title>
    <style>
        body {
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Arial', sans-serif;
            overflow: hidden;
        }
        .container {
            text-align: center;
            position: relative;
            z-index: 10;
        }
        h1 {
            font-size: 5em;
            margin: 0;
            color: #ff7a3d;
            animation: drop 1s ease-out forwards, bounce 1s infinite 1s;
        }
        p {
            font-size: 1.5em;
            color: #333;
            margin-bottom: 20px;
            animation: slideIn 1s ease-out forwards;
            animation-delay: 0.5s;
            opacity: 0;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #ff7a3d;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            animation: rise 1s ease-out forwards;
            animation-delay: 1s;
            opacity: 0;
        }
        a:hover {
            background-color: #e66a2c;
        }
        @keyframes drop {
            0% {
                transform: translateY(-100px);
                opacity: 0;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-30px);
            }
            60% {
                transform: translateY(-15px);
            }
        }
        @keyframes slideIn {
            0% {
                transform: translateX(-100px);
                opacity: 0;
            }
            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes rise {
            0% {
                transform: translateY(100px);
                opacity: 0;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }
        .cloud {
            position: absolute;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            animation: float 6s ease-in-out infinite;
            z-index: 1;
        }
        .cloud:before, .cloud:after {
            content: '';
            position: absolute;
            background: #fff;
            border-radius: 50%;
        }
        .cloud:before {
            width: 60px;
            height: 60px;
            top: -30px;
            left: 10px;
        }
        .cloud:after {
            width: 100px;
            height: 100px;
            top: -50px;
            right: 10px;
        }
        @keyframes float {
            0% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-20px);
            }
            100% {
                transform: translateY(0);
            }
        }
        .cloud1 {
            width: 120px;
            height: 60px;
            top: 10%;
            left: 10%;
            animation-duration: 8s;
        }
        .cloud2 {
            width: 180px;
            height: 90px;
            top: 10%;
            right: 10%;
            animation-duration: 10s;
        }
        .cloud3 {
            width: 150px;
            height: 75px;
            bottom: 10%;
            left: 20%;
            animation-duration: 12s;
        }
        .cloud4 {
            width: 130px;
            height: 65px;
            bottom: 5%;
            right: 5%;
            animation-duration: 9s;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>404</h1>
        <p>Oops! The page you're looking for doesn't exist.</p>
        <a href="{{ request()->is('admin/*') ? url('/admin/dashboard') : url('/home') }}">Go Home</a>
    </div>
    <div class="cloud cloud1"></div>
    <div class="cloud cloud2"></div>
    <div class="cloud cloud3"></div>
    <div class="cloud cloud4"></div>
</body>
</html>