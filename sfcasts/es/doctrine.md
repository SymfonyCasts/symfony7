# Actualización de Doctrine y objetos perezosos nativos

Tenemos que actualizar el bundle de Doctrine... pero antes de hacerlo, quiero refrescarte la memoria sobre una función muy interesante de Doctrine: los objetos perezosos.

Abre `src/Entity/StarshipPart.php`. Esta entidad tiene una propiedad `Starship` con una relación de muchos a uno con nuestra entidad `Starship`. Cada `StarshipPart`tiene un `Starship`, y cada `Starship` puede tener muchos `StarshipParts`. Cuando se obtienen entidades con relaciones, Doctrine utiliza cierta magia para evitar consultas innecesarias a la base de datos. Veamos cómo funciona en acción.

## Configurar un controlador temporal

En tu terminal, crea un nuevo controlador:

```terminal
symfony console make:controller
```

Nómbralo `LazyController`, y no hace falta que hagas pruebas.

Abre el nuevo controlador en `src/Controller/LazyController.php`. Vale, tenemos este método `index()` cuya ruta está establecida en `/lazy`. Inyecta`StarshipPartRepository $repository` en él. Luego, coge la primera parte del repositorio con `$part = $repository->find(1)`. Volcarlo con `dump($part)`:

[[[ code('6978bbd0ba') ]]]

Ahora, regresa a nuestra aplicación y navega manualmente a la url `/lazy`.

## Explorando los objetos perezosos de Doctrine

Si observamos la barra de herramientas de depuración web, veremos una única consulta. Es la que obtiene el objeto `StarshipPart`. Si abrimos el panel del perfilador de volcado, veremos el objeto obtenido`StarshipPart`. Fíjate en la propiedad `starship`, su tipo es este extraño Proxy CG. Se trata de un objeto Doctrine lazy. Mira en su interior. Todas las propiedades están desactivadas excepto el ID. El ID es lo único que Doctrine conoce de`Starship` hasta que consulta a la base de datos el resto de los datos. Sólo cuando accede a una propiedad de `Starship`, Doctrine lanza una segunda consulta para obtener el resto.

¡Vamos a lanzar esta segunda consulta! En `LazyController::index()`, añade`$part->getStarship()->getName()` como primer argumento a `dump()`:

[[[ code('1a9adbf178') ]]]

Actualiza la página `/lazy`... Ahora hay dos consultas. La primera busca el `StarshipPart`, y la segunda busca el `Starship` porque hemos accedido a su nombre.

En el panel de volcado, podemos ver que el `Starship` está ahora completamente cargado con todas sus propiedades.

Así que este Proxy CG es una clase real que Doctrine genera sobre la marcha. Contiene toda la lógica para obtener los datos cuando sea necesario. La clave está en que extiende la entidad real `Starship`. Así es como puede utilizarse como sustituta de `Starship` hasta que se necesiten los datos reales. También por eso las entidades no pueden ser finales, necesitan ser extensibles por estas clases proxy.

Como puedes imaginar, la lógica para hacer todo esto es bastante compleja y difícil de mantener. Pero... todo eso cambió en PHP 8.4, que introdujo objetos lazy nativos en el propio PHP.

Y Doctrine Bundle 3, ¡permite que nuestras aplicaciones Symfony se aprovechen de ello! Así que ¡a actualizar!

## Actualización del bundle Doctrine

En primer lugar, abre nuestro `composer.json`. Busca donde requerimos el`doctrine-bundle`. Cambia su versión a `^3.0`.

Ahora, en el terminal, ejecuta:

```terminal
symfony composer update
```

Oooo, un error de Composer. No se han podido resolver nuestras dependencias... `composer.json`
requiere `doctrine-bundle` 3. Sí... `doctrine-bundle` 3 requiere `doctrine/dbal` 4 pero esto entra en conflicto con nuestro requisito de `doctrine/dbal` 3.

Como referencia, `doctrine/dbal` es la capa de abstracción de bases de datos que Doctrine utiliza para comunicarse con diferentes bases de datos.

Comprobemos nuestro `composer.json`. Sólo requerimos `dbal` versión 3, pero`doctrine-bundle` necesita la versión 4 según ese error.

