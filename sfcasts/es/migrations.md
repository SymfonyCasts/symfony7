# Migraciones

Tenemos una entidad `Starship`... ¡pero no una tabla `starship`! ¿La solución? ¡Migraciones de base de datos!

## `make:migration`

Crea nuestra primera migración ejecutando:

```terminal
symfony console make:migration
```

¡Éxito! Esto no añadió la tabla real, pero sí creó un nuevo archivo en el directorio `migrations/`. ¡Vamos a comprobarlo!

Ooh, es una clase PHP en la que el método `up()` contiene el SQL para crear nuestra tabla. Lo interesante es cómo se creó: Doctrine comparó el estado actual de nuestras entidades con la base de datos y generó el SQL necesario para hacerlas coincidir. ¡Vaya!

[[[ code('08456e3596') ]]]

También hay un método `down()`... porque las migraciones pueden invertirse, pero yo nunca lo he hecho, así que no me preocupo por `down()`.

Una cosa a tener en cuenta sobre el SQL: está en el formato de la plataforma de base de datos que estés utilizando. En nuestro caso, SQL específico de Postgres. Si usaras SQLite, verías SQL específico de SQLite.

Si quieres, añade una nota sobre lo que hace esto en `getDescription()`:`return 'Add starship table'`.

[[[ code('b7872ce8d2') ]]]

## Comprobar el estado de la migración

Ve al terminal y ejecuta:

```terminal
symfony console doctrine:migrations:list
```

La salida es un poco confusa, pero podemos ver nuestra clase de migración y su descripción. El estado es `not migrated` porque aún no la hemos ejecutado. ¡Vamos a hacerlo!

```terminal
symfony console doctrine:migrations:migrate
```

¿Estamos seguros de que queremos continuar? ¡Sí! ¡Lo hemos conseguido! Inténtalo

```terminal
symfony console doctrine:migrations:list
```

otra vez. ¡Estado: `migrated`!

## Cómo funcionan las migraciones

Pero, ¿cómo hace Doctrine para saber qué migraciones se han ejecutado? Crea una tabla`doctrine_migration_versions`, y luego inserta una fila por cada migración una vez ejecutada.

¡Podemos verlo! Ejecuta:

```terminal
symfony console doctrine:query:sql 'select * from doctrine_migration_versions'
```

¡Mira esto! ¡Ahí está nuestra clase de migración, cuándo se ejecutó, cuánto tardó y el color favorito de la migración! Vale, esto último no.

¿Significa esto que tenemos nuestra tabla `starship`? ¡Ejecuta otra consulta SQL sin procesar para averiguarlo!

```terminal
symfony console doctrine:query:sql 'select * from starship'
```

> La consulta arrojó un conjunto de resultados vacío.

El verde significa bueno, ¿verdad? ¡Sí! Esto nos dice que no hay datos en la tabla `starship`... ¡pero existe!

Comprobación de la clase de entidad: ✅ Comprobación de la tabla de la base de datos: ✅ ¿Hay datos en la base de datos? ¡Aprendamos a hacerlo a continuación!
