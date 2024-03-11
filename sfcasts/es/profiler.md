# Depurando con el Asombroso Perfilador

Symfony presume de tener algunas de las herramientas de depuración más épicas de todo Internet. Pero como las aplicaciones Symfony empiezan tan pequeñas, aún no las tenemos instaladas. Es hora de arreglarlo. Dirígete a tu terminal y, como antes, confirma todos tus cambios para que podamos comprobar lo que harán las recetas. Ya lo he hecho.

## Instalar las herramientas de depuración

Ejecuta:

```terminal
composer require debug
```

¡Sí! Es otro alias de Flex. E... instala un paquete. Esto instala cuatro paquetes diferentes que añaden una variedad de bondades de depuración a nuestro proyecto. Gira y abre `composer.json`. 

[[[ code('3921cfe932') ]]]

Vale, el paquete ha añadido una nueva línea bajo la clave `require` para `monolog-bundle`. 
Monolog es una biblioteca de registro.

Luego, al final, ha añadido tres paquetes a la sección `require-dev`.

[[[ code('23fb5876dc') ]]]

Se conocen como dependencias de desarrollo... lo que significa que no se descargarán cuando los despliegues en producción. Por lo demás, funcionan igual que los paquetes de la clave `require`. Los tres ayudan a impulsar algo llamado perfilador, que veremos dentro de un minuto.

Antes de hacerlo, vuelve a tu terminal y ejecuta

```terminal
git status
```

para que podamos ver lo que hicieron las recetas. Vale: actualizó los archivos normales, habilitó unos cuantos bundles nuevos y nos dio tres archivos de configuración nuevos para esos bundles.

¿Cuál es el resultado final de todo esto nuevo? Bueno, en primer lugar, ahora tenemos una biblioteca de registros. Así que, como por arte de magia, los registros empezarán a aparecer en un directorio `var/log/`.

## Hola barra de herramientas de depuración web y perfilador

Pero el momento alucinante ocurre cuando actualizamos la página. ¡Woh! Una nueva y hermosa barra negra en la parte inferior llamada barra de herramientas de depuración web.

Está repleta de información. Aquí podemos ver la ruta y el controlador de esta página. Eso facilita ir a cualquier página de tu sitio -quizá una que ni siquiera hayas construido- y encontrar rápidamente el código que hay detrás. También podemos ver cuánto tardó en cargarse esta página, cuánta memoria utilizó, e incluso la plantilla Twig que se renderizó y cuánto tardó.

Pero la verdadera magia de la barra de herramientas de depuración web ocurre cuando haces clic en cualquiera de estos enlaces: saltas al perfilador. Éste tiene diez veces más información: detalles sobre la petición y la respuesta, registros que se produjeron mientras se cargaba la página, detalles de enrutamiento e incluso estadísticas sobre las plantillas Twig que se procesaron. Aparentemente, se estaban renderizando seis plantillas: la principal, el diseño base y algunas otras que alimentan la barra de herramientas de depuración web, que, por cierto, no se renderizarán ni se mostrarán cuando pasemos a producción. Pero de eso hablaremos en el próximo tutorial.

Luego está probablemente mi sección favorita: Rendimiento. Aquí se divide todo el tiempo de carga de nuestra página en diferentes partes. Esto me encanta. A medida que aprendas más sobre Symfony, te irás familiarizando con lo que son estas diferentes piezas. Esta sección es útil para saber qué parte de tu código puede estar ralentizando la página... pero también es una forma fantástica de profundizar en Symfony y entender todas sus piezas móviles.

Vamos a utilizar el perfilador a lo largo de esta serie, pero pasemos a otra herramienta de depuración: ¡una que ha estado instalada en nuestra aplicación todo este tiempo!

## ¡Hola bin/console!

Dirígete a la línea de comandos y ejecuta:

```terminal
php bin/console
```

O, en la mayoría de las máquinas, puedes decir simplemente `./bin/console`. Esta es la consola de Symfony, y está repleta de comandos que pueden hacer todo tipo de cosas Aprenderemos sobre ellos a lo largo del camino. También puedes añadir tus propios comandos, cosa que haremos al final del tutorial.

Fíjate en que muchos de ellos empiezan por `debug`, como `debug:router`. Pruébalo:

```terminal
php bin/console debug:router
```

¡Genial! Esto nos muestra todas las rutas de nuestra aplicación: la ruta de la página de inicio en la parte inferior y un montón de rutas añadidas por Symfony en el entorno `dev` que alimentan la barra de herramientas de depuración web y el perfilador.

Otro comando es `debug:twig`:

```terminal-silent
php bin/console debug:twig
```

Nos indica todas las funciones, filtros u otros elementos de Twig que existen en nuestra aplicación. Es como la documentación de Twig... salvo que también incluye funciones y filtros adicionales añadidos a Twig por los bundles que hemos instalado. Genial.

Estos comandos de `debug` son superútiles, y seguiremos probando más de ellos por el camino.

A continuación, vamos a crear nuestra primera ruta API y a conocer el potente componente serializador de Symfony.
