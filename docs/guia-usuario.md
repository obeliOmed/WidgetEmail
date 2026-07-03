# WidgetEmail — Guía de usuario

## ¿Qué hace este campo?

El campo de email valida el formato mientras escribes y te avisa si el dominio es de un servicio de correo "desechable" (temporal), muy usado para dar datos falsos.

## Cómo usarlo

1. Escribe el email en el campo.
2. Al salir del campo aparece un aviso:
   - **✓** (verde) — formato correcto.
   - **⚠** (amarillo) — formato correcto, pero es un dominio de correo desechable/temporal (ej. `mailinator.com`, `10minutemail.com`, `yopmail.com`). **No se bloquea el guardado**, solo es un aviso — puede que el paciente/cliente realmente use ese correo.
   - **✗** (rojo) — formato incorrecto.
3. El sistema convierte automáticamente el email a minúsculas y quita espacios al principio/final.

## Qué se considera un email inválido

- Sin arroba o sin dominio (`juan`, `juan@`)
- Dominio de una sola palabra sin punto (`juan@localhost`)
- Direcciones IP en vez de dominio (`juan@[192.168.1.1]`)
- Más de 254 caracteres en total, o más de 64 en la parte antes de la arroba
- Comillas en la parte local (`"juan"@dominio.com`)

## Preguntas frecuentes

**¿Por qué me sale el aviso amarillo (⚠) si el email está bien escrito?**
Porque el dominio pertenece a una lista conocida de correos temporales/desechables. Es solo informativo — el sistema no impide guardar ese email.

**¿Distingue mayúsculas/minúsculas?**
No — se normaliza todo a minúsculas al guardar.

**¿Puedo dejarlo en blanco?**
Sí, el campo vacío no da error de formato.
