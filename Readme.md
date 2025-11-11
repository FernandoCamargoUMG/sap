# 💰 Sistema de Administración Presupuestaria (SAP)

### 🏫 Universidad Mariano Gálvez de Guatemala — Sede Escuintla  
**Facultad de Ingeniería en Sistemas**  
**Proyecto de Graduación I – 9° Ciclo**  
**Autor:** Jenry Emanuel Teletor Rosales  
**Asesor:** Ing. Carlos Eduardo Hernández Herrera  
**Fecha:** 31 de marzo de 2025  

---

## 📘 Descripción del Proyecto

El **Sistema de Administración Presupuestaria (SAP)** es una **aplicación web** diseñada para optimizar la **planificación, ejecución y monitoreo del presupuesto anual** en el **Comando Aéreo Central “La Aurora”**, ubicado en Ciudad de Guatemala.

El sistema busca **automatizar los procesos financieros**, reducir errores humanos y mejorar la **transparencia, seguridad y eficiencia** en la gestión presupuestaria.  
Su diseño web permitirá acceder desde cualquier dispositivo dentro de la red local.

---

## 🎯 Objetivos y Alcance

- 📊 Planificar de forma eficiente el presupuesto anual.  
- 💡 Monitorear en tiempo real los gastos y detectar sobre/subejecuciones.  
- 💼 Gestionar movimientos financieros por renglón presupuestario.  
- 🧾 Generar reportes automáticos para auditorías y rendición de cuentas.  
- 📁 Almacenar y organizar documentación financiera.  
- 🔒 Garantizar la seguridad mediante roles de usuario y autenticación JWT.  
- 💻 Accesible desde cualquier dispositivo dentro del entorno local.

---

## 💡 Beneficios

- ✅ Reducción de errores en la ejecución presupuestaria.  
- 🔍 Mayor transparencia y control del uso de fondos.  
- ⚙️ Automatización de reportes financieros y procesos administrativos.  
- 🧠 Seguridad avanzada para la información sensible.  
- 📱 Diseño moderno y adaptable (frontend en Vue).  

---

## 🧩 Módulos del Sistema

### 🔹 **1. Gestión de Saldos Presupuestarios**
- **Renglones:** Crear, consultar, modificar o eliminar renglones presupuestarios.  
- **Movimientos:** Registrar, consultar, modificar o anular transacciones financieras.  
- **Proveedores:** Administrar proveedores, facturas y documentos de soporte.  

---

### 🔹 **2. Gestión de Usuarios y Roles**
- **Cuentas:** Registro, consulta, modificación y eliminación de usuarios.  
- **Roles:** Asignación de permisos (lector, editor, administrador).  
- **Recuperación de contraseña:** Sistema automatizado por correo electrónico.  

---

### 🔹 **3. Gestión Documental**
- **Intras:** Transferencias entre renglones presupuestarios.  
- **Programación Presupuestaria:** Definición mensual de montos y documentos de respaldo.  
- **CUR:** Asignación y control de comprobantes únicos de registro (CUR) con soporte documental.  

---

### 🔹 **4. Control de Facturas**
- **Registro de Insumos:** Administración de facturas para inventario, bodega y despensa.  
- **Renglones de Facturación:** Configuración de renglones asociados a cada área.  
- **Actas de Bajas Cuantías:** Registro, consulta y control de actas mensuales de contraloría.  

---

## 🛠️ Tecnologías Utilizadas

| Área | Tecnología | Descripción |
|------|-------------|-------------|
| 🧩 **Backend** | **Laravel 12** | Framework PHP moderno para desarrollo estructurado y seguro. |
| 🎨 **Frontend** | **Vue.js** | Framework progresivo para interfaces dinámicas y reactivas. |
| 🗄️ **Base de Datos** | **MySQL** | Sistema relacional para almacenar toda la información presupuestaria. |
| 🔐 **Autenticación** | **JWT (JSON Web Tokens)** | Mecanismo de autenticación segura basada en tokens. |
| 🌐 **Entorno** | **Proyecto local** | Desarrollado y desplegado dentro de la red interna del Comando Aéreo. |

---

## 🧱 Estructura General del Proyecto
/sap
├── backend/ # API REST desarrollada con Laravel 12
│ ├── app/
│ ├── routes/
│ ├── database/
│ └── config/
├── frontend/ # Interfaz desarrollada en Vue.js
│ ├── src/
│ ├── components/
│ ├── views/
│ └── router/
├── .env # Variables de entorno (conexión local, JWT, etc.)
└── README.md

---

## 🚀 Funcionalidades Clave

- Sistema de autenticación y roles (JWT).  
- Gestión integral de renglones presupuestarios.  
- Control de movimientos, proveedores y facturas.  
- Generación de reportes automáticos y trazabilidad completa.  
- Interfaz responsiva y dinámica con Vue.js.  
- Base de datos segura y estructurada en MySQL.  

---

## ⚙️ Requisitos del Entorno

- PHP 8.2+  
- Composer  
- Node.js 20+  
- MySQL 8.0+  
- Laravel CLI  
- Vue CLI  

---

## 📄 Licencia
Proyecto académico desarrollado con fines educativos en la **Universidad Mariano Gálvez de Guatemala**.  
Derechos reservados © 2025 – **Jenry Emanuel Teletor Rosales**  

---
