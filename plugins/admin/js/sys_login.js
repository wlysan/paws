document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const passwordInput = button.previousElementSibling;
            const icon = button.querySelector('i');
            
            // Toggle password visibility
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Form validation
    const loginForm = document.querySelector('.login-form');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            let isValid = true;
            
            // Simple email validation
            if (!validateEmail(email)) {
                showError('email', 'Please enter a valid email address');
                isValid = false;
            } else {
                clearError('email');
            }
            
            // Simple password validation
            if (password.length < 1) {
                showError('password', 'Password is required');
                isValid = false;
            } else {
                clearError('password');
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
    }
    
    // Email validation function
    function validateEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
    
    // Show error message
    function showError(inputId, message) {
        const input = document.getElementById(inputId);
        input.classList.add('is-invalid');
        
        // Check if error message exists
        let errorMessage = input.parentElement.nextElementSibling;
        if (!errorMessage || !errorMessage.classList.contains('invalid-feedback')) {
            errorMessage = document.createElement('div');
            errorMessage.classList.add('invalid-feedback');
            input.parentElement.after(errorMessage);
        }
        
        errorMessage.textContent = message;
    }
    
    // Clear error message
    function clearError(inputId) {
        const input = document.getElementById(inputId);
        input.classList.remove('is-invalid');
        
        // Check if error message exists
        const errorMessage = input.parentElement.nextElementSibling;
        if (errorMessage && errorMessage.classList.contains('invalid-feedback')) {
            errorMessage.textContent = '';
        }
    }
    
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.add('fade');
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
});