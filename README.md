# Hotmart MemberPress Webhook

Este plugin permite integrar los Webhooks de Hotmart con el sistema de membresías de MemberPress en WordPress.

## 🎯 Funcionalidades

- Recepción de notificaciones desde Hotmart vía Webhook
- Procesamiento de evento `PURCHASE_APPROVED`:
  - Crea el usuario en WordPress (si no existe) con email, nombre y apellido
  - Asigna la membresía correspondiente usando `MeprTransaction`
  - Mapea múltiples `offer.code` a membresías distintas
- Registro detallado de logs en el archivo `hmw-log.txt`
- Interfaz de configuración dentro del panel de administración

## 🛠️ Configuración desde WordPress

1. Ir a **Hotmart Webhook** en el menú del administrador de WordPress.
2. Completar la tabla con:
   - Nombre del Producto (texto libre)
   - Offer Code (proporcionado por Hotmart)
   - Membresía (seleccionable de las creadas en MemberPress)
3. Guardar la configuración.

## 📩 Webhook Endpoint

El plugin registra el siguiente endpoint:

```
POST /wp-json/hmw/v1/webhook
```

Este debe configurarse en Hotmart como Webhook POST para eventos tipo `PURCHASE_APPROVED`.

## 📂 Estructura de Archivos

```
hotmart-memberpress-webhook/
├── includes/
│   ├── admin-settings.php
│   ├── webhook-handler.php
│   └── events/
│       ├── purchase-approved.php
│       └── purchase-canceled.php (pendiente)
├── hotmart-memberpress-webhook.php
├── hmw-log.txt (se genera automáticamente)
├── README.md
```

## 🚧 Estado actual

- [x] Webhook activo para `PURCHASE_APPROVED`
- [ ] Soporte para `PURCHASE_CANCELED` (próxima versión)
- [ ] Test unitarios
- [ ] Actualizaciones automáticas vía GitHub

## 🧑 Autor

Desarrollado por Javier Quiroz  
Repositorio: https://github.com/soyjavierquiroz/hotmart-memberpress-webhook  
Versión actual: 1.0.0
