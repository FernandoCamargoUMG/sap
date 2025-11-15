# Script para probar la carga de documentos
param(
    [string]$FilePath = "test-documento.txt",
    [string]$DocumentableType = "App\\Models\\PresupuestoCab",
    [int]$DocumentableId = 1
)

Write-Host "Probando carga de documento..." -ForegroundColor Green
Write-Host "Archivo: $FilePath" -ForegroundColor Yellow
Write-Host "Tipo: $DocumentableType" -ForegroundColor Yellow
Write-Host "ID: $DocumentableId" -ForegroundColor Yellow

try {
    # Crear el formulario multipart
    $boundary = [System.Guid]::NewGuid().ToString()
    $encoding = [System.Text.Encoding]::UTF8
    
    # Leer el archivo
    if (-not (Test-Path $FilePath)) {
        Write-Host "Creando archivo de prueba..." -ForegroundColor Yellow
        "Contenido de prueba para documento PDF" | Out-File -FilePath $FilePath -Encoding utf8
    }
    
    $fileBytes = [System.IO.File]::ReadAllBytes((Resolve-Path $FilePath).Path)
    $fileName = Split-Path $FilePath -Leaf
    
    # Construir el body multipart
    $body = ""
    $body += "--$boundary`r`n"
    $body += "Content-Disposition: form-data; name=`"file`"; filename=`"$fileName`"`r`n"
    $body += "Content-Type: application/octet-stream`r`n`r`n"
    # El archivo se agregará después
    
    $body += "`r`n--$boundary`r`n"
    $body += "Content-Disposition: form-data; name=`"nombre_documento`"`r`n`r`n"
    $body += "$fileName`r`n"
    
    $body += "--$boundary`r`n"
    $body += "Content-Disposition: form-data; name=`"documentable_type`"`r`n`r`n"
    $body += "$DocumentableType`r`n"
    
    $body += "--$boundary`r`n"
    $body += "Content-Disposition: form-data; name=`"documentable_id`"`r`n`r`n"
    $body += "$DocumentableId`r`n"
    
    $body += "--$boundary`r`n"
    $body += "Content-Disposition: form-data; name=`"descripcion`"`r`n`r`n"
    $body += "Documento de prueba subido via PowerShell`r`n"
    
    $body += "--$boundary--`r`n"
    
    # Convertir a bytes
    $bodyBytes = $encoding.GetBytes($body)
    
    # Insertar los bytes del archivo en la posición correcta
    $part1 = $encoding.GetBytes($body.Substring(0, $body.IndexOf("Content-Type: application/octet-stream`r`n`r`n") + "Content-Type: application/octet-stream`r`n`r`n".Length))
    $part2 = $encoding.GetBytes($body.Substring($body.IndexOf("`r`n--$boundary`r`n")))
    
    $finalBody = $part1 + $fileBytes + $part2
    
    # Hacer la petición
    $response = Invoke-WebRequest -Uri "http://localhost:8000/api/documentos" -Method POST -Body $finalBody -ContentType "multipart/form-data; boundary=$boundary"
    
    Write-Host "Respuesta exitosa!" -ForegroundColor Green
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "Contenido: $($response.Content)" -ForegroundColor White
    
} catch {
    Write-Host "Error en la petición:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $responseContent = $reader.ReadToEnd()
        Write-Host "Respuesta del servidor: $responseContent" -ForegroundColor Yellow
    }
}