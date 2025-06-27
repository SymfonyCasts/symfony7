# Obtener los datos de una relación

Navega hasta la página de inicio y haz clic en cualquiera de las naves estelares con estado "En curso".

Ya tenemos una lista de las piezas... más o menos... ¡todas están codificadas!

Ahora, ¿cómo obtenemos las piezas relacionadas con esta nave?

Abre el controlador de esta página: `src/Controller/StarshipController.php`

## Consulta de piezas relacionadas como cualquier otra propiedad

Para consultar las piezas, lo normal sería autoconectar el `StarshipPartRepository`. Comienza de la misma manera aquí con un argumento `StarshipPartRepository $partRepository`:

[[[ code('9e7bd2a0c6') ]]]

A continuación, establece `$parts`, en `$partRepository->findBy()`:

Esto es bastante estándar: si quieres consultar dónde una propiedad es igual a un valor, utiliza `findBy()` y pasa el nombre de la propiedad y el valor. Cuando se trata de relaciones, ¡es lo mismo!

`$parts = $partRepository->findBy(['starship' => $ship])`.

Y no, no vamos a hacer `Starship ID` ni nada por el estilo. ¡Mantén los identificadores fuera de esto! En su lugar, pasa el propio objeto `Starship`. Puedes pasarle `id` si te da pereza, pero en el espíritu de la Doctrine, las relaciones y el pensamiento sobre objetos, lo mejor es pasarle el objeto `Starship` entero.

Vamos a depurar y a ver qué tenemos: `dd($parts)`:

[[[ code('d958a46207') ]]]

Actualiza, y ¡voilá! Una matriz de 10 objetos `StarshipPart`, todos relacionados con este `Starship`. Impresionante, ¿verdad? Si piensas así, agárrate los pantalones.

## Cómo coger las partes relacionadas fácilmente

Sustituye `$parts` por `$ship->getParts()`:

[[[ code('f9a8dc8c59') ]]]

¡Actualiza! En lugar de una matriz de objetos `StarshipPart`, obtenemos un objeto`PersistentCollection` que parece... vacío. ¿Recuerdas el `ArrayCollection` que `make:entity` añadió a nuestro constructor`Starship`? `PersistentCollection` y `ArrayCollection` forman parte de la misma familia de colecciones. Son objetos pero actúan como matrices. Genial... pero ¿por qué esta colección parece vacía? Es porque Doctrine es inteligente: no consulta las partes hasta que las necesitamos. Realiza un bucle sobre `$ship->getParts()` y vuelca `$part`:

[[[ code('c18659391b') ]]]

De repente, esa colección de aspecto vacío está llena de los 10 objetos `StarshipPart`. 
¡Mágico!

## Consultas perezosas de relación

Aquí hay dos consultas en juego. La primera es para el `Starship`, y la segunda es para todos sus `StarshipPart`s. La primera proviene de la consulta de Symfony para el `Starship` basada en el slug. La segunda es más interesante: ocurre en el momento en que `foreach` pasa por encima de `parts`. En ese preciso instante Doctrine dice:

> Acabo de acordarme: En realidad no tengo los datos de `StarshipPart`s para este
> `Starship`. Voy a buscarlos.

¿No es increíble? Me dan ganas de hacer una fiesta para Doctrine. 

## Ordenar y repasar partes

Deshazte por completo de la variable `parts`... y elimina `StarshipPartRepository`: 
era demasiado trabajo. En su lugar, establece una variable `parts` en `$ship->getParts()`:

[[[ code('010ec22e30') ]]]

Ahora que tenemos nuestra nueva y brillante variable `parts`, haz un bucle sobre ella en la plantilla. Abre `templates/starship/show.html.twig` y sustituye la parte codificada por nuestro bucle: `for part in parts`, `part.name`, `part.price`,`part.notes`, `endfor`:

[[[ code('f7d3f1ce87') ]]]

## ¿Sigue siendo demasiado trabajo?

¡Y lo hemos conseguido! Hemos conseguido mostrar las 10 partes relacionadas, sin hacer mucho trabajo gracias a `$ship->getParts()`.

¿Pero sabes una cosa? Incluso esto es demasiado trabajo. Deshazte por completo de la variable `parts`:

[[[ code('240c2f7d37') ]]]

`for part in ship.parts`:

[[[ code('45d580cbe9') ]]]

Y... ¡todavía no se ha roto! Para divertirnos, mostremos también el número de piezas de esta nave. `ship.parts|length`:

[[[ code('1249479e09') ]]]

Seguimos teniendo dos consultas, pero Doctrine, una vez más, es inteligente. Sabe que ya hemos consultado todas las `StarshipPart`s, así que cuando las contemos, en realidad no necesitamos hacer otra consulta de recuento.

A continuación hablaremos de un tema a menudo incomprendido en las relaciones Doctrine: el lado propio y el inverso de cada relación.
