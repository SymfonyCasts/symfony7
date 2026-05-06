# PHP 8.4 y actualizaciones de recetas

¡Hola amigos! Bienvenidos a un nuevo curso sobre cómo actualizar una aplicación Symfony de Symfony 7 a Symfony 8. Por suerte, las nuevas versiones de Symfony hacen que este proceso de actualización sea pan comido. Pero todavía hay algunas cosas que hacer para que el proceso de actualización sea más suave.

Para seguir adelante, descarga el código del curso desde la parte superior de esta página, abre el directorio`start` en tu IDE, y verás lo que tengo aquí. Sigue el README para configurarlo todo, cuando estés listo, en tu terminal, ejecuta:

```terminal
symfony server:start -d
```

(la bandera `-d` ejecuta el servidor en segundo plano). Haz clic en el enlace de la salida para abrir la aplicación en tu navegador.

¡Bienvenido a la Starshop! Si has seguido nuestros cursos anteriores sobre Symfony 7, esto debería resultarte familiar.

Abajo, en la barra de herramientas de depuración web, puedes ver que estamos en Symfony 7.3.5 y PHP 8.3.30.

¿Nuestro objetivo? ¡Actualizar este chico malo a Symfony 8!

Dado que Symfony 8 requiere PHP 8.4, un buen primer paso es actualizar esta aplicación para que requiera PHP 8.4. Al mismo tiempo, también actualizaremos a la última versión de Symfony 7.3.

Si ya has hecho estas actualizaciones importantes antes, puede que estés pensando que deberíamos actualizar a 7.4, la última versión de Symfony 7. Lo haremos, pero me gusta dar pequeños pasos para evitar agobiarme con demasiados cambios a la vez.

En tu IDE, abre `composer.json`. En la sección `require`, cambia `>=8.3` por `>=8.4`:

[[[ code('4426dcd419') ]]]

Esto indica a Composer que se necesita al menos PHP 8.4 para este proyecto.

Desplázate hacia abajo y encuentra la sección `config.platform.php`. Esto es lo que utiliza la CLI de Symfony para determinar qué versión local de PHP utilizar cuando ejecutas `symfony console` o `symfony php`. Cámbialo por `8.4`:

[[[ code('d1ae6bb136') ]]]

Este paso no es necesario, pero desplázate hacia abajo hasta la sección `replace`. Esta es una lista de paquetes que Composer ignorará al instalar dependencias. Puedes ver que son todos paquetes polyfill. Se trata de paquetes que proporcionan características de versiones más recientes de PHP a versiones más antiguas. Como estamos en PHP 8.4, podemos añadir los paquetes polyfill para 8.3 y 8.4 a esta lista:

[[[ code('94ddfe93aa') ]]]

Ahorra unos bytes de espacio en disco y unos milisegundos de tiempo de instalación...

En nuestro terminal, confirmemos que ahora estamos utilizando PHP 8.4. Ejecuta:

```terminal
symfony php --version
```

Bien, ¡8.4.20!

Ahora, hagamos una actualización de Composer para capturar todos los cambios que hemos hecho en nuestro archivo `composer.json` y actualicemos a las últimas dependencias permitidas. Ejecuta:

```terminal
symfony composer update
```

Sin errores, ¡genial! Salta al navegador y actualiza la página...

Qué asco, esto es un error desagradable. No se parece a los errores estándar de Symfony porque ni siquiera ha podido cargar Symfony. Se trata de un error a nivel del autoloader de Composer.

Dice que estamos ejecutando PHP 8.3 pero nuestras dependencias requieren PHP 8.4. ¿Recuerdas que antes ejecutamos el servidor Symfony en segundo plano? Lo ejecutamos con PHP 8.3. Solución sencilla, reinicia el servidor con:

```terminal
symfony server:stop
symfony server:start -d
```

Ahora actualiza la página... ¡Genial! Volvemos a estar operativos. Observa en la barra de herramientas de depuración web que ahora estamos en Symfony 7.3.11 y PHP 8.4.20.

