<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Care Plus Clinic</title>
    <link rel="stylesheet" href="/css/app.css">
    <script src="/js/auth.js"></script>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1>Create Account</h1>
            <p class="auth-subtitle">Join us to book and manage appointments.</p>
            <div id="register-error" class="alert error"></div>
            <form id="register-form">
                <div class="form-group">
                    <label for="reg-name">Full Name</label>
                    <input type="text" id="reg-name" class="form-control" placeholder="Enter your name" required>
                </div>
                <div class="form-group">
                    <label for="reg-email">Email Address</label>
                    <input type="email" id="reg-email" class="form-control" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="reg-phone">Contact Number</label>
                    <input type="text" id="reg-phone" class="form-control" placeholder="Enter your contact number" required>
                </div>
                <div class="form-group">
                    <label for="reg-password">Password</label>
                    <input type="password" id="reg-password" class="form-control" placeholder="Create a password" required>
                </div>
                <div class="form-group">
                    <label for="reg-password-confirmation">Confirm Password</label>
                    <input type="password" id="reg-password-confirmation" class="form-control" placeholder="Confirm your password" required>
                </div>
                <button type="submit" class="btn">Sign Up</button>
            </form>
            <div class="auth-link">
                Already have an account? <a href="/login">Sign In</a>
            </div>
        </div>
    </div>
</body>
</html>
