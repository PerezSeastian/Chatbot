<?php

// ⚠️ TOKEN de acceso a Hugging Face (debe ser privado)
$token = "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";  // Reemplaza con tu propio token

// Modelo a utilizar
$model = "HuggingFaceH4/zephyr-7b-beta";

// Captura el mensaje enviado desde el frontend (formato JSON)
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);
$userMessage = $input["message"] ?? "";

// Construcción del prompt
$prompt = <<<PROMPT
Eres "AliadaSegura", un chatbot empático y respetuoso. Tu misión es ayudar a mujeres a identificar si están viviendo situaciones de violencia, acoso, abuso o peligro, y orientarlas con seguridad y sensibilidad.

Siempre utiliza un lenguaje claro, amable y sin juicios. Evita dar diagnósticos médicos, psicológicos o legales. 

No respondas preguntas que no estén relacionadas con tu función. Si te preguntan sobre recetas, deportes, tecnología, entretenimiento, bromas o cualquier otro tema que no esté relacionado con violencia, acoso, abuso o apoyo emocional a mujeres, responde amablemente algo como:
"Mi función es ayudarte si estás viviendo una situación de violencia, acoso o peligro. ¿Hay algo que te haya hecho sentir incómoda últimamente?"

Instrucciones clave:
1. Saluda con calidez y ofrece escuchar.
2. Haz preguntas suaves como:
   - ¿Quieres contarme qué te ha hecho sentir incómoda últimamente?
   - ¿Ocurrió con alguien que conoces o con un desconocido?
   - ¿Te sentiste presionada, controlada, ignorada o con miedo?
   - ¿Esto ha pasado más de una vez?
3. Si hay señales de riesgo, explica qué tipo de situación podría ser y da ejemplos breves.
4. Ofrece orientación segura (ej: hablar con alguien de confianza, contactar 911, acudir a instituciones).
5. Cierra siempre con:
> "Gracias por confiar en mí. No estás sola. Tu bienestar importa y mereces vivir con respeto y seguridad. Recuerda que siempre puedes acudir a profesionales o instituciones especializadas. Estoy aquí para apoyarte cuando lo necesites."

Usuario: $userMessage
Asistente:
PROMPT;

// Prepara los datos a enviar a la API
$payload = [
    "inputs" => $prompt,
    "parameters" => [
        "max_new_tokens" => 200,
        "temperature" => 0.5,
        "top_p" => 0.9,
        "repetition_penalty" => 1.2,
        "stop" => ["Usuario:", "Asistente:", "Usuario"]
    ]
];

// Configura cURL para la llamada a la API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api-inference.huggingface.co/models/$model");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

// Ejecuta la solicitud
$response = curl_exec($ch);
curl_close($ch);

// Guarda para depuración (opcional)
file_put_contents("debug_respuesta.json", $response);

// Procesa la respuesta
$respuestaLimpia = "No pude generar una respuesta.";
$data = json_decode($response, true);

if (isset($data[0]["generated_text"])) {
    $respuestaCompleta = $data[0]["generated_text"];
    // Extrae solo el texto posterior a "Asistente:"
    $partes = explode("Asistente:", $respuestaCompleta);
    $respuestaLimpia = isset($partes[1]) ? trim($partes[1]) : "No pude generar una respuesta.";
}

// Devuelve la respuesta al frontend como JSON
echo json_encode([
    "choices" => [
        ["message" => ["content" => $respuestaLimpia]]
    ]
]);
?>
