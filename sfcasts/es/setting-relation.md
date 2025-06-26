# Establecer la relación

Vale, pero ¿cómo establecemos realmente la relación? ¿Cómo decimos

> ¿Este `StarshipPart` pertenece a este `Starship`?

Hasta ahora, hemos estado trabajando en `AppFixtures` con Foundry. Volveremos a Foundry dentro de un rato, pero volvamos a la vieja escuela por un minuto para ver cómo funciona todo esto.

Empieza con `new Starship()`... luego pegaré algo de código para establecer las propiedades necesarias. A continuación, añade `$manager->persist($starship)`:

[[[ code('b1bcf47f80') ]]]

A continuación crea un nuevo `StarshipPart` y al igual que antes, pegaré código para rellenar las propiedades. A continuación, asegúrate de que esto se guarda con `$manager->persist($part)`, y por último, `$manager->flush()`:

[[[ code('3f9ebee20f') ]]]

Foundry suele llamar a `persist()` y `flush()` por nosotros. Pero como estamos en modo manual, tenemos que hacerlo nosotros.

Tenemos un `Starship` y un `StarshipPart`, pero aún no están relacionados. Pff, intenta cargarlos de todas formas. Dirígete a tu terminal y ejecuta:

```terminal
symfony console doctrine:fixtures:load
```

Rutro:

> `starship_id` no puede ser nulo en la tabla `starship_part`.

¿Por qué es necesaria esa columna? En `StarshipPart`, la propiedad `starship` tiene un atributo `ManyToOne`y otro `JoinColumn()`:

[[[ code('fa467d8ded') ]]]

Esto nos permite controlar la columna de clave foránea: `nullable: false` significa que todo`StarshipPart` debe pertenecer a un `Starship`.

## Asignación de la pieza a la nave

Entonces, ¿cómo decimos que esta pieza pertenece a este `Starship`? La respuesta es maravillosamente sencilla. En cualquier lugar antes de `flush()`, decimos`$part->setStarship($starship)`:

[[[ code('e528363598') ]]]

Eso es todo. Con Doctrine, no establecemos ninguna propiedad `starship_id` ni siquiera pasamos un ID, como `$starship->getId()`. No Fijamos objetos. Doctrine se encarga de los aburridos detalles de la inserción: primero guarda el `Starship`, luego utiliza su nuevo `id` para establecer la columna `starship_id` en la tabla `starship_part`. 

¡Qué listo!

Prueba de nuevo las fijaciones:

```terminal-silent
symfony console doctrine:fixtures:load
```

¡Sin errores! Comprueba las cosas:

```terminal
symfony console doctrine:query:sql 'SELECT * FROM starship_part'
```

¡Et voila! Ahí está nuestra pieza única, felizmente vinculada a `starship_id` 75. Compruébalo:

```terminal
symfony console doctrine:query:sql 'SELECT * FROM starship WHERE id = 75'
```

Ahí está: `Starship` id 75 tiene un `StarshipPart` id 1. ¡Somos geniales!

## Doctrine: trabaja con objetos, no con IDs

Estas son las conclusiones: con las relaciones Doctrine, estás en el mundo de los objetos. Olvídate de los ID. Doctrine se encarga de esa parte por ti. Tú fijas el objeto y Doctrine hace el resto.

Pero ugh, esto es mucho trabajo en `AppFixtures` para crear un único`Starship` y un único `StarshipPart`. Así que, a continuación, volvamos a utilizar Foundry para crear una flota de naves y un montón de piezas y enlazarlas todas de una sola vez. Aquí es donde Foundry brilla de verdad.
