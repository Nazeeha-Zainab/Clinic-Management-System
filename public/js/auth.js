document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    
    // Allow users to see the login page even if they have a token in localStorage.
    // If they want to switch accounts, they can log in again.

    // Handle Login
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorAlert = document.getElementById('login-error');
            
            try {
                const response = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    localStorage.setItem('token', data.authorisation.token);
                    localStorage.setItem('user', JSON.stringify(data.user));
                    errorAlert.style.display = 'none';
                    
                    if (data.user.role === 'admin') {
                        window.location.href = '/admin/dashboard';
                    } else if (data.user.role === 'patient') {
                        window.location.href = '/patient/dashboard';
                    } else if (data.user.role === 'receptionist') {
                        window.location.href = '/receptionist/dashboard';
                    } else if (data.user.role === 'doctor') {
                        window.location.href = '/doctor/dashboard';
                    } else {
                        window.location.href = '/dashboard';
                    }
                } else {
                    errorAlert.textContent = data.message || 'Login failed';
                    errorAlert.style.display = 'block';
                }
            } catch (err) {
                errorAlert.textContent = 'Network error. Please try again.';
                errorAlert.style.display = 'block';
            }
        });
    }

    // Handle Registration
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('reg-name').value;
            const email = document.getElementById('reg-email').value;
            const phone = document.getElementById('reg-phone').value;
            const password = document.getElementById('reg-password').value;
            const password_confirmation = document.getElementById('reg-password-confirmation').value;
            const errorAlert = document.getElementById('register-error');
            
            try {
                const response = await fetch('/api/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name, email, phone, password, password_confirmation })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    localStorage.setItem('token', data.authorisation.token);
                    localStorage.setItem('user', JSON.stringify(data.user));
                    errorAlert.style.display = 'none';
                    window.location.href = '/patient/dashboard';
                } else {
                    errorAlert.textContent = data.message || 'Registration failed';
                    errorAlert.style.display = 'block';
                }
            } catch (err) {
                errorAlert.textContent = 'Network error. Please try again.';
                errorAlert.style.display = 'block';
            }
        });
    }
});
