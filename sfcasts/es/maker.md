# Maker Bundle: ¡Generemos algo de código!

Me quito el sombrero por haber superado casi por completo el primer tutorial de Symfony. Has dado un gran paso hacia la construcción de lo que quieras en la web. Para celebrarlo, quiero jugar con MakerBundle: La impresionante herramienta de Symfony para la generación de código.

## Composer require vs require-dev

Vamos a instalarlo:

```terminal
composer require symfony/maker-bundle --dev
```

Aún no hemos visto la bandera `--dev`, pero no es tan importante. Muévete y abre `composer.json`. Gracias a la bandera, en lugar de que `symfony/maker-bundle` vaya bajo la clave `require`, se ha añadido aquí abajo, bajo `require-dev`. 

[[[ code('958656467c') ]]]

Por defecto, cuando ejecutes `composer install`, descargará todo lo que esté bajo `require`y `require-dev`. Pero `require-dev` está pensado para paquetes que no necesitan estar disponibles en producción: paquetes que sólo necesitas cuando desarrollas localmente. Esto se debe a que, cuando despliegues, si quieres, puedes decirle a Composer:

> ¡Eh! Instala sólo los paquetes de mi clave `require`: no instales las cosas de
> `require-dev`.

Eso puede darte un pequeño aumento de rendimiento en producción. Pero, en general, no es gran cosa.

## Los comandos Maker

Acabamos de instalar un bundle. ¿Recuerdas lo principal que nos proporcionan los bundles? Exacto: servicios. Esta vez, los servicios que nos ha proporcionado MakerBundle son servicios que proporcionan nuevos comandos de consola. Redoble de tambores, por favor. Ejecuta:

```terminal
php bin/console
```

O, en realidad, empezaré a ejecutar `symfony console`, que es lo mismo. ¡Gracias al nuevo bundle, tenemos un montón de comandos que empiezan por `make`! Comandos para generar un sistema de seguridad, hacer un controlador, generar entidades de doctrina para hablar con la base de datos, formularios, oyentes, un formulario de registro.... ¡muchas, muchas cosas!

## Generar un comando de consola

Utilicemos uno de éstos para crear nuestro propio comando de consola personalizado. Ejecuta:

```terminal
symfony console make:command
```

Esto nos preguntará interactivamente por nuestro comando. Llamémoslo: `app:ship-report`. ¡Listo!

Esto ha creado exactamente un archivo: `src/Command/ShipReportCommand.php`. ¡Vamos a comprobarlo! 

[[[ code('73dc758763') ]]]

¡Genial! Esta es una clase normal - es un servicio, por cierto - pero con un atributo encima: `#[AsCommand]`. Esto le dice a Symfony:

> ¡Eh! ¿Ves este servicio? No es sólo un servicio: Me gustaría que lo incluyeras
> en la lista de comandos de la consola.

El atributo incluye el nombre del comando y una descripción. Además, la propia clase tiene un método `configure()` en el que podemos añadir argumentos y opciones. Pero la parte principal es que, cuando alguien llame a este comando, Symfony llamará a `execute()`.

Esta variable `$io` es genial. Nos permite mostrar cosas -como `$this->note()`o `$this->success()` - con diferentes estilos. Y aunque no lo veamos aquí, también podemos hacer preguntas al usuario de forma interactiva.

¿Y lo mejor? Con sólo crear esta clase, ¡ya está lista para usar! Pruébala:

```terminal
symfony console app:ship-report
```

¡Qué guay! El mensaje de aquí abajo procede del mensaje de éxito de la parte inferior del comando. Y gracias a `configure()`, tenemos un argumento llamado`arg1`. Los argumentos son cadenas que pasamos después del comando, como:

```terminal
symfony console app:ship-report ryan
```

Dice 

> Has pasado un argumento: ryan

... que viene de este lugar del comando.

## Construir una barra de progreso

Hay muchas cosas divertidas que puedes hacer con los comandos... y quiero jugar con una de ellas. Uno de los superpoderes del objeto `$io` es crear barras de progreso animadas.

Imagina que estamos construyendo un informe sobre un barco... y requiere algunas consultas pesadas. Así que queremos mostrar una barra de progreso en la pantalla. Para ello, decimos `$io->progressStart()`y le pasamos el número de filas de datos que estemos recorriendo y manejando. Imaginemos que estamos haciendo un bucle sobre 100 filas de datos para este informe.

En lugar de hacer un bucle sobre datos reales, crea un bucle falso con `for`. ¡Incluso voy a incluir la variable `$i` en el medio! Dentro, para hacer avanzar la barra de progreso, di `$io->advance()`. Entonces, aquí es donde haríamos nuestra consulta pesada o trabajo pesado. Finge eso con un `usleep(10000)` para crear una breve pausa.

Después del bucle, termina con `$io->progressFinish()`.

[[[ code('c6afb73551') ]]]

Ya está Gira y pruébalo:

```terminal-silent
symfony console app:ship-report ryan
```

Qué guay.

Y... ¡eso es todo, gente! Choca esos cinco contigo mismo... o, mejor, ¡sorprende a un compañero de trabajo con un choca esos cinco saltarín! Después, celébralo con una merecida cerveza, un té, un paseo por la manzana o un partido de frisbee con tu perro. Porque... ¡lo has conseguido! Has dado el primer gran paso para ser peligroso con Symfony. Entonces, vuelve y prueba estas cosas: juega con ellas, construye un blog, crea unas cuantas páginas estáticas, lo que sea. Eso marcará una gran diferencia.

Y si alguna vez tienes alguna pregunta, miramos atentamente la sección de comentarios debajo de cada vídeo y respondemos a todo. Además, ¡sigue adelante! En el próximo tutorial, vamos a ponernos aún más peligrosos profundizando en la configuración y los servicios de Symfony: los sistemas que dirigen todo lo que harás en Symfony.

Muy bien, amigos, ¡hasta la próxima!
