# Herencia de tablas de clases

Es hora de sumergirnos en el último tipo de Herencia Doctrine: la Herencia de Tabla de Clase. Ésta vuelve a utilizar una única tabla por entidad en la jerarquía. Pero aquí está el problema: en la tabla sólo se almacenan las propiedades específicas de esa entidad (además del id). Al buscar, Doctrine realiza las uniones necesarias entre bastidores para obtener todos los datos de la entidad que estás buscando.

En Starship, en el atributo `InheritanceType`, cambia `SINGLE_TABLE`por `JOINED`. Doctrine utilizaba tradicionalmente `JOINED` para referirse a este tipo de herencia, pero se conoce más comúnmente como Herencia de tabla de clases. Ya está

***NOTE
Aunque este cambio de código es superfácil, los cambios en la base de datos no lo son. Si tuvieras una base de datos ya establecida con datos en ella, tendrías que hacer algunas migraciones cuidadosas.
***

Para mostrar mejor cómo cambia el SQL, eliminemos el esquema y empecemos de cero. En el terminal, ejecuta

```terminal
symfony console doctrine:schema:drop --force
```

Ahora, crea el esquema con:

```terminal
symfony console doctrine:schema:create --dump-sql
```

## Observar los cambios

Veamos lo que tenemos aquí. La tabla del carguero sólo tiene `id` y `cargo_capacity`. La tabla `starship` tiene todas las propiedades comunes, y la tabla `scout` sólo tiene `id`y `sensor_range`.

Carguemos nuestros accesorios para asegurarnos de que todo sigue funcionando.

```terminal
symfony console foundry:load-fixtures
```

Se ve bien. Salta a la aplicación y actualiza. ¡Perfecto! ¡Seguimos teniendo seis naves!

## Comprobación de la base de datos

Ahora, echemos un vistazo a la base de datos para ver con qué estamos tratando. De nuevo en el terminal, ejecuta:

```terminal
symfony console doctrine:query:sql 'select * from starship'
```

***NOTE
Recuerda que este comando ha cambiado a `dbal:run-sql` en las nuevas versiones de Doctrine.
***

Como puedes ver, sólo contiene las columnas de la entidad `Starship`, no las de`Freighter` y `Scout`. Sin embargo, sigue incluyendo una `ship_type`. Y sí, tenemos tres cargueros y tres exploradores. Si queremos ver las propiedades específicas de éstos, están en sus respectivas tablas.

Veamos la tabla de los cargueros. En primer lugar, fíjate en la columna `id` para los cargueros, 1, 2 y 3. Deberían coincidir con los identificadores de la tabla de cargueros. Ejecuta:

```terminal
symfony console doctrine:query:sql 'select * from freighter'
```

Esto sólo tiene los `cargo_capacity`'s de los tres cargueros, y efectivamente, los ids coinciden con los ids de la tabla de naves estelares. Así, cuando Doctrine construye (o hidrata) una nave estelar, sabe que la nave con id 1 es un carguero. Entonces instanciará un objeto `Freighter`, tomará las propiedades comunes de la tabla de naves estelares, se unirá con la tabla de cargueros en el id y tomará la capacidad de carga. Uf, ¡me alegro de que Doctrine haga todo eso por nosotros!

## Inspección del perfilador

Volviendo a la aplicación, tomémonos un momento para comprobar el perfilador y ver esto en acción. Visualiza la consulta formateada para tener una mejor visión. La consulta es un poco más compleja que antes, debido a las uniones, pero hace exactamente lo que esperábamos.

## Añadir una nueva entidad

Vamos a subir la apuesta con un escenario un poco más avanzado. Vamos a crear otra nave: un carguero minero. Será una subclase de `Freighter`, lo que nos llevará a otro nivel de profundidad en el árbol de herencia.

De vuelta al terminal, crea la entidad con:

```terminal
symfony console make:entity MiningFreighter
```

Dale una propiedad llamada `laserPower`, un número entero, no anulable, y listo. Ahora la fábrica de la Fundición:

```terminal
symfony console make:factory
```

Elige `all` para crear el `MiningFreighter` que falta.

De vuelta en nuestro IDE, abre nuestra nueva entidad `MiningFreighter`. Haz que extienda `Freighter` y elimina la propiedad `id` y el getter.

## Actualizar la fábrica

Ahora, abre el `MiningFreighterFactory`. Tenemos que hacer algunos ajustes en el `FreighterFactory` antes de poder extenderlo, así que ábrelo.

Elimina el `final` para que podamos ampliarlo. A continuación, tenemos que añadir una plantilla para que las subfábricas puedan especificar el tipo de entidad que están creando. En el docblock de la clase, añade`@template T of Freighter`. Luego, en `@extends`, amplíala a `T`.

De vuelta en `MiningFreighterFactory`, haz que la clase extienda a `FreighterFactory`. Haz lo mismo con `@extends` en el docblock.

Abajo, en `defatuls()`, añade nuestro truco `array_merge`. `array_merge(parent::defaults()`, el segundo argumento, el array, y no olvides cerrar la función.

Aquí tenemos una genial creación de `array_merge`. `MiningFreighterFactory` se está fusionando con los valores por defecto de `FreighterFactory`, que a su vez se está fusionando con los valores por defecto de `StarshipFactory`.

## Crear algunos datos

A continuación, en `AppStory`, crea unos cuantos cargueros mineros con `MiningFreighterFactory::createMany(2)`.

De vuelta al terminal, vuelve a cargar los accesorios.

```terminal-silent
symfony console foundry:load-fixtures
```

Uy, tenemos un error... "La entidad MiningFreighter tiene que formar parte del mapa discriminador de Starship para estar correctamente mapeada en la jerarquía de herencia"

Este es un paso habitual que se olvida al añadir nuevas entidades a una jerarquía de herencia.

De vuelta en `Starship`, en la matriz `DiscriminatorMap`, añade nuestra nueva entidad con`'mining_freighter' => MiningFreighter::class`. Me gusta utilizar snake case para las claves, pero puedes utilizar lo que quieras. Lo importante es que el valor sea el nombre de la clase de la entidad.

Ahora carga de nuevo nuestros accesorios.

```terminal-silent
symfony console foundry:load-fixtures
```

Bien, ¡ha funcionado! Actualiza nuestra aplicación... ¡y voilá! ¡Ya tenemos ocho naves!

## Conclusión

Vuelve a echar un vistazo a la consulta en el perfilador. Se ha añadido otro join para la tabla `mining_freighter`.

Este es uno de los contras de la herencia de tablas de clases, las consultas se vuelven más complejas cuantas más entidades tengas en tu jerarquía. Esto puede reducir el rendimiento.

Otro contra en el que quizá no pienses es que, si utilizas herramientas externas para consultar y manipular tu base de datos, es más difícil trabajar con ella. Tienes que duplicar las uniones y la lógica que Doctrine utiliza internamente.

Como en la mayoría de las cosas, hay ventajas y desventajas con cada tipo de herencia.

A continuación, veremos cómo consultar los distintos tipos de naves estelares.
