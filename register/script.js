const passwordInput = document.getElementById('contrasena');
const toggleCheckbox = document.getElementById('mostrar-contrasena');

toggleCheckbox.addEventListener('change', function() {
    if (this.checked) {
        passwordInput.type = 'text';
    } else {
        passwordInput.type = 'password';
    }
});