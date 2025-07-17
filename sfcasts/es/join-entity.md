# Muchos-a-Muchos pero con Datos Extra

Las relaciones ManyToMany son el único lugar de Doctrine donde tenemos una tabla en nuestra base de datos - `starship_droid` - pero ninguna entidad correspondiente en nuestra aplicación.

Pero hay un problema: no podemos añadir columnas adicionales a esa tabla de unión. Por ejemplo, ¿qué pasaría si quisiéramos saber cuándo se asigna un droide a una nave estelar? Para ello, nuestra tabla de unión necesitaría una columna `assignedAt`. Pero no podemos añadirla

## Cuando muchos a muchos no es suficiente

La solución es arremangarnos y empezar a manejar las cosas de forma más manual.

Dejaremos de utilizar la relación muchos-a-muchos por completo. En su lugar, vamos a generar una nueva entidad que represente la tabla de unión. Primero, deshaz la relación muchos-a-muchos (pero preocúpate sólo de las propiedades, no de los métodos). En `Starship`, despídete de la propiedad`droids`:

[[[ code('08ca1145f3') ]]]

Y en `Droid`, haz lo mismo con la propiedad muchos-a-muchos `starships`:

[[[ code('30fee2522b') ]]]

Borra el constructor en ambos.

Busca tu terminal y ejecuta:

```terminal
symfony console doctrine:schema:update --dump-sql
```

Esto te muestra el aspecto que tendría tu migración si la generaras ahora mismo. Es lo que esperamos: no más tabla `starship_droid`.

## Crear una nueva entidad de unión

Pero ¡no generes todavía esa migración! Queremos la tabla de unión, pero ahora necesitamos crear una entidad que la represente. Ejecuta::

```terminal
symfony console make:entity StarshipDroid
```

`DroidAssignment` podría ser un nombre más adecuado, pero `StarshipDroid`nos ayuda a visualizar lo que estamos haciendo: recrear exactamente la misma relación de base de datos mediante dos `ManyToOne`s

Añade `assignedAt` junto con dos propiedades más para crear relaciones desde esta tabla de unión a `Starship` y `Droid`. 

Éstas serán relaciones `ManyToOne`, y conectarán`StarshipDroid` con `Starship` y `Droid`.

## La migración que no hace nada

Ahora, genera esa migración:

```terminal
symfony console make:migration
```

Y compruébalo. Puede parecer que hay muchos cambios, pero fíjate bien: sólo se eliminan las restricciones de clave foránea, se añade una clave primaria y se vuelve a crear la clave foránea:

[[[ code('ab36b5fa82') ]]]

Así que, al final, esta migración no cambia nada real en la base de datos. 

Ejecútala con:

```terminal
symfony console doctrine:migrations:migrate
```

Y ¡boom!

> La columna `assignedAt` no puede contener valores `null`. 

Doctrine está haciendo un berrinche debido a las filas existentes en la tabla`starship_droid`. Podemos apaciguarlo con un valor por defecto. Actualiza manualmente la migración a, por ejemplo, `DEFAULT NOW() NOT NULL`:

[[[ code('da05bc25ab') ]]]

## Los toques finales

Vamos a añadir un toque final a `StarshipDroid`:

[[[ code('c28ba6cf5b') ]]]

Este `assignedAt` no es realmente algo de lo que debamos preocuparnos. Crea un constructor y configúralo automáticamente: `$this->assignedAt = new \DateTimeImmutable();`:

[[[ code('740c1d4a35') ]]]

Espera, ¡porque esto es enorme! Ahora tenemos exactamente la misma relación en la base de datos que antes. Pero como hemos tomado el control de la entidad join, podemos añadirle nuevos campos. A continuación, veremos cómo asignar droides a naves estelares con esta nueva configuración de entidades. Y, finalmente, ¡nos pondremos elegantes y ocultaremos por completo este detalle de la implementación!
