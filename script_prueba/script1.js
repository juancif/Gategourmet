const passwordInput = document.getElementById('contrasena');
const toggleCheckbox = document.getElementById('mostrar-contrasena');

toggleCheckbox.addEventListener('change', function() {
    if (this.checked) {
        passwordInput.type = 'text';
    } else {
        passwordInput.type = 'password';
    }
});

// Selecciona el elemento del campo de contraseña
const passwordField = document.getElementById('password');

// Agrega un evento 'mouseover' al campo de contraseña
passwordField.addEventListener('mouseover', function() {
    // Obtiene la información del atributo 'data-info'
    const info = passwordField.getAttribute('data-info');
    
    // Muestra la información en algún lugar (por ejemplo, una alerta)
    alert(info);
});