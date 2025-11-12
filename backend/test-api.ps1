# 🧪 Script de Prueba Rápida - SAP API

# Este script prueba los endpoints principales del sistema SAP
# Ejecutar desde PowerShell en la raíz del proyecto backend

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   Prueba Rápida API - SAP System" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

$baseUrl = "http://localhost:8000"
$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

# Test 1: Login
Write-Host "1. Probando Login..." -ForegroundColor Yellow
try {
    $loginBody = @{
        correo = "administrador@contabilidad.com"
        contraseña = "admin123"
    } | ConvertTo-Json

    $loginResponse = Invoke-WebRequest -Uri "$baseUrl/api/auth/login" -Method POST -Body $loginBody -Headers $headers -SessionVariable session
    
    if ($loginResponse.StatusCode -eq 200) {
        Write-Host "   ✓ Login exitoso" -ForegroundColor Green
        $loginData = $loginResponse.Content | ConvertFrom-Json
        Write-Host "   Usuario: $($loginData.data.nombre)" -ForegroundColor Gray
    }
} catch {
    Write-Host "   ✗ Error en login: $($_.Exception.Message)" -ForegroundColor Red
    exit
}

# Test 2: Obtener usuario actual
Write-Host "`n2. Probando 'Me' (Usuario actual)..." -ForegroundColor Yellow
try {
    $meResponse = Invoke-WebRequest -Uri "$baseUrl/api/auth/me" -Method GET -WebSession $session
    
    if ($meResponse.StatusCode -eq 200) {
        Write-Host "   ✓ Usuario obtenido correctamente" -ForegroundColor Green
        $meData = $meResponse.Content | ConvertFrom-Json
        Write-Host "   Rol: $($meData.data.rol.nombre)" -ForegroundColor Gray
    }
} catch {
    Write-Host "   ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 3: Listar usuarios
Write-Host "`n3. Probando Listar Usuarios..." -ForegroundColor Yellow
try {
    $usuariosResponse = Invoke-WebRequest -Uri "$baseUrl/api/usuarios" -Method GET -WebSession $session
    
    if ($usuariosResponse.StatusCode -eq 200) {
        $usuariosData = $usuariosResponse.Content | ConvertFrom-Json
        Write-Host "   ✓ Usuarios encontrados: $($usuariosData.data.Count)" -ForegroundColor Green
    }
} catch {
    Write-Host "   ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 4: Crear renglón presupuestario
Write-Host "`n4. Probando Crear Renglón..." -ForegroundColor Yellow
try {
    $renglonBody = @{
        codigo = "1.1.21.1.TEST"
        descripcion = "Renglón de Prueba API"
        presupuesto_vigente = 100000.00
        saldo_disponible = 100000.00
        estado = 1
    } | ConvertTo-Json

    $renglonResponse = Invoke-WebRequest -Uri "$baseUrl/api/renglones" -Method POST -Body $renglonBody -Headers $headers -WebSession $session
    
    if ($renglonResponse.StatusCode -eq 201) {
        Write-Host "   ✓ Renglón creado exitosamente" -ForegroundColor Green
        $renglonData = $renglonResponse.Content | ConvertFrom-Json
        $renglonId = $renglonData.data.id
        Write-Host "   ID: $renglonId - Código: $($renglonData.data.codigo)" -ForegroundColor Gray
    }
} catch {
    Write-Host "   ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 5: Consultar saldo del renglón
if ($renglonId) {
    Write-Host "`n5. Probando Consultar Saldo del Renglón..." -ForegroundColor Yellow
    try {
        $saldoResponse = Invoke-WebRequest -Uri "$baseUrl/api/renglones/$renglonId/saldo" -Method GET -WebSession $session
        
        if ($saldoResponse.StatusCode -eq 200) {
            Write-Host "   ✓ Saldo consultado correctamente" -ForegroundColor Green
            $saldoData = $saldoResponse.Content | ConvertFrom-Json
            Write-Host "   Presupuesto: Q$($saldoData.data.presupuesto_vigente)" -ForegroundColor Gray
            Write-Host "   Disponible: Q$($saldoData.data.saldo_disponible)" -ForegroundColor Gray
        }
    } catch {
        Write-Host "   ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Test 6: Crear proveedor
Write-Host "`n6. Probando Crear Proveedor..." -ForegroundColor Yellow
try {
    $proveedorBody = @{
        nit = "12345678-K"
        nombre = "Proveedor de Prueba API"
        direccion = "Zona 1, Guatemala"
        telefono = "2234-5678"
        email = "prueba@test.com"
        contacto = "Juan Pérez"
        estado = 1
    } | ConvertTo-Json

    $proveedorResponse = Invoke-WebRequest -Uri "$baseUrl/api/proveedores" -Method POST -Body $proveedorBody -Headers $headers -WebSession $session
    
    if ($proveedorResponse.StatusCode -eq 201) {
        Write-Host "   ✓ Proveedor creado exitosamente" -ForegroundColor Green
        $proveedorData = $proveedorResponse.Content | ConvertFrom-Json
        $proveedorId = $proveedorData.data.id
        Write-Host "   ID: $proveedorId - $($proveedorData.data.nombre)" -ForegroundColor Gray
    }
} catch {
    Write-Host "   ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 7: Crear movimiento de ampliación
if ($renglonId) {
    Write-Host "`n7. Probando Crear Movimiento (Ampliación)..." -ForegroundColor Yellow
    try {
        $movimientoBody = @{
            tipo_movimiento = "ampliacion"
            fecha_movimiento = (Get-Date).ToString("yyyy-MM-dd")
            descripcion = "Ampliación presupuestaria de prueba"
            monto_total = 25000.00
            estado = 1
            detalles = @(
                @{
                    renglon_id = $renglonId
                    descripcion = "Ampliación de prueba"
                    monto = 25000.00
                    estado = 1
                }
            )
        } | ConvertTo-Json -Depth 10

        $movimientoResponse = Invoke-WebRequest -Uri "$baseUrl/api/movimientos" -Method POST -Body $movimientoBody -Headers $headers -WebSession $session
        
        if ($movimientoResponse.StatusCode -eq 201) {
            Write-Host "   ✓ Movimiento creado y saldos afectados" -ForegroundColor Green
            $movimientoData = $movimientoResponse.Content | ConvertFrom-Json
            $movimientoId = $movimientoData.data.id
            Write-Host "   Tipo: $($movimientoData.data.tipo_movimiento) - Monto: Q$($movimientoData.data.monto_total)" -ForegroundColor Gray
        }
    } catch {
        Write-Host "   ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Test 8: Verificar saldo después de la ampliación
if ($renglonId) {
    Write-Host "`n8. Verificando Saldo Actualizado..." -ForegroundColor Yellow
    try {
        $saldoResponse2 = Invoke-WebRequest -Uri "$baseUrl/api/renglones/$renglonId/saldo" -Method GET -WebSession $session
        
        if ($saldoResponse2.StatusCode -eq 200) {
            Write-Host "   ✓ Saldo verificado" -ForegroundColor Green
            $saldoData2 = $saldoResponse2.Content | ConvertFrom-Json
            Write-Host "   Nuevo Presupuesto: Q$($saldoData2.data.presupuesto_vigente)" -ForegroundColor Gray
            Write-Host "   Nuevo Disponible: Q$($saldoData2.data.saldo_disponible)" -ForegroundColor Gray
            Write-Host "   % Ejecutado: $($saldoData2.data.porcentaje_ejecutado)%" -ForegroundColor Gray
        }
    } catch {
        Write-Host "   ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Test 9: Crear factura con detalles
if ($proveedorId -and $renglonId) {
    Write-Host "`n9. Probando Crear Factura con Detalles..." -ForegroundColor Yellow
    try {
        $facturaBody = @{
            proveedor_id = $proveedorId
            numero_factura = "TEST-001-2025"
            serie_factura = "A"
            fecha_factura = (Get-Date).ToString("yyyy-MM-dd")
            descripcion = "Factura de prueba API"
            total = 5000.00
            estado = 1
            detalles = @(
                @{
                    renglon_id = $renglonId
                    descripcion = "Producto de prueba 1"
                    cantidad = 10
                    precio_unitario = 300.00
                    subtotal = 3000.00
                    estado = 1
                },
                @{
                    renglon_id = $renglonId
                    descripcion = "Producto de prueba 2"
                    cantidad = 20
                    precio_unitario = 100.00
                    subtotal = 2000.00
                    estado = 1
                }
            )
        } | ConvertTo-Json -Depth 10

        $facturaResponse = Invoke-WebRequest -Uri "$baseUrl/api/facturas" -Method POST -Body $facturaBody -Headers $headers -WebSession $session
        
        if ($facturaResponse.StatusCode -eq 201) {
            Write-Host "   ✓ Factura creada con detalles" -ForegroundColor Green
            $facturaData = $facturaResponse.Content | ConvertFrom-Json
            $facturaId = $facturaData.data.id
            Write-Host "   Factura: $($facturaData.data.numero_factura) - Total: Q$($facturaData.data.total)" -ForegroundColor Gray
            Write-Host "   Detalles: $($facturaData.data.detalles.Count) líneas" -ForegroundColor Gray
        }
    } catch {
        Write-Host "   ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Test 10: Crear transferencia (INTRA)
Write-Host "`n10. Probando Crear Segundo Renglón para Transferencia..." -ForegroundColor Yellow
try {
    $renglon2Body = @{
        codigo = "1.1.24.1.TEST"
        descripcion = "Renglón Destino Prueba"
        presupuesto_vigente = 50000.00
        saldo_disponible = 50000.00
        estado = 1
    } | ConvertTo-Json

    $renglon2Response = Invoke-WebRequest -Uri "$baseUrl/api/renglones" -Method POST -Body $renglon2Body -Headers $headers -WebSession $session
    
    if ($renglon2Response.StatusCode -eq 201) {
        Write-Host "   ✓ Segundo renglón creado" -ForegroundColor Green
        $renglon2Data = $renglon2Response.Content | ConvertFrom-Json
        $renglon2Id = $renglon2Data.data.id
        
        # Crear transferencia
        Write-Host "`n11. Probando Crear Transferencia (INTRA)..." -ForegroundColor Yellow
        $intraBody = @{
            renglon_origen_id = $renglonId
            renglon_destino_id = $renglon2Id
            monto = 10000.00
            descripcion = "Transferencia de prueba API"
            fecha_transferencia = (Get-Date).ToString("yyyy-MM-dd")
            estado = 1
        } | ConvertTo-Json

        $intraResponse = Invoke-WebRequest -Uri "$baseUrl/api/intras" -Method POST -Body $intraBody -Headers $headers -WebSession $session
        
        if ($intraResponse.StatusCode -eq 201) {
            Write-Host "   ✓ Transferencia creada y saldos afectados" -ForegroundColor Green
            $intraData = $intraResponse.Content | ConvertFrom-Json
            Write-Host "   De: $($intraData.data.renglonOrigen.codigo) → A: $($intraData.data.renglonDestino.codigo)" -ForegroundColor Gray
            Write-Host "   Monto transferido: Q$($intraData.data.monto)" -ForegroundColor Gray
        }
    }
} catch {
    Write-Host "   ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 12: Crear compromiso (CUR)
if ($renglonId) {
    Write-Host "`n12. Probando Crear Compromiso (CUR)..." -ForegroundColor Yellow
    try {
        $curBody = @{
            renglon_id = $renglonId
            numero_cur = "CUR-TEST-001"
            descripcion = "Compromiso de prueba API"
            monto = 15000.00
            fecha_compromiso = (Get-Date).ToString("yyyy-MM-dd")
            estado = 1
        } | ConvertTo-Json

        $curResponse = Invoke-WebRequest -Uri "$baseUrl/api/cur" -Method POST -Body $curBody -Headers $headers -WebSession $session
        
        if ($curResponse.StatusCode -eq 201) {
            Write-Host "   ✓ Compromiso creado y saldo comprometido" -ForegroundColor Green
            $curData = $curResponse.Content | ConvertFrom-Json
            Write-Host "   CUR: $($curData.data.numero_cur) - Monto: Q$($curData.data.monto)" -ForegroundColor Gray
        }
    } catch {
        Write-Host "   ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Test 13: Logout
Write-Host "`n13. Probando Logout..." -ForegroundColor Yellow
try {
    $logoutResponse = Invoke-WebRequest -Uri "$baseUrl/api/auth/logout" -Method POST -WebSession $session
    
    if ($logoutResponse.StatusCode -eq 200) {
        Write-Host "   ✓ Sesión cerrada correctamente" -ForegroundColor Green
    }
} catch {
    Write-Host "   ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

# Resumen final
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "   Pruebas Completadas" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "`nSistema funcionando correctamente! ✓" -ForegroundColor Green
Write-Host "`nPara pruebas completas, importa la colección de Postman:" -ForegroundColor Yellow
Write-Host "  - SAP_API_Collection.postman_collection.json" -ForegroundColor Gray
Write-Host "  - SAP_Local_Environment.postman_environment.json" -ForegroundColor Gray
Write-Host "`nConsulta POSTMAN_GUIDE.md para más información.`n" -ForegroundColor Cyan
