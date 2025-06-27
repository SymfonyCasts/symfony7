# Eliminación de huérfanos

Cuando utilizamos `make:entity` para añadir una relación, nos preguntó por`orphanRemoval`. Es hora de averiguar qué es y cuándo utilizarlo.

En los accesorios, empieza con `$starshipPart = StarshipPartFactory::createOne()`. Para que destaque, lo convertiré en un elemento crucial para cualquier viaje espacial: "Papel higiénico" Sí, un guiño descarado a los tiempos de la pandemia. ¡Qué asco!

Asigna esta parte al `Starship` anterior (añade el `$ship =` que falta) y luego vierte `$starshipPart`:

[[[ code('001d4573f7') ]]]

Hasta aquí todo bien: nada del otro mundo. Prueba a recargar los archivos fijos:

```terminal-silent
symfony console doctrine:fixtures:load
```

No hay errores y, por primera vez, vemos el objeto proxy que he mencionado.

## Desvelar el objeto proxy

Recuerda: cuando creas un objeto a través de Foundry, te devuelve tu nuevo y brillante objeto, pero está empaquetado dentro de otro objeto llamado proxy. La mayoría de las veces no te das cuenta ni te importa: todas las llamadas a métodos del proxy se reenvían al objeto real.

Pero como quiero dejar las cosas muy claras, extrae el objeto real tanto de`$ship` como de `$starshipPart` utilizando `_real()`:

[[[ code('722d63280c') ]]]

Ejecuta de nuevo las fijaciones:

```terminal-silent
symfony console doctrine:fixtures:load
```

Y... todo sin problemas. Sin el proxy, podemos ver que el `StarshipPart` está efectivamente vinculado al `Starship` correcto -el USS Espresso- que creamos antes. Hasta aquí, ¡todo en orden!

## Eliminación de una parte de una nave estelar: La trama se complica

Pero, ¿y si tenemos que borrar una `StarshipPart`? Normalmente, diríamos `$manager->remove($starshipPart)`, luego `$manager->flush()`. Pero vamos a mezclar las cosas: eliminemos simplemente la pieza de su nave:`$ship->removePart($starshipPart)`:

[[[ code('fe77652c62') ]]]

¿Qué crees que ocurrirá? ¿Se borrará la pieza? ¿O simplemente la eliminará de la nave? En ese caso, la pieza se quedará flotando en el espacio, se convertirá en huérfana. Pruébalo:

```terminal-silent
symfony console doctrine:fixtures:load
```

Explota con nuestro favorito:

> `starship_id` no puede ser nulo. 

## Arreglar el error nulo

¿Por qué ocurre esto? Cuando llamamos a `removePart()`, establece el `Starship` como nulo. Pero hicimos que eso no estuviera permitido con `nullable: false`: cada pieza debe pertenecer a una nave. ¿La solución? Bueno, depende de lo que quieras: ¿queremos permitir que las piezas queden huérfanas? ¡Genial! Cambia `nullable` a verdadero en `StarshipPart` y haz una migración.

O tal vez, si una pieza se retira repentinamente de su nave, queremos eliminar esa pieza por completo de la base de datos. Tal vez el propietario de la nave no sea un gran aficionado al reciclaje. Para ello, dirígete a `Starship` y añade `orphanRemoval: true`a `OneToMany`:

[[[ code('3e1585083f') ]]]

Retrocede y vuelve a cargar los accesorios:

```terminal-silent
symfony console doctrine:fixtures:load
```

¡No hay errores a la vista! El ID es nulo porque se ha borrado por completo de la base de datos. Así que `orphanRemoval` significa:

> Oye, si alguna de estas piezas se queda huérfana, tírala al
> incinerador.

Siguiente paso: exploraremos una forma de controlar el orden de una relación, como hacer que `$ship->getParts()` devuelva alfabéticamente.
