# Persistir en cascada

Echa un vistazo a este error: ¡es una pasada!

> Se ha encontrado una entidad a través de la relación `Starship.droids` que no estaba
> configurada para operaciones de "persistencia en cascada" para la entidad `StarshipDroid`.

Déjame que te lo traduzca:

> Oye, estás guardando este `Starship` y tiene un `StarshipDroid`
> adjunta. Eso es genial, pero te olvidaste de decirme que persistiera la > entidad  
> `StarshipDroid`. ¿Qué quieres que haga?

Pero, de nuevo, desde dentro de `Starship`, no podemos hacer que el gestor de entidades diga `$manager->persist($starshipDroid)`. 

## Aprovechar el poder de `cascade=['persist']`

La solución es utilizar algo llamado persistir en cascada. 

Desplázate hasta la propiedad `$starshipDroids`, y busca la opción `OneToMany`. Añade una nueva opción: `cascade`. La escribiré manualmente. Establécela como una matriz con`persist` dentro:

[[[ code('db5674a8de') ]]]

Estamos creando una especie de efecto dominó. Si alguien persiste en este `starship`, vamos a enviar en cascada esa persistencia a todas las relaciones adjuntas. 

Una advertencia: utiliza este poder con prudencia. Hace que tu código sea más automático, lo cual es estupendo, pero también puede dificultar la detección de errores.

Pero en este caso, es exactamente la solución que necesitamos.

Dale otra vuelta a esas fijaciones:

```terminal-silent
symfony console doctrine:fixtures:load
```

## Volver a añadir droides

Ya estamos otra vez en marcha. Podemos volver a utilizar `ship->addDroid()`. Pero aún quiero crear una flota de `starships` con `droids` unido a ellos.

Elimina todo el código manual y vuelve a poner la propiedad `droids` en el`StarshipFactory`:

[[[ code('855236b449') ]]]

Vuelve a encender los dispositivos:

```terminal-silent
symfony console doctrine:fixtures:load
```

¿Adivina qué? ¡Funcionan! 

Entre bastidores, Foundry está llamando a `addDroid()` en cada `Starship` para cada `droid`. Y acabamos de demostrar que `addDroid()` vuelve a funcionar.

¡La creación de la entidad de unión `StarshipDroid` está ahora oculta a toda nuestra base de código!

## Control más fino con `assignedAt`

Pero, ¿y si quieres añadir un `droid` a un `starship` y controlar la propiedad `assignedAt`? Añade un argumento para `addDroid()` en `Starship`: un `DateTimeImmutable`. Hazlo opcional para mantener la flexibilidad. Entonces, después de crear el `StarshipDroid`, establece el `$assignedAt` si lo hemos pasado:

[[[ code('902c577144') ]]]

Genial... pero hay un pequeño problema. Foundry no nos permite controlar el campo `assignedAt`. Así que, si quieres asignar algún `droids` en un momento concreto, tendrás que coger el volante manualmente. 

## Visualización de `assignedAt`

Por último, hagamos que ese `assignedAt` sea visible en nuestro sitio. Para ello necesitaremos el objeto de entidad de unión`StarshipDroid`. Es un poco más de trabajo, pero totalmente factible.

Cambia el bucle a `for starshipDroid in ship.starshipDroids`. A continuación,`starshipDroid.droid.name` y `starshipDroid.assignedAt` con el filtro `ago`para darle estilo:

[[[ code('e882aa26f3') ]]]

Actualiza y... ya podemos ver cuándo se asignó cada `droid`. 

¡Eso es todo, amigos! Hemos explorado los rincones más profundos de las relaciones Doctrine, incluso las esquivas muchos-a-muchos con campos adicionales. Como siempre, si tienes alguna pregunta, déjala en los comentarios a continuación. ¡Estamos todos juntos en esto!