Y ahora, ¡las depreciaciones! Cuando Symfony, o el propio PHP quieren eliminar o cambiar una característica, no hacen el cambio sin más. En lugar de eso, marcan la función como obsoleta durante un tiempo, lo que significa que sigue funcionando como se espera, pero activa una advertencia. Normalmente, la advertencia explica cómo solucionar el problema y actualizar a la nueva forma de hacer las cosas.

Symfony tiene una política de desaprobación realmente buena. Garantizan que nada se romperá al actualizar a una nueva versión menor, como de la 7.3 a la 7.4. Los cambios de ruptura se producen en la versión mayor, como pasar de la 7 a la 8. En la 7.4, todos los cambios de ruptura que se producirán en la 8 se marcan como obsoletos. Esto te da tiempo y orientación para arreglarlos. Sólo pasarás a la 8 cuando ya no queden depreciaciones.

Entonces, ¡cómo encontramos las obsoletas! Este panel de registro de depreciaciones va a ser tu mejor amigo cuando actualices Symfony.

Parece que tenemos 3. La primera es una deprecación de PHP. El método `addDroid()` de nuestra entidad`Starship` necesita que un parámetro se marque explícitamente como anulable. Arreglemos eso primero.

Abre `src/Entity/Starship.php` y desplázate hasta el método `addDroid()`. Ahh, incluso PhpStorm me avisa de esto. La solución es sencilla, añade un `?` antes de `DateTimeImmutable`:

[[[ code('2491d03716') ]]]

Vuelve al navegador, actualiza la página de inicio, y... todavía 3 deprecations... No se ha borrado. A veces, cuando se corrigen las depreciaciones, es necesario borrar manualmente la caché de Symfony para que las recoja. Esto se debe a que algunas deprecaciones se detectan en tiempo de compilación cuando se está construyendo el contenedor. Así que en tu terminal, ejecuta:

```terminal
symfony console cache:clear
```

Actualiza la página de nuevo... Vaya, ahora tenemos 4 desaprensiones. Abre el panel. Nuestra deprecación `addDroid()`ha desaparecido, así que la hemos arreglado. A veces, cuando limpias la caché manualmente, se activan otras imprecisiones. Vale, 3 de ellas son de Doctrine. Tendremos un capítulo entero sobre la actualización de Doctrine un poco más adelante, así que las ignoraremos por ahora.

La cuarta es una opción de configuración obsoleta de zenstruck/foundry.

Antes de arreglarla manualmente, actualicemos primero nuestras recetas Symfony Flex. A veces, simplemente actualizándolas se arreglan las obsoletas por ti.

Para actualizar las recetas, en tu terminal, ejecuta:

```terminal
symfony composer recipe:update
```

No ha funcionado. Dice que tenemos cambios no comprometidos. El comando de actualización de recetas debe ejecutarse en una pizarra limpia, sin cambios pendientes. Ejecuta:

```terminal
git status
```

Para ver nuestros cambios. `composer.json` `composer.lock` , y nuestro `Starship.php`. Tiene sentido, vamos a confirmarlos:

```terminal
git commit -a -m "composer update"
```

`-a` añade todos los archivos modificados a la lista de cambios antes de confirmar y `-m` nos permite establecer un mensaje de confirmación.

Borrón y cuenta nueva, así que vuelve a ejecutar el comando de actualización de la receta:

```terminal-silent
symfony composer recipe:update
```

Ooo, tenemos un montón que actualizar. Los revisaremos uno a uno. Al pulsar enter utilizaremos el primero de la lista, `doctrine/deprecations`.

"No se ha modificado ningún archivo...". Ejecuta `git status` para ver qué pasa. Sólo se ha modificado el archivo `symfony.lock`. Éste es el archivo de configuración interna que Symfony Flex utiliza para llevar un registro de tus recetas. Podemos confirmar esto y seguir adelante:

```terminal
git commit -a -m "update recipes"
```

¡Adelante! Ejecuta de nuevo el comando de actualización de recetas:

```terminal-silent
symfony composer recipe:update
```

