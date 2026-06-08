# Instalar el bundle de seguridad

¡Hola Amigos! ¡Bienvenidos al curso de Seguridad de Symfony! En este tutorial, vamos a explorar una de las partes más importantes de cualquier aplicación web: controlar quién puede acceder a tu sitio y qué se le permite hacer una vez dentro. Para ayudar a explicar estos conceptos, utilizaremos nuestro universo Starshop: una enorme estación de reparación interestelar llena de mecánicos, capitanes, ingenieros, hangares restringidos y cubiertas de mando de alta seguridad.

Piensa en Symfony Security como el sistema de seguridad automatizado a bordo de la estación. Cuando llega una nave, la estación primero tiene que identificar quién está atracando: eso es la autenticación. 
Luego, una vez dentro, la estación decide a qué sectores puede acceder esa persona: eso es la autorización. 
Por el camino, exploraremos cortafuegos, roles, votantes, sistemas de inicio de sesión, hash de contraseñas y mucho más.

Para seguir el curso, deberás descargarte el código del curso, que encontrarás en la parte superior de la página. Una vez que lo tengas, abre el directorio `start`en tu IDE favorito. Encontrarás un código parecido a éste. Echa un vistazo al README para empezar. Sigue los pasos que allí se indican, y una vez que hayas terminado, podrás ejecutar el servidor Symfony utilizando:

```terminal
symfony serve -d
```

Abre el enlace proporcionado en tu navegador y verás nuestra aplicación Starshop, una ingeniosa herramienta diseñada para gestionar y reparar naves estelares. 

## Instalar el componente de seguridad

Ahora, vamos a ensuciarnos las manos. Nuestra primera tarea es instalar el componente de seguridad. En nuestro terminal, ejecuta el siguiente comando:

```terminal
symfony composer require security
```

Parece que se ha instalado el componente `symfony/clock`, una dependencia de los paquetes de seguridad, junto con los componentes `symfony/security-bundle` y`symfony/security-http`. El bundle se encarga de integrar los componentes de seguridad en Symfony, mientras que el componente `security-http` se ocupa de los aspectos HTTP de la seguridad.

Para ver qué ha cambiado, ejecuta:

```terminal
git status
```

Podemos ver actualizaciones en nuestros archivos `composer.json` y `composer.lock` y se ha añadido el bundle a nuestro archivo `config/bundles.php`.

La receta de Flex añadió estos archivos `config/packages/security.yaml` y`config/routes/security.yaml`. Vamos a examinarlos. 

## Examinar los nuevos archivos

De vuelta a nuestro código, abre `config/routes/security.yaml`. Esto añade un cargador de rutas especial para añadir rutas de cierre de sesión. Actualmente, no hace nada, pero una vez que configuremos el cierre de sesión en nuestros ajustes de seguridad, se añadirá automáticamente una ruta de cierre de sesión.

A continuación, echa un vistazo a `config/packages/security.yaml`. Este archivo es el corazón de la operación. Bajo la clave `security`, tenemos opciones para configurar `password_hashers`, de las que hablaremos más adelante. Luego, tenemos esta clave`providers` - aquí es donde dictamos de dónde vienen nuestros usuarios. La configuración por defecto utiliza un proveedor en memoria, perfecto para sitios más pequeños. Sin embargo, en este curso nos centraremos en la carga de usuarios desde la base de datos.

## Cortafuegos

Continuando, tenemos la sección `firewalls`. Piensa en ellos como campos de fuerza que rodean toda tu aplicación o partes específicas de ella. Ahora bien, el orden aquí es crucial. El primero que coincida, gana. Por defecto, tenemos dos cortafuegos, un cortafuegos `dev` y un cortafuegos `main`. 

El cortafuegos `dev` se activa cuando accedemos a determinados patrones de ruta específicos de dev, como el perfilador. Esto `security: false` significa que cuando accedemos a esas rutas, se desactiva completamente la seguridad y se ignora el siguiente cortafuegos.

Como el cortafuegos `main` no tiene un patrón, significa que cubre toda la aplicación y es como un cajón de sastre. Cualquier cortafuegos adicional por debajo nunca sería alcanzado.

Ahora bien, que toda la aplicación esté cubierta por el cortafuegos `main` no significa que todos los usuarios deban autenticarse. Esta opción de `lazy: true` significa que los usuarios anónimos (usuarios que no han iniciado sesión) pueden acceder a la app, y ahora mismo, a cualquier página. También es perezosa, por lo que la comprobación de la autenticación y autorización del usuario se aplaza hasta que sea realmente necesaria para nuestro código. Esto puede ayudar a mejorar el rendimiento y, cuando se utiliza la autenticación de sesión, permite el almacenamiento en caché http.

Esta clave `provider` indica al cortafuegos desde dónde cargar los usuarios cuando se necesitan. En este momento, está utilizando el proveedor `users_in_memory` configurado anteriormente.

A continuación configuraremos distintos métodos de autenticación.

## Autenticación vs Autorización

Tomémonos un momento para aclarar la diferencia entre autenticación y autorización.

La autenticación responde a la pregunta: ¿quién eres? Es el proceso de demostrar tu identidad, como iniciar sesión con un formulario o presentar un token de API.

La autorización responde a la pregunta: ¿qué se te permite hacer? Controla a qué partes de la aplicación puedes acceder y qué acciones se te permite realizar una vez autenticado. Por ejemplo, incluso después de iniciar sesión con éxito, sólo los usuarios con el rol de administrador pueden acceder al panel de control de la estación, o, sólo el propietario de un ticket de reparación puede editarlo.

El cortafuegos sólo se encarga de la autenticación.

## Control de Acceso

Esta sección de `access_control` es una forma de gestionar la autorización. Te permite marcar ciertas rutas como que requieren roles específicos. Los ejemplos comentados aquí muestran que al acceder a cualquier ruta que empiece por `admin`, el usuario debe tener el rol admin, y al acceder a una ruta que empiece por `profile`, se requiere el rol user.

Por tanto, cuando se acceda a cualquiera de estas rutas, si el usuario no ha iniciado sesión, se le obligará a autenticarse, ya sea redirigiéndole a una página de inicio de sesión o dándole una respuesta 401 No autorizado, dependiendo de cómo esté configurada la autenticación. Si están autenticados, pero no tienen el rol correcto, recibirán una respuesta 403 Prohibido.

De nuevo, el orden es importante. Gana la primera regla que coincida.

## Barra de herramientas de depuración web

Con la seguridad activada, vuelve a nuestra aplicación y actualiza la página de inicio... Observa el nuevo icono de seguridad en la barra de herramientas de depuración web. Esto nos da una visión rápida del estado de seguridad de la petición actual. Muestra `n/a` porque no estamos autenticados y no tenemos nombre de usuario. Al pasar el ratón por encima, podemos ver que estamos utilizando el cortafuegos `main` y que, efectivamente, no estamos autenticados.

A continuación, vamos a crear la entidad Usuario que proporcionará los usuarios para nuestro cortafuegos `main`.
