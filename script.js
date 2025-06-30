// Esta función agrega un nuevo mensaje al área del chat
function agregarAlChat(remitente, mensaje) {
  const chat = document.getElementById("chat"); // Obtiene el contenedor del chat
  const nuevoMensaje = document.createElement("div"); // Crea un nuevo elemento div para el mensaje

  // Establece la clase CSS dependiendo de quién envía el mensaje (usuario o bot)
  nuevoMensaje.className = remitente === "Tú" ? "user-message align-self-end" : "bot-message align-self-start";

  // Reemplaza los saltos de línea con <br> para que se vean bien en HTML
  const mensajeConFormato = mensaje.replace(/\n/g, "<br>");

  // Inserta el mensaje dentro del nuevo elemento HTML
  const icono = remitente === "Tú" ? "🧍" : "🤖";
  nuevoMensaje.innerHTML = `<span class="chat-icon">${icono}</span><div><strong>${remitente}:</strong><br>${mensajeConFormato}</div>`;


  // Añade el mensaje al final del chat
  chat.appendChild(nuevoMensaje);

  // Hace scroll automático hacia abajo
  chat.scrollTop = chat.scrollHeight;
}

// Variable global para el intervalo de animación de los puntos suspensivos
let dotsInterval = null;

// Esta función se ejecuta cuando el usuario envía un mensaje
async function enviarMensaje() {
  const input = document.getElementById("input"); // Obtiene el textarea
  const mensajeUsuario = input.value.trim(); // Obtiene el mensaje del usuario sin espacios al inicio o final
  const typing = document.getElementById("typing"); // Elemento donde se muestra “Bot está escribiendo...”
  const dots = document.getElementById("dots"); // Elemento donde se animan los puntos suspensivos

  if (mensajeUsuario === "") return; // Si el campo está vacío, no hace nada

  agregarAlChat("Tú", mensajeUsuario); // Muestra el mensaje del usuario en pantalla
  input.value = ""; // Limpia el campo de entrada

  // Muestra el mensaje de que el bot está escribiendo
  typing.style.display = "block";
  dots.textContent = ".";

  // Comienza la animación de puntos (de 1 a 3 puntos repetitivos)
  dotsInterval = setInterval(() => {
    dots.textContent = dots.textContent.length >= 3 ? "." : dots.textContent + ".";
  }, 500);

  try {
    // Llama al backend (PHP) para obtener la respuesta del modelo IA
    const respuesta = await fetch("backend.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ message: mensajeUsuario }) // Envía el mensaje como JSON
    });

    // Convierte la respuesta en JSON y extrae el texto generado
    const data = await respuesta.json();
    const mensajeIA = data.choices[0].message.content.trim();

    // Agrega la respuesta del bot al chat
    agregarAlChat("Bot", mensajeIA);
  } catch (error) {
    // Si hay un error, muestra un mensaje de error
    agregarAlChat("Bot", "Lo siento, no pude responder en este momento. Inténtalo más tarde.");
    console.error("Error al contactar con el backend:", error); // Imprime el error en la consola
  } finally {
    // Detiene la animación y oculta el mensaje de “escribiendo...”
    clearInterval(dotsInterval);
    typing.style.display = "none";
  }
}

window.addEventListener("DOMContentLoaded", () => {
  setTimeout(() => {
    agregarAlChat("Bot", "¡Hola! Soy tu asistente <strong>AliadaSegura</strong> y estoy aquí para apoyarte. Cuéntame cómo te sientes o qué te preocupa.");
  }, 500); // Espera 0.5 segundos antes de aparecer
});


