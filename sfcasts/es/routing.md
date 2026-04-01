# Enrutamiento con la herencia de Doctrine

Vamos a crear una página de demostración dedicada para nuestras naves estelares y a comprobar cómo funciona el enrutamiento con las entidades de herencia Doctrine. Primero necesitamos un nuevo controlador, así que espera al terminal y ejecuta:

```terminal
symfony console make:controller Starship
```

No es necesario realizar ninguna prueba. Bien, salta a tu IDE y busca nuestro nuevo y brillante`StarshipController`. Empieza por cambiar el nombre del método a `show()`. A continuación, en el atributo`#[Route]`, cambia la ruta a `/starship/{id}`, y nómbralo `app_starship_show`.

Por último, vamos a utilizar la magia del resolvedor de valores de entidad de Doctrine para inyectar el `Starship` para el id pasado. Añade `Starship $ship` como argumento al método `show()`.

Por ahora, sólo `dd($ship)` para verlo en acción.

## Enlace a la página Mostrar

Ahora, dirígete a nuestra plantilla teaser genérica. Busca el enlace del marcador de posición (el que tiene `#` como href). Sustitúyelo por `{{ path('app_starship_show') }}` y`{id: ship.id}` como parámetros.

Actualiza nuestra página de inicio y haz clic en el primer carguero. Vaya, este volcado nos muestra esta instancia`Freighter`. Vuelve a la página de inicio y haz clic en el último carguero minero. Ahora el volcado nos muestra una instancia de `MiningFreighter`. Aunque hemos indicado`Starship`, Doctrine sabe que debe instanciar la clase de entidad correcta.

Si todo esto funciona exactamente como esperabas, tienes razón, realmente no hay nada especial en el enrutamiento y la herencia Doctrine. 

## Comprender la sugerencia de tipos

Pero toma nota: si vuelves a `StarshipController::show()`, y cambias la sugerencia de tipo de `Starship` a `Scout`, acabarás con un error 404 cuando actualices la página. ¿Por qué? Porque no hay ninguna nave exploradora con este ID. Así que, para asegurarte de que se puede encontrar cualquier nave en esta ruta, tu pista de tipo debe ser `Starship`, la entidad más alta que quieras encontrar para esta ruta. 

## Preparar la plantilla de presentación

Preparemos la plantilla de este controlador. El bundle creador creó este archivo`index.html.twig` en nuestro directorio `templates/starship`. Cámbiale el nombre a `show.html.twig`. Ábrelo y sustituye el bloque del título por `{{ ship.name }}`. Elimina todo el texto repetitivo del bloque body.

Para simplificar, copia el contenido de nuestra plantilla teaser y pégalo en el bloque body de la plantilla show. En una aplicación del mundo real, la plantilla de espectáculo probablemente sería bastante diferente, ofreciendo más detalles y un diseño distinto.

Vuelve a `StarshipController::show()`, elimina el `dd()`, cambia la plantilla a`starship/show.html.twig`, y pasa `ship` como parámetro. Ahora, vuelve a nuestra aplicación y actualiza la página. Por supuesto, esto parece el teaser, pero en realidad se trata de nuestra página de presentación independiente.

## Implementar la herencia de plantillas Twig

Al igual que hicimos con los teasers, podemos utilizar la herencia de plantillas Twig con nuestra plantilla de espectáculo. Sin embargo, esto requiere un enfoque diferente, ya que estamos renderizando la plantilla dentro de un controlador, no con la función `include()` en otra plantilla Twig.

En primer lugar, copia el directorio `teaser` en `show` dentro del directorio `templates/starship`. Éstas serán las plantillas de espectáculo personalizadas y específicas de la entidad. Abre `show/freighter.html.twig`, y cambia el `extends` por `starship/show.html.twig`. En `show/mining_freighter.html.twig`, cambia el `extends` por `starship/show/freighter.html.twig`.

Ya estamos listos para utilizar estas plantillas, pero tenemos que averiguar cuál renderizar.

Al principio, podrías pensar que podrías hacer lo mismo con `render()` y simplemente cambiarla por una matriz. Pero `render()` es estrictamente de tipo cadena para la vista. Así que... tenemos que ensuciarnos un poco las manos.

En `StarshipController::show()`, vamos a darnos un respiro e inyectar el servicio de entorno Twig con `Environment`, el del espacio de nombres `Twig`, `$twig`.

A continuación, crea una variable `$template`. Para ello, utilizaremos la interpolación de cadenas para construir el nombre de la plantilla: `starship/show/{$ship->getType()}.html.twig`. Este es el nombre de la plantilla para el tipo específico de nave.

Si esta plantilla no existe, queremos recurrir a la plantilla genérica del programa.

Así que, `if (!$twig->getLoader()->exists($template))`, y dentro, pon `$template` a `starship/show.html.twig`.

Por último, sustituye la cadena del método `render()` por nuestra nueva variable `$template`.

Esto es muy similar a lo que la función `include()` de Twig hace por nosotros cuando pasas una matriz.

Salta a nuestra aplicación y actualiza... Genial, como se trata de un carguero minero, ahora vemos la capacidad de carga y la potencia láser. Ve a la página de inicio y haz clic en el primer carguero normal. Genial, ahora vemos la capacidad de carga.

Puedes pasar el ratón por encima del icono Twig de la barra de herramientas de depuración web para ver la plantilla utilizada. Efectivamente,`starship/show/freighter.html.twig`.

Vuelve a la página de inicio y haz clic en un explorador. Genial, no hay detalles adicionales, y la barra de herramientas muestra que se está renderizando la plantilla genérica `starship/show.html.twig`.

¡Nuestro sistema casero de plantillas dinámicas funciona!

A continuación veremos cómo funcionan las asociaciones con la Herencia de Doctrine y qué consideraciones debes tener en cuenta al utilizarlas.
