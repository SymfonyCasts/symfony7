# Unir a través de una relación de muchos a muchos

¿Te has preguntado alguna vez en qué nave de nuestra flota hay más droides? Yo también ¡Pues vamos a averiguarlo! Quiero enumerar todas las naves en orden descendente según su número de droides.

Sumérgete en `src/Controller/MainController.php`. La consulta es:

```terminal
$ships = $repository->findIncomplete()
```

Haz clic en ese método y dale un nombre nuevo y elegante:`findIncompleteOrderedByDroidCount()`. Cópialo, vuelve al controlador y sustituye el antiguo nombre del método por el nuevo. 

Aún no hemos cambiado nada, así que una actualización rápida nos da lo mismo.

Para ordenar las naves estelares por su número de droides, tenemos que unir la tabla de unión hasta `droid`, agrupar por`starship`, y luego contar los droides. Guau. En realidad, ¡es bastante bonito!

En `StarshipRepository`, añade una unión izquierda. Pero no vamos a pensar en la tabla de unión ni en la base de datos. No, céntrate sólo en las relaciones en Doctrine. Así que estamos uniendo a través de `s`, que es nuestra nave estelar, y `droids`, la propiedad que tiene la relación ManyToMany con `Droid`. Por último, aliasamos esos droides como `droid`.

Para contar los droides, añade un `group by s.id`.

Para ordenar sustituye el `orderBy` existente por`orderBy('COUNT(droid)', 'ASC')`. 

Después, pulsa actualizar y ¡boom! En la parte superior, verás `droids none`. Pero a medida que te desplazas hacia abajo, el recuento de droides aumenta. Si eres lo suficientemente valiente como para aventurarte unas páginas más adelante, ¡empezaremos a ver naves estelares con dos, tres o incluso cuatro droides!

¿La clave? No hay nada especial en esta unión. Nos unimos a través de la propiedad y Doctrine se encarga del resto.

Si echas un vistazo a la consulta en esta página, verás que se encarga de todos los detalles. Busca `starship_droid` para encontrar la consulta. 
Esto es feo, pero si formateas la consulta, selecciona de `starship`, encargándose de la unión a la tabla de unión y uniéndose de nuevo a `droid`, Lo que nos permite contar y ordenar por ese recuento en esa tabla `droid`. Impresionante Doctrine, impresionante.

¡Eso es técnicamente todo para ManyToMany! Pero a continuación vamos a tratar un caso de uso más avanzado, pero aún común: añadir datos a la tabla join, como una fecha en la que el droide se unió a la nave estelar.
