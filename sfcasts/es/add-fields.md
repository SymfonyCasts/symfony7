# Actualización de la Nave Estelar: Añadir campos Slug y Timestamp

Han llegado nuevos requisitos de los almirantes del Cuartel General de la Flota Estelar. En lugar de ver el `id` en la URL, como `/starship/1`, quieren ver un nombre legible por humanos, como `/starship/enterprise`. Para conseguirlo, tenemos que añadir un nuevo campo a nuestra entidad `Starship`.

Podríamos añadirlo a mano, superfácilmente: añadir la propiedad, el getter, el setter y el atributo `#[ORM\Column]`. ¡O podemos hacer trampas! Ejecuta:

```terminal
symfony console make:entity Starship
```

En lugar de crear una nueva entidad, esta vez añadiremos campos a una entidad existente. Añade `slug`, tipo `string`, longitud `255`. "¿Debería ser anulable?" - no, pero elige`yes` por ahora. Añadamos otros 2 campos útiles: `updatedAt`, `datetime_immutable`, ¿anulable? sí, temporalmente, y `createdAt`, `datetime_immutable`, ¿anulable? sí.

Pulsa `Enter` para salir del comando. Antes de crear la migración, ve a comprobar la entidad`Starship`: `src/Entity/Starship.php`. ¡Genial! E incluso podemos eliminar`length: 255` por `$slug`. Eso es por defecto.

Nuevo campo, ¡comprobado! Pero, ¿existe la nueva columna en la base de datos? No! Eso es tarea para una migración.

En tu terminal, crea una con:

```terminal
symfony console make:migration
```

Abre el nuevo archivo de migración. Recuerda que las migraciones se generan comparando la base de datos con la clase de entidad. Doctrine ve los nuevos campos en la clase, no ve las columnas correspondientes en la tabla, genera el SQL para arreglar eso y lo anida con seguridad en el método `up()`. Es opcional, pero añadamos una descripción: "Añadir slug y timestamps a starship".

En tu terminal, ejecuta la migración:

```terminal
symfony console doctrine:migrations:migrate
```

¡Éxito! Se han añadido las nuevas columnas. Pruébalo ejecutando:

```terminal
symfony console doctrine:query:sql 'SELECT name, slug, updated_at, created_at FROM starship'
```

¡Sí! Las columnas están ahí, pero aún vacías. Con el tiempo, configuraremos Doctrine para que establezca automáticamente estos campos por nosotros. Pero antes, todas estas columnas deben ser obligatorias en la base de datos, lo que en Doctrine se conoce como `nullable: false`.

Abre `Starship`. Encima de `$slug`, elimina `nullable: true`. Esto significa ahora`nullable: false`: ése es el valor por defecto. En otras palabras, esto le dice a Doctrine que la columna debe ser obligatoria en la base de datos.

Establece también `unique: true` para que sea una columna única. Para `$updatedAt` y `$createdAt`, elimina también `nullable: true`.

Una vez más, hemos realizado cambios en nuestra entidad que no se reflejan en la base de datos ¡Hora de migrar! Ejecuta:

```terminal
symfony console make:migration
```

Abre la nueva migración. ¡Genial! En el método `up()`, altera las tres columnas para convertirlas en `NOT NULL` y crea un índice único en la columna `slug`. Añade una descripción: "Hacer que slug y timestamps no sean anulables".

En el terminal, ¡ejecútalo!

```terminal
symfony console doctrine:migrations:migrate
```

¡Error! Estos campos no se pueden establecer en `NOT NULL` porque contienen valores de `null`. ¡Doh! Esta es una situación complicada en la que tenemos que hacer las cosas en 3 pasos: añadir las nuevas columnas, darles un valor a cada una y luego hacerlas `NOT NULL`.

Vuelve a abrir la última migración. La mayoría de las veces, Doctrine hace todo el trabajo por nosotros, pero podemos añadir nuestro propio SQL a una migración.

En el método `up()`, antes del SQL generado, escribe`$this->addSql('UPDATE starship SET slug = id, updated_at = arrived_at, created_at = arrived_at')`.

Desmenucemos esto. Estamos actualizando la tabla de naves estelares, haciendo que `slug` sea igual a `id`. ¿Por qué? Porque `id` es único y no nulo, exactamente lo que necesitamos para `slug`. También estamos haciendo que`updated_at` y `created_at` sean iguales a `arrived_at`. Sabemos que `arrived_at` también es una marca de tiempo y no nula.

De nuevo en el terminal, vuelve a ejecutar las migraciones:

```terminal
symfony console doctrine:migrations:migrate
```

¡Ha funcionado! Ejecuta de nuevo la consulta para ver los datos:

```terminal
symfony console doctrine:query:sql 'SELECT name, slug, updated_at, created_at FROM starship'
```

¡Fíjate! Tres nuevos campos rellenados con datos.

Pero ahora tenemos un problema. ¡Maldita sea! Recarga los accesorios:

```terminal
symfony console doctrine:fixtures:load
```

¡Explosión! No hay nada en nuestros dispositivos que establezca estos tres campos obligatorios.

Podríamos actualizar nuestro `StarshipFactory` para establecer valores por defecto para estos campos... pero quiero mostrarte una forma diferente: un paquete de "extensión de doctrina" que puede establecerlos automáticamente. Es lo mejor, ¡y es lo siguiente!
