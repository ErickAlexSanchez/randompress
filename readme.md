# RandomPress
## Autor: Erick Alexander Sánchez
## Vista del proyecto en vivo:
http://random-press.appe4.com/

Mini-blog de noticias en PHP y Bootstrap con:
- **10 posts por página**
- **Paginación** limitada a 4 páginas
- **Modo claro/oscuro** con persistencia en localStorage
- **Rate limiting**: máximo 30 peticiones/minuto por IP
- **Caché** de respuestas (5 minutos) para NewsAPI y RandomUser
- **Fallback** a endpoint everything de NewsAPI si no hay resultados
- **Validación** de errores y mensajes claros de NewsAPI
- **Autores** generados de forma aleatoria con RandomUser.me


## Estructura de archivos

randompress/
├── .env                 # Variables de entorno (NEWSAPI_KEY)
├── .env.example         # Ejemplo de .env
├── composer.json        # Dependencias PHP
├── composer.lock        # Versionado de dependencias
├── bootstrap.php        # Carga de Composer + dotenv
├── index.php            # Lógica principal y renderizado
├── cache/               # Caché de JSON (creado por la app)
├── vendor/              # Librerías de Composer
├── README.md            # Documentación del proyecto
└── .gitignore           # Archivos ignorados por Git

## Requisitos

- PHP ≥ 7.4 con extensiones: curl, json, zip
- Composer instalado
- Clave válida de News API

## Instalación

1. Clona el repositorio:

2. Variables de entorno:
   cp .env.example .env
   # Edita .env y añade tu clave: NEWSAPI_KEY=tu_clave_newsapi

3. Instala dependencias:
   composer install

4. Crea carpeta cache (opcional):
   mkdir cache
   chmod 775 cache

5. Inicia servidor:
   php -S 0.0.0.0:8000
   # Visitar http://localhost:8000/index.php

## Uso

- Navegar páginas (4 páginas)
- Cambiar modo claro/oscuro con 🌓 (localStorage)
- 429 tras 30 peticiones/60s
- Caché 5 minutos NewsAPI/RandomUser

## .gitignore

/vendor/
*/cache/
/.env
composer.phar

## Licencia

MIT
