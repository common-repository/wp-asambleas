=== WP-Asambleas ===
Contributors: PLATCOM
Donate link: https://asamblea.co/donate/
Tags: asamblea, asambleas, votaciones, polls, annual meeting, elecciones, votes, voting, quorum, poll, polling, votos
Requires at least: 5.5
Tested up to: 6.2.2
Stable tag: 2.85
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Gestione el quorum y votaciones avanzadas en asambleas y elecciones virtuales, de  copropiedades, cooperativas, conjuntos residenciales, condominios, edificios, condóminos, fondos de empleados, clubes, empresas, asociaciones, sociedades, y más.

== Description ==

<b>Sitio web oficial</b>

Puede encontrar toda la información del plugin (<a href="https://asamblea.co/plugin-para-wordpress/">pulsando aquí</a>)

Este plugin le permite tener asambleas virtuales bajo un sitio en WordPress y votaciones restringidas a los usuarios que usted cree, pudiendo tener un control detallado de su asistencia (quorum).

Hay muchos plugins de votaciones para WordPress, pero este permite el recibir votos por  COEFICIENTES, o sea que cuando un usuario vote, su voto no pese siempre 1, sino un porcentaje o número de acciones de acuerdo a su participación en la organización, y tener la posibilidad de que un usuario otorgue PODER a otro para que lo represente, para que cuando quien recibe el poder vote, su voto pese por su propio coeficiente más el de los poderes que ha recibido. 


<b>Características</b>

1. Página de shortcode con múltiples opciones de configuración, pudiendo activar/ocultar columnas en la tabla general de usuarios conectados y mostrar/ocultar la lista de usuarios online y offline.

2. Dos tipos de votaciones:
Votaciones de única respuesta
Votaciones de múltiples respuestas, pudiendo seleccionar la cantidad de opciones mínimas y máximas que el usuario debe seleccionar.

3. Abrir y cerrar votaciones con un click

4. Mostrar resultados de las votaciones con un click

5. En los resultados de las votaciones se puede mostrar la lista de usuarios que votaron, que no votaron, y si se desea opcionalmente se puede mostrar la respuesta seleccionada por cada persona.

6. Exportarlos resultados de cada votación a archivos de texto CSV.

7. Enviar emails personalizados a los usuarios con su nombre de usuario y clave.

8. Posibilidad de integración con múltiples plugins externos:  Integración con Zoom, importar usuarios. Encuéntrelos todos en: https://asamblea.co/tutoriales-plugin-de-asambleas-virtuales-para-wordpress/

9. Perfil AUDITOR para que ciertos usuarios puedan ver resultados de las votaciones sin que estos tengan que ser mostrados a todos los usuarios.

10. Enviar emails masivos a sus usuarios con más campos personalizados, aparte del nombre de usuario y clave.

11. Shortcode para tener página para realizar sorteos / rifas. Demo: https://asamblea.co/rifa/


<b>Shortcodes</b>

1. Para mostrar el quórum: [quorum]

2. Para mostrar votaciones: [showallpolls]

3. Para mostrar resultados de las votaciones: [showallpollsanswers]

4. Para mostrar el quórum en una ventana emergente:
[quorum_popup link="Ver y marcar quórum"]

5. Para abrir pagina de votaciones en ventana emergente:
[polls_popup link="VOTE AQUI" iframe_url="pagina_con_shortcodes_de_votos_aqui"]

6. Para mostrar a usuarios con rol auditor los resultados de todas las votaciones, aun si estas estan cerradas:[showallpollsanswers_audit]

7. Shortcode para que usuarios puedan ver en una página sus respuestas en las votaciones: [mypollanswers]

En la siguiente pagina puede ver shortcodes adicionales y explicacion mas detallada:  https://asamblea.co/tutoriales-plugin-de-asambleas-virtuales-para-wordpress/

Ejemplo:
[polls_popup link="VOTE AQUI" iframe_url="https://demo.asamblea.co/votos/"]

Esto mostrará un enlace VOTE AQUI en la página donde se incluya, que al pulsarlo abrirá la página https://demo.asamblea.co/votos/  en una ventana emergente. Esa página https://demo.asamblea.co/votos/ debe tener el shortcode [showallpolls] en ella para mostrar los votos activos.


<b>Versión Premium </b>

La versión premium permite, todo lo que incluye esta versión gratuita y además:

1. Posibilidad de crear votaciones ilimitadas.  La version gratuita permite solo 2.

2. Posibilidad de importar votaciones masivamente.

<b>Demo</b>

En la siguiente página puede ver un demo de este plugin:

https://demo.asamblea.co 


<b>Idiomas soportados: </b>

* Inglés
* Español

== Instalación ==

1. Suba el archivo del plugin al directorio  `/wp-content/plugins/`y descomprímalo.

2. Active el plugin en el menu PLUGINS de su WordPress

3. Encuentre las opciones del plugin en el menu VOTACIONES y en USUARIOS

== Screenshots ==

1. Backend - Página principal de votaciones
2. Backend - Crear nueva votación
3. Backend - Ajustes del quorum
4. Backend - Ajuste resultados de votaciones 
5. Backend - Envío de emails masivos a los usuarios con su clave
6. Backend - Gestión de poderes
7. Backend - Reportes
8. Frontend - Voto simple
9. Frontend - Voto múltiple
10. Frontend - Vista del quorum
11. Resultado de votaciones


== Changelog ==

= 2.85.0. =
* Nuevo shortcode que permite al administrador abrir manualmente el popup de votaciones en las pantallas de los usuarios
* Activada opcion para gestionar cantidad de decimales a mostrar en valores de quorum y resultados de votaciones
* En resultados... Cantidad de  abstenciones (Usuarios/Acciones en QUORUM que no votaron)
* Usuarios que se cuentan como presentes en quorum pueden definirse ahora desde una hora especifica o desde X minutos hacia atras desde la hora actual.

= 1.17.3. =
* Mejoras en el shortcode para mostrar quorum
* Mejoras en shortcode para mostrar resultados de las votaciones
* Mejoras en gestión de poderes


= 1.16.10. =
* Mejoras en el shortcode para mostrar el quorum

= 1.16.9. =
* Actualización general

= 1.15.. =
* Nueva página de reportes


== Preguntas frecuentes ==

= Para cualquier inquietud =

1. Por favor escribir a: soporte@asamblea.co 


