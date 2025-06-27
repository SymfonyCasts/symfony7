# Las dos caras de una relación: Propia vs Inversa

Dato curioso para tu próxima fiesta de tacos Doctrine: Toda relación puede verse desde dos lados distintos. Por ejemplo,`Starship`: tiene varias partes, lo que la convierte en una relación de uno a muchos desde la perspectiva de `Starship`. Pero, dale la vuelta al telescopio y mira desde el extremo `StarshipPart`, y encontrarás una relación de muchos a uno. Una de estas perspectivas se conoce siempre como el lado propietario, y la otra, el lado inverso. 

Ahora bien, puede que estés pensando

> ¿Por qué me importa cómo se nombran los lados? ¡Tengo que ir a dar de comer a mi gato!

Dile a Mittens que se calme durante tres minutos: esto podría ahorrarte un gran dolor de cabeza más adelante... y una comida completamente perdida.

## El lado propio al descubierto

En primer lugar, ¿qué lado es el propio? Para un muchos-a-uno: siempre es el lado que tiene el atributo `ManyToOne`, que está en la entidad que tendrá la columna de clave foránea. En nuestro caso, es`StarshipPart`.

## La importancia de la propiedad

Pero, ¿por qué es importante? Por dos razones. En primer lugar, el`JoinColumn` sólo puede vivir en la parte propietaria. Y eso tiene sentido: controla la columna de clave externa. En segundo lugar, sólo puede establecerse en el lado propietario de la relación. 
Deja que te lo muestre:

Abre `src/DataFixtures/AppFixtures.php` y juguemos un poco:`$starship = StarshipFactory::createOne();`. Mi señor de la IA casi tenía razón. Debajo, espolvorearé código que crea dos objetos `StarshipPart`, los persiste y los vacía:

[[[ code('c0cf9aaac9') ]]]

Aún no he establecido ninguna relación, pero de todos modos carguemos imprudentemente los accesorios:

```terminal
symfony console doctrine:fixtures:load
```

Aparece nuestro error favorito

> `starship_id` no puede ser nulo

Totalmente esperado.

## El lado propietario vs inverso en acción

Para demostrar el problema de la propiedad frente a la inversión, añade `_real()` al final de `$starship`:

[[[ code('99c7b02f1d') ]]]

Cuando creas una entidad a través de foundry, en realidad la envuelve en un regalito llamado objeto proxy. Esto no suele importar, pero ocasionalmente puede causar cierta confusión. Llamando a `_real()`, desenvolvemos el proxy y obtenemos el objeto real `Starship`.

Es hora de conectar estas piezas a la nave. Normalmente, diríamos`$part1->setStarship($starship);`, que establece el lado propio. Esta vez intenta establecer el lado inverso. Serían`$starship->addPart($part1);` y `$starship->addPart($part2);`:

[[[ code('ad601bd5fe') ]]]

Basándome en lo que acabo de explicar, esto no debería funcionar porque sólo estamos fijando el lado inverso. Pero, de todos modos, tiremos los dados y carguemos los accesorios:

```terminal-silent
symfony console doctrine:fixtures:load
```

Pero, ¡sorpresa, sorpresa! No hay errores. De hecho, si compruebas la base de datos

```terminal
symfony console doctrine:query:sql "SELECT * FROM starship_part"
```

Efectivamente, tenemos dos piezas nuevas, cada una relacionada con una nave estelar.

Entonces, ¿qué pasa? Acabamos de establecer el lado inverso de la relación, y aún así se ha guardado en la base de datos. ¡Eso es lo contrario de lo que acabo de decirte!

## El giro argumental: el lado inverso establece el lado propio

Abre la entidad `Starship` y busca el método `addPart()`:

[[[ code('6ced05fe6c') ]]]

Este método llama a `$part->setStarship($this);`. Establece el lado propio. Cuando fijamos el lado inverso, nuestro propio código generado por el comando`make:entity` también fija el lado propio. Chica lista, ¿eh?

 ## Propietario vs Inverso vs Me da igual

Así que éstas son las conclusiones: toda relación tiene un lado propio y un lado inverso. El lado inverso es opcional. `make:entity` nos preguntó si queríamos generar el lado inverso, y dijimos que sí. Eso nos proporcionó el método superconveniente`$ship->getParts()`. 

Así que sí, técnicamente, sólo puedes establecer la relación desde el lado propietario (es decir,`$starshipPart->setShip()`), pero en la práctica, puedes establecerla desde cualquier lado gracias a nuestro propio código que sincroniza ambos lados. Así que asombra a tus amigos con tus nuevos conocimientos y luego olvídate de ello: no es importante en la práctica.

Limpia aquí nuestro código temporal y refresca las cosas recargando los accesorios:

```terminal-silent
symfony console doctrine:fixtures:load
```

Muy bien, a continuación: `orphanRemoval`. No es tan malo como parece.
