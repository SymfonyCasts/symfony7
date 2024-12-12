# Añadir campos Slug y Timestamp

Vamos a añadir algunos campos nuevos a nuestra entidad `Starship`. El primer campo que me gustaría añadir es un `slug`. Será una versión slugificada del `name` -único, en minúsculas y sin espacios ni caracteres especiales, ¡perfecto para utilizarlo en nuestra URL en lugar del `id` que tenemos ahora!

Los otros dos campos son `updatedAt` y `createdAt`. `createdAt` se ajustará a la hora actual siempre que se cree una nueva entidad. `updatedAt` se ajustará a la hora actual siempre que cambie algo en la entidad.

Podemos utilizar `make:entity` para añadirlos Gira hasta tu consola y ejecuta:

```terminal
symfony console make:entity Starship
```

En lugar de crear una nueva entidad, ejecutar el comando con `Starship` como argumento nos permite añadir nuevos campos a esta entidad existente. Añade los campos `slug`, tipo`string`, longitud `255`. "¿Debería ser anulable?" - no, pero elige `yes` por ahora.`updatedAt`, `datetime_immutable`, ¿nulable? no, pero elige `yes` por ahora. lo mismo para `createdAt`.

Pulsa `Enter` para salir del comando. Antes de crear la migración, vamos a comprobar la entidad`Starship` para ver los nuevos campos. Abre `src/Entity/Starship.php`. Podemos eliminar`length: 255` para `$slug` ya que es el predeterminado.

Ahora, en tu terminal, crea una migración con:

```terminal
symfony console make:migration
```

Recuerda que las migraciones se generan comparando la base de datos actual y la entidad para encontrar los cambios. Doctrine detecta automáticamente los nuevos campos y genera el SQL correcto. De vuelta en nuestro IDE, abre la nueva migración. En el método `up()`, podemos ver que está alterando la tabla `starship` y añadiendo las tres columnas. Añade una descripción: "Añadir slug y timestamps a starship".

En tu terminal, ejecuta la migración:

```terminal
symfony console doctrine:migrations:migrate
```

¡Éxito! Se han añadido los nuevos campos, pero todos son `null`. Echa un vistazo ejecutando:

```terminal
symfony console doctrine:query:sql 'SELECT name, slug, updated_at, created_at FROM starship'
```

Estas tres columnas vacías están en blanco, lo que significa que son `null`.

Necesitamos que estos tres nuevos campos no sean nulos y que `slug` también sea único. Como tenemos datos existentes, tenemos que crear una segunda migración que primero actualice estos campos basándose en los datos existentes, y luego actualice la definición del campo.

Abre nuestra entidad `Starship`. Para el campo `$slug`, elimina `nullable: true` y establece `unique: true`. Para `$updatedAt` y `$createdAt`, elimina `nullable: true`.

En tu terminal, crea la segunda migración ejecutando:

```terminal
symfony console make:migration
```

En tu IDE, abre la nueva migración. En el método `up()`, podemos ver que está alterando los tres campos para convertirlos en `NOT NULL` y también está creando un índice único en el campo `slug`. Añade una descripción: "Hacer que slug y timestamps no sean anulables".

En el terminal, ejecuta la migración:

```terminal
symfony console doctrine:migrations:migrate
```

¡Error! Estos campos no se pueden establecer en `NOT NULL` porque contienen valores de `null`. Tenemos que actualizar nuestra segunda migración para asegurarnos de que estos campos tienen valores válidos. Abre de nuevo la migración. En el método`up()`, antes del SQL generado, escribe`$this->addSql('UPDATE starship SET slug = id, updated_at = arrived_at, created_at = arrived_at')`.

Vamos a descomprimir esto. Estamos actualizando la tabla Starship, estableciendo `slug` igual a `id`. ¿Por qué? Porque `id` es único y no nulo, exactamente lo que necesitamos para `slug`. También estamos estableciendo`updated_at` y `created_at` iguales a `arrived_at`. Sabemos que `arrived_at` también es una marca de tiempo y no nula.

De vuelta al terminal, ejecuta de nuevo la migración:

```terminal
symfony console doctrine:migrations:migrate
```

¡Ha funcionado! Ejecuta de nuevo la consulta para ver los datos:

```terminal
symfony console doctrine:query:sql 'SELECT name, slug, updated_at, created_at FROM starship'
```

¡Mira! Estos tres nuevos campos están ahora llenos de datos.

Cuando trabajamos en desarrollo como nosotros, no es gran cosa borrar nuestra base de datos y empezar de nuevo. Pero... en producción, no puedes simplemente borrar una tabla para añadir un nuevo campo obligatorio. Este truco de la doble migración puede ser útil en este caso.

Sin embargo, ahora tenemos un problema. Recarga los accesorios:

```terminal
symfony console doctrine:fixtures:load
```

¡Explosión! No hay nada en nuestros dispositivos que establezca estos tres campos obligatorios.

Podríamos actualizar nuestro `StarshipFactory` para establecer algunos valores por defecto para estos campos... pero quiero mostrar una forma diferente. Hay un paquete de "extensión de doctrina" que tiene ayudantes para establecer estos valores automáticamente. Veámoslo a continuación
