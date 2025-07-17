# Persistencia de la relación muchos-muchos más compleja

Hemos refactorizado nuestra relación de muchos a muchos para incluir una entidad de unión llamada `StarshipDroid`, en lugar de confiar en que Doctrine cree la tabla de unión por nosotros. Recarga nuestras instalaciones, pero no te quites el sombrero:

```terminal-silent
symfony console doctrine:fixtures:load
```

¡Error!

> Propiedad no definida: `App\Entity\Starship::$droids`

Este error está siendo escupido desde `Starship` línea 205. ¿El culpable? Nuestro método`getDroids()`. Pues duh, ¡acabamos de eliminar la propiedad `droids`! La solución rápida, duh de nuevo, simplemente coméntalo:

[[[ code('5f74c3e3e2') ]]]

Y ¡hurra! Las fijaciones vuelven a funcionar:

```terminal-silent
symfony console doctrine:fixtures:load
```

## Creación de la entidad de unión

Para descubrir el arreglo correcto, hagamos algunas cosas manualmente:`$ship = StarshipFactory`, podríamos utilizar `createOne()`, pero en su lugar cojamos uno al azar. Utiliza también el truco de `_real()` para obtener el objeto real, no un proxy. Luego haz lo mismo con`$droid = DroidFactory`, de nuevo cogiendo uno al azar y llamando a`_real()` con él:

[[[ code('906ff98ce0') ]]]

## Relacionar a través de la Entidad de Unión

Antes podíamos utilizar `$ship->addDroid($droid)` para añadir un droide a`Starship`. Pero ¡ya no! Está haciendo referencia a la obsoleta propiedad `droids`. Ahora se llama `starshipDroids`, y como habrás adivinado, es una colección de entidades `StarshipDroid`. Deshazte de`$ship->addDroid()` y en su lugar di que `$starshipDroid` es igual a`new StarshipDroid()`, luego `$starshipDroid->setDroid()`, no `$ship` sino `$droid`. Y pon `$starshipDroid->setStarship($ship)`.

Estamos creando manualmente la entidad y estableciendo esas relaciones de muchos a uno. Por último, como las estamos ensamblando a mano, necesitamos persistirlas y vaciarlas utilizando `$manager->persist($starshipDroid)`, y `$manager->flush()`:

[[[ code('eb8ff242d6') ]]]

Sin duda es más trabajo, pero es bastante sencillo. Dale una vuelta a los accesorios:

```terminal-silent
symfony console doctrine:fixtures:load
```

Y echa un vistazo a la base de datos con:

```terminal
symfony console doctrine:query:sql "SELECT * FROM starship_droid"
```

Estamos seleccionando en esa tabla de unión y ¡sí! Una entrada para el `Starship`, y otra para el `Droid`. Hasta aquí, todo bien. Actualiza la página de inicio. ¡Otro error!

> [Error semántico] línea 0, col 55 cerca de 'droids WHERE':
> La clase App\Entity\Starship no tiene ninguna asociación llamada droids.

Parece que tenemos un problema de consulta entre manos.

## Solucionar el problema de consulta

Es hora de arremangarse y sumergirse en `src/Repository/StarshipRepository`. Nuestra unión está teniendo un pequeño problema. Nos estamos uniendo a `s.droids`, pero la propiedad `droids`ha abandonado el edificio. Tenemos que unirnos a `starshipDroids`. Cambia `s.droids`por `s.starshipDroids`. Y para mayor claridad, llámalo `starshipDroid`, porque es lo que realmente es. Ahora cuéntalos en lugar del inexistente `droids`:

[[[ code('5decc8b9f4') ]]]

Una vez solucionado esto, actualizamos la página de inicio y... ¡otro error! Es: 

> Advertencia: Propiedad no definida: `App\Entity\Starship::$droids`.

Esto viene de `ship.droidNames` en la plantilla de la página de inicio. Sabemos que cuando llamamos a `ship.droidNames`, éste llama a `$starship->getDroidNames()`y seguimos haciendo referencia a la propiedad `droids`:

[[[ code('c63582b136') ]]]

## Ocultar esa Entidad Unida

A continuación, vamos a ocultar la entidad de unión para que funcione exactamente igual que la relación MuchosMuchos que teníamos antes. ¡Magia!
