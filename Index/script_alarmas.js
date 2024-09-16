document.addEventListener('DOMContentLoaded', function() {
    const botonAlarmas = document.getElementById('alarmas'); // Botón "Mostrar Alarmas"
    const menuDesplegable = document.getElementById('abirMenu'); // Menú desplegable
    const botonCerrar = document.getElementById('cerrarMenu'); // Botón "X" para cerrar el menú
  
    // Muestra u oculta el menú al hacer clic en el botón "Mostrar Alarmas"
    botonAlarmas.addEventListener('click', function() {
      menuDesplegable.classList.toggle('desplegar');
    });
  
    // Oculta el menú al hacer clic en la "X"
    botonCerrar.addEventListener('click', function() {
      menuDesplegable.classList.add('desplegar');
    });
  
    // Oculta el menú al hacer clic fuera del menú desplegable
    document.addEventListener('click', function(event) {
      if (!menuDesplegable.contains(event.target) && !botonAlarmas.contains(event.target)) {
        menuDesplegable.classList.add('desplegar');
      }
    });
  });
  