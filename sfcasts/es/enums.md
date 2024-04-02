# Enums PHP

Dentro del bucle, hacer que las cosas sean dinámicas no es nada nuevo... ¡lo cual es genial! Por ejemplo, `{{ ship.status }}`. Cuando actualizamos, ¡se imprime! Aunque, ¡ay! Los estados se están quedando sin espacio. ¡Nuestros datos no coinciden con el diseño!

[[[ code('9fdb2495cc') ]]]

¡Giro argumental! Alguien cambió los requisitos del proyecto... ¡justo en medio! ¡Eso "nunca" ocurre! El nuevo plan es éste: cada nave debe tener un estado de`in progress`, `waiting`, o `completed`. En`src/Repository/StarshipRepository.php`, nuestras naves sí tienen un `status` -es este argumento-, pero es una cadena que puede establecerse con cualquier valor.

## Crear un Enum

Así que tenemos que hacer algunas refactorizaciones para adaptarnos al nuevo plan. Pensemos: hay exactamente tres estados válidos. Este es un caso de uso perfecto para una enum PHP.

Si no estás familiarizado con los enums, son encantadores y una forma estupenda de organizar un conjunto de estados -como publicado, no publicado y borrador- o tamaños -pequeño, mediano o grande- o cualquier cosa similar.

En el directorio `Model/` -aunque esto podría vivir en cualquier sitio... estamos creando el enum para nuestra propia organización- crea una nueva clase y llámala `StarshipStatusEnum`. En cuanto escribí la palabra enum, PhpStorm cambió la plantilla de `class` a una`enum`. Así que no estamos creando una clase, como puedes ver, creamos una `enum`

[[[ code('7d1698ea35') ]]]

Añade un `: string` al enum para hacer lo que se llama un "enum respaldado por cadena". No profundizaremos demasiado, pero esto nos permite definir cada estado -como `WAITING` y asignarlo a una cadena, lo que será útil en un minuto. Añade un estado para `IN_PROGRESS`y finalmente uno para `COMPLETED`.

[[[ code('dc199d83ad') ]]]

Y ya está Eso es todo lo que es un enum: un conjunto de "estados" que se centralizan en un solo lugar.

A continuación: abre la clase `Starship`. El último argumento es actualmente un estado `string`. Cámbialo para que sea un `StarshipStatusEnum`. Y en la parte inferior, el método `getStatus` devolverá ahora un `StarshipStatusEnum`.

[[[ code('dc199d83ad') ]]]

Por último, en `StarshipRepository` donde creamos cada `Starship`, mi editor está enfadado. Dice:

> ¡Eh! ¡Este argumento acepta un `StarshipStatusEnum`, pero estás pasando una cadena!

Vamos a calmarlo. Cambia esto a `StarshipStatusEnum::`... ¡y autocompleta las opciones! Hagamos que la primera sea `IN_PROGRESS`. Y eso añadió la declaración `use` para el `enum` al principio de la clase. Para la siguiente, que sea `COMPLETED`... y para la última, `WAITING`.

[[[ code('e229f0e273') ]]]

¡Refactorización realizada! Bueno... tal vez. Cuando actualizamos, ¡arruinado! Dice

> el objeto de clase `StarshipStatusEnum` no se ha podido convertir a cadena

Y viene de la llamada a Twig de `ship.status`.

Tiene sentido: `ship.status` es ahora un enum... que no puede imprimirse directamente como cadena. La solución más fácil, en `homepage.html.twig`, es añadir `.value`.

[[[ code('9c4199a09e') ]]]

Como hemos hecho que nuestro enum esté respaldado por una cadena, tiene una propiedad `value`, que será la cadena que asignamos al estado actual. Pruébalo ahora. ¡Tiene una pinta estupenda! En curso, completado, esperando.

A continuación: vamos a aprender cómo podemos hacer este último cambio un poco más elegante creando métodos más inteligentes en nuestra clase `Starship`. Luego daremos los toques finales a nuestro diseño.
