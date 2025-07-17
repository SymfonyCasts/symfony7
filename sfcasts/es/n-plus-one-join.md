# Unir para evitar la trampa del N+1

Tenemos una tabla `parts`, ¡y la estamos utilizando! Pero ahora queremos ordenar los recambios por `price` de forma descendente, porque si vamos a vender más, mejor empezar por los más caros, ¿no? Es una tarea sencilla, pero vamos a hacerla más emocionante elaborando una consulta personalizada. Abre`src/Repository/StarshipPartRepository.php`.

¿Ves el método stubbed? Cópialo y descoméntalo: este documento PHP es útil y no queremos perderlo. Elimina el último stub y llámalo `findAllOrderedByPrice()`. Elimina el argumento `$value`, no es necesario:

[[[ code('834c98b8ae') ]]]

Construye una consulta simple: Utilizaré `sp` como alias de `StarshipPart`. Deshazte de`andWhere()` y `setParameter()` que hay debajo. Sin embargo, necesitamos el `orderBy()`: como `orderBy('sp.price', 'DESC')`. El `setMaxResults()` también puede ir:

[[[ code('7d25dc71ae') ]]]

Consulta personalizada, ¡comprobado! Copia el nombre del método y dirígete a`PartController`. Utilízalo en lugar de `findAll()`:

[[[ code('b6ce491735') ]]]

## Examinar nuestras consultas

Echa un vistazo a las consultas de esta página: hay 9. La primera es exactamente lo que habíamos previsto: busca todas las `starship_part`ordenadas por precio de forma descendente. Pero espera, ¿qué son todas estas otras consultas? Hay una consulta más por nave. ¿Por qué?

## El problema N + 1

Consultamos todas las piezas y, cuando estamos en la plantilla repasando las piezas, al hacer referencia a `part.starship`, a Doctrine se le ilumina la bombilla: se da cuenta de que tiene los datos de `part`, pero no los de `Starship` para este `part`. Así que los consulta. Al final tenemos una consulta para las partes y una consulta adicional para cada `Starship` para obtener sus partes. Se trata de un villano conocido como el problema N + 1.

Piénsalo así: si tenemos 10 partes, acabaremos con una consulta para las partes y luego 10 consultas extra, una para el `Starship` de cada una de esas partes. Esto es un problema de rendimiento. Puede que no parezca gran cosa, pero es algo que debemos vigilar. Y podemos vencerlo con un `join`.

## Unir a través de la relación

Volviendo a `StarshipPartRepository`, vamos a potenciar`findAllOrderedByPrice()` con una unión. Añade `innerJoin('sp.starship', 's')`. Todo lo que tenemos que hacer es unir en la propiedad. Doctrine averiguará los detalles por nosotros, como en qué columnas unir. A continuación, pasaremos toda la tabla `starship`a `s`:

[[[ code('a89feb8cee') ]]]

Antes teníamos 9 consultas a la base de datos. Actualizar y... seguimos teniendo 9 consultas a la base de datos ¿Por qué? ¿No nos habíamos unido ya a la tabla `starship`? Sí, pero hay dos razones para utilizar un `join`. La primera es evitar este problema de N + 1, y la segunda es hacer un `where()` o `orderBy()` en la tabla de unión. Pronto exploraremos esta segunda razón.

Para resolver el problema de N más 1, además del `join`, necesitamos seleccionar los datos en `Starship`. Es tan sencillo como decir `addSelect('s')`:

[[[ code('02b7c92de4') ]]]

Estamos aliasando toda la tabla `Starship` a `s`. Luego, con `addSelect()`, no nos molestamos con columnas individuales. Simplemente decimos:

> Oye, quiero todos esos datos.

## La magia de `join` y `addSelect()`

Ahora sólo tenemos 1 consulta a la base de datos desde 9. Eso sí que es magia. Como puedes ver, estamos seleccionando en `StarshipPart`, cogiendo todos los datos de `Starship` y `StarshipPart`, con `innerJoin()`justo ahí. ¿Y lo mejor? No tenemos que preocuparnos por los detalles de unir en qué columnas. Todo lo que tenemos que hacer es unir en la propiedad de relación, y Doctrine se encarga de los detalles aburridos por nosotros.

A continuación, vamos a añadir una búsqueda a nuestra página. Cuando lo hagamos, veremos el segundo uso de `JOIN` y, por último, jugaremos con el objeto `Request`.