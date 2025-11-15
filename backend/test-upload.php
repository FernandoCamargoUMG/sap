<?php
// Script para probar la carga de documentos directamente

// Crear archivo de prueba si no existe
$testFile = 'test-documento.txt';
if (!file_exists($testFile)) {
    file_put_contents($testFile, 'Contenido de prueba para documento');
}

// Preparar los datos para curl
$postData = [
    'file' => new CURLFile(realpath($testFile), 'text/plain', 'test-documento.txt'),
    'nombre_documento' => 'Documento de prueba',
    'documentable_type' => 'App\\Models\\PresupuestoCab',
    'documentable_id' => '1',
    'descripcion' => 'Documento de prueba subido via PHP'
];

// Inicializar curl
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/documentos');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json'
]);

echo "Enviando petición a la API...\n";
echo "Datos enviados:\n";
print_r([
    'file' => 'test-documento.txt',
    'nombre_documento' => 'Documento de prueba',
    'documentable_type' => 'App\\Models\\PresupuestoCab',
    'documentable_id' => '1',
    'descripcion' => 'Documento de prueba subido via PHP'
]);

// Ejecutar la petición
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "\nRespuesta HTTP: $httpCode\n";
echo "Contenido de respuesta:\n$response\n";

if (curl_error($ch)) {
    echo "Error de cURL: " . curl_error($ch) . "\n";
}

curl_close($ch);
?>