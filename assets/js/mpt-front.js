(function() {

    const passwordButtons = document.querySelectorAll('.mpt-password-button');
    
    passwordButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const passwordField = button.previousElementSibling;
            const isPasswordVisible = passwordField.getAttribute('type') === 'text';
            
            passwordField.setAttribute('type', isPasswordVisible ? 'password' : 'text');
            button.setAttribute('aria-pressed', !isPasswordVisible);
            button.setAttribute(
                'aria-label',
                isPasswordVisible ? button.getAttribute('data-show') : button.getAttribute('data-hide')
            );
        });
    });
})();
