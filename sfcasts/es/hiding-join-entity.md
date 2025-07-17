# Ocultar la Entidad de Unión

Actualiza la página de inicio y... ¡arruinado! En la plantilla de la página de inicio, hacemos referencia a `ship.droidNames`. Sabemos que esto llama a`$starship->getDroidNames()`. Pero eso sigue intentando utilizar la propiedad`droids` que acabamos de eliminar. Arreglemos eso primero.

## ¿No sigue siendo una relación entre Starship y Droide?

Ahora, podríamos arreglar esto haciendo un bucle sobre `$ship->starshipDroids`y cogiendo el nombre de cada uno. Pero, ¡espera! Ignora este método durante un minuto. Si te alejas, esto sigue siendo una relación entre `Starship` y `Droid`. Así que, ¿no estaría bien que pudiéramos seguir llamando a `$ship->getDroids()` y que devolviera una colección de objetos `Droid`? ¿Es posible? Absolutamente, amigo mío, absolutamente.

## Arreglar el método getDroids()

Utiliza `$this->starshipDroids->map()` para transformar cada elemento de la colección `StarshipDroid`en un objeto `Droid`:

[[[ code('bf9d77485f') ]]]

Ahora tenemos un método `getDroids()` que devuelve de nuevo una colección de objetos `Droid`. ¡Estupendo!

Ahora que tenemos este método, aquí abajo en `getDroidNames()`. En lugar de utilizar la propiedad `droids`, cambia al método `getDroids()`:

[[[ code('3a007fda38') ]]]

Vuelve a la plantilla de la página de inicio y actualízala. ¡Ya está! Obtener los droides de una nave sigue siendo fácil. Y el resto de nuestro código no ha tenido que cambiar.

## Acto 5: Droides a prueba de futuro

Abre la entidad `Droid` y busca `getStarships()`. Aún no hemos utilizado este método, pero vamos a arreglarlo también. Esto debería devolver una colección de objetos `Starship`. Utiliza el mismo truco de `map()` para transformar la colección `StarshipDroid` en una colección de objetos `Starship`:

[[[ code('0a0a4fc660') ]]]

## Ocultar la entidad de unión cuando creamos la relación

Hay una última cosa de la que tenemos que ocuparnos. Cuando creamos la relación, todavía tenemos que hacer un poco de trabajo pesado creando esta entidad de unión. No es tan sencillo como `$ship->addDroid($droid)`. Lo abordaremos en el próximo capítulo.