`asset-mapper` es lo siguiente... Otra vez el archivo `symfony.lock`.

Cuando actualizo recetas, me gusta mantener todas las actualizaciones en un único commit. Así que modificaremos los nuevos cambios con el commit anterior con:

```terminal
git commit -a --amend
```

Esto abre un editor de texto para actualizar opcionalmente el mensaje de confirmación. Saldré para mantenerlo igual.

Ahora actualiza la receta de `framework-bundle`... Ooo, este tiene algunos cambios reales. Lo bueno de este comando es que muestra el CHANGELOG del repositorio symfony/recipe, así que puedes indagar fácilmente para entender por qué se ha hecho un cambio.

Ejecuta `git status` para ver los cambios. Aparte del archivo `symfony.lock`, se ha modificado `public/index.php`. ¡Vamos a comprobarlo! Abre `public/index.php` y observa el cambio... Parece que se ha añadido la palabra clave `static`. Creo que es una microoptimización útil, ¡la mantendremos!

Este es un buen momento para mencionar que nunca debes actualizar recetas a ciegas. Comprueba siempre los cambios y, si no estás seguro de un cambio, sigue el registro de cambios hasta el repositorio symfony/recipe para encontrar el razonamiento.

Modifica el commit y pasa a la siguiente actualización de recetas. El `monolog-bundle` también tiene algunos cambios. Parecen de configuración del paquete, así que abre `config/packages/monolog.yaml` para verlos. Sólo se han eliminado algunos comentarios. No pasa nada... modifica el commit.

A continuación, actualiza la receta `stimulus-bundle` y observa los cambios. Abre `assets/stimulus_bootstrap.js`. Sólo se han añadido algunas cosas. Modifica el commit y pasa a actualizar la receta `twig-bundle`. Nuestra plantilla base fue modificada, así que abre `templates/base.html.twig`. Se han añadido algunas cosas de FrankenPHP. De momento no utilizamos FrankenPHP, pero no está de más dejarlo por si lo hacemos en el futuro

Modifica el commit y actualiza la última receta: `zenstruck/foundry`. Se modificó el archivo de configuración, así que abre `config/packages/zenstruck_foundry.yaml`. Sólo se ha actualizado un comentario, así que no pasa nada.

Modifica el commit y ¡habremos terminado! Ejecuta de nuevo el comando de actualización de la receta para confirmar. Bien! "Todos los paquetes parecen estar actualizados"

Antes de comprobar la aplicación, limpia de nuevo la caché...

Ahora actualiza la página de inicio. 2 desapariciones. La del cargador automático de Doctrine sigue aquí, pero la ignoraremos hasta que actualicemos Doctrine. La de Foundry también sigue aquí, así que la actualización de la receta no la ha arreglado. Vamos a arreglarlo nosotros. Dice que esta configuración de `enable_auto_refresh_with_lazy_objects` se forzará a `true` en la 3.0, así que pongámosla ahora en `true`. Copia la opción y abre`config/packages/zenstruck_foundry.yaml`. En `zenstruck_foundry`, pégala, y ajústala a `true`:

[[[ code('fe8d185e21') ]]]

Totalmente al margen, pero si tienes curiosidad por saber qué es esta extraña sintaxis de `&dev` y `*dev`, se trata de un ancla y un alias de YAML. El `&dev` marca esta configuración como un ancla llamada `dev`, y luego el `*dev` es un alias que hace referencia a esa ancla. Así que esto está diciendo: "para el entorno `test`, utiliza la misma configuración que `dev`". Es una forma genial de evitar la duplicación en tus archivos YAML.

Vale, vuelve al navegador y actualiza la página de inicio. ¡Ya no hay depreciaciones!

Pero... borremos la caché... y actualicemos de nuevo... ahora hay 3, pero son las de Doctrine que arreglaremos más tarde.

Vale, hemos actualizado con éxito nuestra aplicación a PHP 8.4. A continuación, actualizaremos a Symfony 7.4, la última versión de Symfony 7.