Vale, esto es un problema heredado. Las versiones anteriores de `doctrine-bundle` no soportaban `dbal` 4, por lo que teníamos que asegurarnos de que se utilizaba la versión 3. Esto ya no es necesario, así que podemos eliminarlo por completo y dejar que `doctrine-bundle` decida qué versión utilizar.

Vuelve a intentar la actualización...

Otro error, pero diferente. Desplázate un poco hacia arriba... podemos ver que `doctrine-bundle`3 y `dbal` 4 se instalaron correctamente.

El error se produjo al intentar borrar la caché. Parece que nuestra configuración de`doctrine` está utilizando algunas opciones que ya no son compatibles.

Podríamos arreglarlas manualmente, pero estoy bastante seguro de que actualizar la receta Flex lo resolverá.

Necesitamos un estado git limpio antes de actualizar la receta, así que vamos a comprobar nuestro`git status`. Tenemos algunos cambios modificados y algunos archivos sin seguimiento. Ejecuta:

```terminal
git add .
```

Ejecuta: Para rastrearlo todo y vuelve a ejecutar `git status`. Ahora que todo está rastreado, podemos confirmarlo con:

```terminal
git commit -a -m "update doctrine-bundle"
```

`git status` de nuevo para confirmar que estamos limpios. Por último, actualiza la receta con:

```terminal
symfony composer recipe:update
```

Efectivamente, ha encontrado una actualización para `doctrine-bundle`. ¡Aplícala!

Ejecuta `git status` para ver los cambios. Perfecto, ha actualizado el archivo `doctrine.yaml`.

## Comprobación de los cambios

De vuelta a nuestro código, abre `config/packages/doctrine.yaml`. En primer lugar, ha eliminado 3 opciones de configuración. Éstas eran las que provocaban el error al borrar la caché.

A continuación, parece que ha cambiado la estrategia de nomenclatura por defecto... y ha eliminado algunas opciones de configuración del controlador.

Debajo de la configuración de producción, eliminó las opciones `auto_generate_proxy_classes` y`proxy_dir`. Con el sistema de objetos perezosos original, en producción, Doctrine generaba archivos para las clases proxy con el fin de mejorar el rendimiento.

¡Nada de eso es ya necesario con los objetos perezosos nativos!

Bien, veamos qué aspecto tienen ahora nuestros objetos perezosos. En primer lugar, vuelve a`LazyController:index()` y elimina el primer argumento de `dump()`:

[[[ code('5f1058052a') ]]]

Esto debería volcar la parte con una instancia de nave estelar que no está completamente cargada.

Actualiza la página `/lazy` en tu navegador. Vale, sólo una consulta, que es lo esperado. Abre el panel de depuración y comprueba la propiedad `starship`. Ahora es nuestra entidad Starship normal, no esa clase proxy generada.

Pero ¡mira dentro! Todas las propiedades están sin establecer excepto el ID. Esto se parece a lo que vimos en la antigua clase proxy. ¡Esto son objetos perezosos nativos en acción!

De vuelta en `LazyController::index()`, vuelve a añadir el `$part->getStarship()->getName()` al `dump()`...

[[[ code('5b465d6980') ]]]

y vuelve a actualizar la página `/lazy`. Dos consultas, y si miramos la propiedad `starship` en el panel de volcado. Sigue siendo sólo nuestra entidad `Starship` normal... y si la expandimos... ¡se cargan todas sus propiedades!

## Finalizando Entidades

Vale... esto mola, pero ¿qué significa realmente para mí como desarrollador? Bueno, probablemente haya alguna mejora de rendimiento con los objetos lazy nativos.

Pero la principal conclusión es que ahora podemos, por fin, marcar nuestras entidades como `final`. Así que, sí, no es que nos cambie la vida, pero está bien que ya no necesitemos una solución complicada.

Así que... ¡marquemos nuestras entidades como finales!

`src/Entity/Droid.php`final:

[[[ code('81207aef19') ]]]

`Starship.php`: final:

[[[ code('325bfc7d86') ]]]

`StarshipDroid.php`: final:

[[[ code('348d6467e5') ]]]

y finalmente `StarshipPart.php`: final:

[[[ code('ae68f7dcbf') ]]]

Actualiza nuestra página perezosa... y... ¡todo sigue funcionando!

Ya casi estamos listos para dar el salto a Symfony 8, pero antes de hacerlo, volvamos a las desaprobaciones.
