RandomPress

Mini-blog de noticias en PHP y Bootstrap con:

10 posts por página

Paginación limitada a 4 páginas

Modo claro/oscuro con persistencia en localStorage

Rate limiting: máximo 30 peticiones/minuto por IP

Caché de respuestas (5 minutos) para NewsAPI y RandomUser

Fallback a endpoint everything de NewsAPI si no hay resultados

Validación de errores y mensajes claros de NewsAPI

Autores generados de forma aleatoria con RandomUser.me

📁 Estructura de archivos

randompress/
├── .env                 # Variables de entorno (NEWSAPI_KEY)
├── .env.example         # Ejemplo de .env
├── composer.json        # Dependencias PHP
├── composer.lock        # Versionado de dependencias (no obligatorio)
├── bootstrap.php        # Carga de Composer + dotenv
├── index.php            # Lógica principal y renderizado
├── cache/               # Caché de JSON (creado por la app)
├── vendor/              # Librerías instaladas por Composer
├── README.md            # Documentación del proyecto
└── .gitignore           # Archivos ignorados por Git

🚀 Requisitos

PHP ≥ 7.4 con extensiones:

curl

json

zip (para Composer)

Composer instalado

Clave válida de News API (regístrate en https://newsapi.org/)

Variables de entorno:

cp .env.example .env
# Edita .env y añade tu clave:
# NEWSAPI_KEY=tu_clave_newsapi

Instala dependencias PHP:

composer install

Crea carpeta de caché y da permisos:

mkdir cache
chmod 775 cache

⚙️ Uso

Navega entre páginas con la barra de paginación (4 páginas).

Cambia entre modo claro y modo oscuro con el botón 🌓 (persistencia en localStorage).

Si superas 30 peticiones en 60 segundos verás un error 429 Too Many Requests.

El sistema cachea respuestas de NewsAPI y RandomUser durante 5 minutos.

📝 .gitignore

/vendor/
/cache/
.env

📄 Licencia

MIT — ¡Siéntete libre de usar y modificar este proyecto!

