<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Care Plus Clinic</title>
    <link rel="stylesheet" href="/css/app.css">
    <script src="/js/auth.js"></script>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1>Welcome Back</h1>
            <p class="auth-subtitle">Login to your account to manage the clinic.</p>
            <div id="login-error" class="alert error"></div>
            <form id="login-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" class="form-control" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn">Sign In</button>
            </form>
            <div class="auth-link">
                Don't have an account? <a href="/register">Create an account</a>
            </div>

        </div>
    </div>
</body>
</html>
