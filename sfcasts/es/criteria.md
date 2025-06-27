# El ingenioso sistema de criterios

Tenemos este método superútil `$ship->getParts()`: nos devuelve todas las piezas para nuestra nave estelar. Pero el año fiscal está llegando a su fin, y tenemos que planificar nuestro presupuesto. Aburrido, pero necesario: ¡nuestros jefes ferengis lo exigen! La mayoría de las piezas son baratas, como las tuercas, los tornillos y la cinta aislante que lo mantienen todo unido. Ésas no nos preocupan realmente. En cambio, quiero devolver rápidamente todas las piezas de nuestra nave que cuesten más de 50.000 créditos.

Claro, podríamos hacer una nueva consulta en nuestro controlador para todas las piezas de la nave estelar relacionadas con la nave cuyo precio sea superior a 50.000. Pero, ¿dónde está la gracia en eso? Quiero seguir con nuestro atajo fácil de `$ship->getParts()`. ¿Es posible?

## Añadir getExpensiveParts()

Entra en la clase `Starship` y busca el método `getParts()`. Cópialo, pégalo a continuación y cámbiale el nombre a `getExpensiveParts()`. Por ahora, devuélvelo todo:

[[[ code('ebc7bb43bc') ]]]

De vuelta a nuestra plantilla de programa, dale una vuelta a esto. Cambia `parts`por `expensiveParts`:

[[[ code('3e88506435') ]]]

No hay propiedad `expensiveParts`, pero esto llamará al método `getExpensiveParts()`que acabamos de crear. 

## Filtrar lo barato:

Es hora de hacer que nuestro método devuelva sólo las partes caras. Recuerda:`$this->parts` no es una matriz, sino un objeto Colección especial con algunos trucos en la manga. Uno de ellos es el método `filter()`. Éste ejecuta una llamada de retorno por cada parte. Si devolvemos verdadero, incluye esa parte en la colección final. Si devolvemos false, la filtra. Así que podemos decir`return $part->getPrice() > 50000;`:

[[[ code('17706a360a') ]]]

¡Listo! Excepto que... esto es súper ineficiente. Seguimos buscando todas las piezas relacionadas con nuestra nave estelar y filtrándolas en PHP. Imagina que tuviéramos 50.000 piezas, pero sólo 10 de ellas costaran más de 50.000. ¡Menudo despilfarro! ¿Podemos pedirle a Doctrine que cambie la consulta para que sólo coja las piezas relacionadas con la nave estelar cuyo precio sea superior a 50.000?

## El poder del objeto Criterio

Entra en el objeto `Criteria`. Esta cosa es poderosa. Aunque, lo admito, también un poco críptico. Elimina nuestra lógica y utiliza en su lugar `$criteria` igual a`Criteria::create()->andWhere(Criteria::expr()->gt('price', 50000))`. Para utilizar esto, `return $this->parts->matching($criteria);`:

[[[ code('1ceafd8105') ]]]

Ahora bien, si me conoces, sabes que me gusta mantener mi lógica de consulta organizada en mis clases de repositorio. Pero ahora tenemos algo de lógica de consulta dentro de nuestra entidad. ¿Es eso malo? No necesariamente, pero me gusta mantener las cosas ordenadas. Así que traslademos esta lógica de `Criteria` a nuestro repositorio. 

## Trasladar los criterios al repositorio

Vamos a `StarshipPartRepository`. En cualquier lugar de aquí, añade una función estática pública: `createExpensiveCriteria()`:

[[[ code('d5291507b0') ]]]

¿Por qué estática? Por dos razones: una, porque podemos (no estamos usando la variable `this`en ningún sitio dentro), y dos, porque vamos a usar este método desde la entidad `Starship` y no podemos autocablear servicios en entidades, así que debe ser estático. 

De vuelta en `Starship`, utiliza esto. Elimina todo el contenido de `Criteria` y sustitúyelo por `StarshipPartRepository::createExpensiveCriteria()`:

[[[ code('9758fb25e5') ]]]

## Combinar criterios con constructores de consultas

Todo sigue funcionando a las mil maravillas, así que demos un paso más y flexionemos nuestros músculos de desarrolladores. Vamos a crear un método que combine `Criteria` con`QueryBuilder`s. 

Digamos que queremos obtener una lista de todas las piezas caras de cualquier `Starship`. Empieza copiando el método `getExpensiveParts()` de `Starship`. Pégalo en `StarshipPartRepository`. A continuación, devuelve `$this->createQueryBuilder('sp')`. Añade un argumento `$limit`, por defecto 10. Para combinar esto con un `Criteria`, di `addCriteria(self::createExpensiveCriteria())`. Ahora que estamos en un `QueryBuilder`, podemos hacer las cosas normales, como `setMaxResults($limit)`. ¿Quieres hacer un `orderBy`o un `andWhere`? Adelante. Y por supuesto, puedes terminar esto con`getQuery()->getResult()`:

[[[ code('aca546b7bf') ]]]

Combinar `Criteria` con Query Builders es una jugada poderosa. 

Muy bien, ya está bien. A continuación, crearemos una página completamente nueva para listar todas las piezas. ¡Vamos camino de necesitar algunos JOINs!
