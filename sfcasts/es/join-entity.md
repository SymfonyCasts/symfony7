# Muchos a muchos pero con datos adicionales

Próximamente...

Vamos a sumergirnos en el singular mundo de las relaciones de muchos a muchos en Doctrine. Imagina que tenemos una tabla en nuestra base de datos, digamos `StarshipDroid`. Ahora viene lo bueno: ¡no hay ninguna entidad correspondiente en tu proyecto! Es como una tabla fantasma que Doctrine maneja por sí sola. Impresionante, ¿no?

Pero hay una pega: no podemos añadir columnas adicionales a esa tabla de unión. Imagina que intentamos rastrear la fecha en que un droide fue asignado a una nave estelar. En el universo de las bases de datos, podríamos añadir una columna `assignedAt` a esa tabla de unión, pero en el reino de Doctrine, no podemos. 

## Cuando muchos a muchos es demasiado

En cuanto necesites datos adicionales en la tabla de unión, tendrás que arremangarte y empezar a manejar las cosas de forma más manual. Es como intentar meter una clavija cuadrada en un agujero redondo: vas a necesitar una nueva estrategia. 

Vas a dejar de utilizar por completo la relación muchos a muchos y, en su lugar, vamos a generar una nueva entidad que represente esa tabla de unión. Lo primero es lo primero, vamos a deshacer la relación muchos-a-muchos (pero preocúpate sólo de las propiedades, no de los métodos). En `Starship`, despídete de la propiedad`droids`, y en `Droid`, haz lo mismo con la propiedad muchos-a-muchos de `Starship`. Elimina el código del constructor en ambos: es como una limpieza de primavera para tu código. 

Ahora, saca tu terminal y ejecuta este ingenioso comando: 

```terminal
symfony console doctrine:schema:update --dump-sql
```

Es como una bola de cristal para tu código. Te muestra cómo sería tu migración si la generaras ahora mismo. Muy chulo, ¿eh?

## Crear una nueva entidad de unión

Antes de realizar la migración, vamos a crear la nueva entidad de unión,`StarshipDroid`. Ejecuta esta orden:

```terminal
symfony console make:entity StarshipDroid
```

Seguro que `DroidAssignment` sería un nombre más adecuado, pero `StarshipDroid`nos ayuda a visualizar lo que estamos haciendo: recrear exactamente la misma relación de base de datos. 

Ahora, vamos a añadir unas cuantas propiedades. ¿Recuerdas la columna `assignedAt` que queríamos? Vamos a añadirla, junto con dos propiedades más para crear relaciones desde esta tabla de unión a `Starship` y `Droid`. 

Éstas van a ser las relaciones `ManyToOne`. Son como el alma de nuestro código, ya que conectan `StarshipDroid` con `Starship` y `Droid`.

## Reunirlo todo

Ahora, vamos a generar esa migración. Ejecuta `symfony console make:migration`y compruébalo. Sí, puede parecer que hay muchos cambios, pero si entrecierras los ojos, verás que sólo se trata de eliminar las restricciones de clave foránea, añadir una clave primaria y volver a crear las restricciones de clave foránea. Es como un truco de magia: un montón de flashes, pero al final, todo está donde estaba.

Pero aún no hemos terminado. Vamos a ejecutar esta migración con `symfony console
doctrine:migrations:migrate`. Y ¡bum! `Column assignedAt cannot contain
null values`. Es como si Doctrine tuviera una rabieta por las filas existentes en nuestra tabla `StarshipDroid`. Pero no te preocupes, podemos apaciguarlo con un valor por defecto. Actualiza manualmente la migración a, por ejemplo, `DEFAULT
NOW() NOT NULL`. 

## Los toques finales

Añadamos un toque final a `StarshipDroid`. Este `assignedAt` no es realmente algo de lo que debamos preocuparnos. Es como el tic-tac de un reloj: debería seguir su propio ritmo. Creemos un constructor que lo ajuste automáticamente: `assignedAt = new \DateTimeImmutable();`. 

Y ¡voilá! Ahora tenemos exactamente la misma relación en la base de datos que antes. Pero hemos tomado el control de la entidad de unión, así que podemos añadirle nuevos campos. A continuación, veremos cómo asignar droides a naves estelares con esta nueva configuración de entidades. Y, finalmente, nos pondremos elegantes y ocultaremos por completo este detalle de implementación. Abróchate el cinturón, ¡va a ser un viaje divertido!
