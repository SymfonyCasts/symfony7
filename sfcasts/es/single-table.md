# Herencia de tabla única

Muy bien, para poder obtener todas las naves estelares a la vez, tenemos que utilizar el segundo tipo de herencia de Doctrine: la herencia de tabla única: Con esta función, todas las entidades que forman parte de la jerarquía se almacenan en una única tabla. Esto significa que no tenemos una tabla única para cada entidad, sino que todas las entidades de esa jerarquía forman parte de la misma tabla.

Y su implementación no requiere grandes cambios en nuestro código.

## Modificar la entidad Nave estelar

Lo primero que tienes que hacer es encontrar la entidad padre en tu jerarquía. Ésta es la entidad de la que se extienden todas las demás entidades de nuestra jerarquía.

En nuestro caso, es `Starship`, así que ábrela. Vuelve a cambiar el atributo `MappedSuperclass` por `Entity`. Puede parecer un poco extraño porque es abstracto, pero a efectos de consulta, es una entidad. Además, vuelve a añadir `repositoryClass: StarshipRepository::class` como argumento.

A continuación, añade el atributo `#[ORM\InheritanceType('SINGLE_TABLE')]` a la clase. Uy, aquí me sobra `ORM`. Este atributo indica a Doctrine que esta entidad utiliza la herencia de tabla única.

Como se trata de una tabla única, Doctrine necesita saber qué tipo de entidad está asociada a cada fila. En nuestro caso, podría ser un `freighter` o un`scout`. Para ello, necesitamos configurar una columna Discriminador.

Añade el atributo `#[ORM\DiscriminatorColumn]`. Indaga en él para echar un vistazo a su firma. Se parece bastante al atributo normal `Column` que estás acostumbrado a utilizar. Y, sí, define una columna en la base de datos. Así que puedes utilizar este atributo para configurar su esquema.

Establece el nombre en `ship_type` y el tipo en `string`.

Una cosa importante a tener en cuenta. Esta columna sólo la utiliza Doctrine internamente, no es algo a lo que puedas acceder desde tu entidad. Esto es un fastidio, porque estaría bien poder acceder a ella desde nuestro código. Más adelante veremos cómo solucionarlo.

## Configurar el Mapa Discriminador

Por último, necesitamos el Mapa Discriminador. Así es como Doctrine sabe qué clase de entidad debe instanciar cuando obtiene una fila de la base de datos.

Añade el atributo `#[ORM\DiscriminatorMap]`. Toma una matriz en la que las claves son el valor almacenado en la columna del discriminador y los valores son las clases de entidad.

Así pues, añade la clave `freighter` y ponla en `Freighter::class`. A continuación, añade la clave `scout` y ponla en `Scout::class`.

Aunque nuestro `Starship` es abstracto aquí (lo que significa que no puedes instanciarlo), eso no es una limitación. Si quisieras, podrías instanciar y guardar `Starships` sin un tipo hijo. Lo único que tendrías que hacer es eliminar el abstracto e incluirlo en el mapa discriminador.

## Actualizar el esquema de la base de datos

Veamos qué aspecto tiene este cambio como actualización del esquema de nuestra base de datos. En tu terminal, ejecuta:

```terminal
symfony console doctrine:schema:update --dump-sql
```

Verás que se eliminan las tablas `freighter` y `scout` que teníamos cuando utilizábamos `MappedSuperclass`. Ya no son necesarias porque ahora tenemos una única tabla `starship` que contiene todas las propiedades de nuestro`starship` y sus entidades hijas.

Fíjate en el campo `ship_type`, es la columna discriminadora que hemos configurado. Fíjate también en los campos `cargo_capacity` y`sensor_range`. Todas las propiedades de nuestras entidades hijas se almacenan ahora en la base de datos de esta forma. Una cosa a tener en cuenta es que cuando configuramos nuestras entidades `scout` y `freighter`, hicimos que sus propiedades no fueran anulables. Con la herencia de tabla única, Doctrine las hace anulables porque no todas las entidades tendrán esas propiedades, y necesitan ser `null`si éste es el caso.

## Recargar la base de datos con elementos fijos

¡Vamos a recargar nuestras fixture! Ejecuta:

```terminal
symfony console foundry:load-fixtures
```

Impresionante, no es necesario hacer cambios en nuestras fijaciones.

Para ver el aspecto de nuestra tabla `starship`, ejecuta:

```terminal
symfony console doctrine:query:sql 'select * from starship'
```

Ten en cuenta que este comando ha sido sustituido por `dbal:run-sql` en versiones posteriores de Doctrine. Así que, si este comando no funciona, utiliza `dbal:run-sql`.

La salida es bastante burda, pero podemos distinguir un poco lo que ocurre. La columna `ship_type` está aquí, y podemos ver que está establecida en `freighter` para las 3 primeras filas y en `scout` para las 3 últimas. La columna `cargo_capacity` sólo está poblada para las filas `freighter`, y la columna `sensor_range` sólo tiene valores para las filas `scout`. Esto es exactamente lo que esperábamos

## Ajustar el controlador de la página de inicio

Bien, ¡es hora de consultarlas todas a la vez! Dirígete a `MainController::homepage()` e inyecta `StarshipRepository` en lugar de `ScoutRepository`.

Esta variable `$ships` será ahora una matriz de entidades `Scout` y `Freighter`.

Salta al navegador y actualiza la página de inicio. ¡Genial! ¡6 naves!

Abre el panel del perfilador Doctrine y despliega la consulta formateada. Su aspecto es similar al de una consulta normal `findAll`, pero incluye nuestra columna discriminadora `starship_type`y la utiliza para filtrar los tipos `freighter` y `scout`.

Puedes seguir utilizando el `ScoutRepository` y el`FreighterRepository`. Puedes inyectarlos y utilizarlos de forma normal, pero ten en cuenta que sólo funcionarán con entidades `Scout` si utilizas el`ScoutRepository`.

## Considerando los inconvenientes

La herencia de tabla única tiene algunos inconvenientes. En primer lugar, tu base de datos no puede tener campos anulables para las propiedades de la entidad hija. En segundo lugar, si tienes muchos hijos diferentes, cada uno con sus propias propiedades personalizadas, acabarás con un montón de campos vacíos en tu base de datos. Puede que no sean problemas importantes, pero tienen solución con el siguiente tipo de herencia, ¡que exploraremos a continuación!
