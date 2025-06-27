# Ordenar una Relación y tipo "buscar

Haz clic en una nave "en curso". A continuación, abre: `templates/starship/show.html.twig`. Para ordenar las piezas, utiliza `for part in ship.parts`.

Esto funcionará a las mil maravillas. Pero con una pega: el orden de las piezas no está garantizado. ¡Salen de la base de datos en el orden que quieran!

Preferiría tenerlas ordenadas por nombre. ¿Significa esto que tenemos que escribir una consulta personalizada... y que ya no podemos utilizar nuestro práctico `ship.parts`?

¡No temáis, amigos! ¡Aprendamos algunos trucos!

## Reorganizar las partes

Dirígete a la entidad `Starship` y busca la propiedad `parts`. Encima de `parts`, añade un nuevo atributo: `#[ORM\OrderBy(['name' => 'ASC'])]`, no `position`:

[[[ code('61aac76485') ]]]

Actualiza la página, ¡y ya está!

Si te estás rascando la cabeza preguntándote por qué T va antes que c, no has olvidado el abecedario. Es que Postgres es una base de datos que distingue entre mayúsculas y minúsculas. Así que la T mayúscula aparentemente va antes que la C minúscula en orden alfabético.

## Consultas inteligentes

Comprueba las consultas de esta página y visualiza el SQL formateado. Consulta desde `starship_part`, donde `starship_id` es igual a nuestro ID, ordenado por `name` de forma ascendente: ¡es exactamente la consulta que queremos!

## El problema N+1

Vuelve a la página de inicio y abre su plantilla:`templates/main/homepage.html.twig`. Después de "llegado", añade un div e imprime el recuento de piezas: `ship.parts|length`:

[[[ code('6affa2aab7') ]]]

De vuelta a la página principal, funciona a las mil maravillas. Echa un vistazo a las consultas de esta página, son interesantes. Algunas parecen un poco locas debido a nuestra paginación, pero esencialmente, tenemos una consulta para la nave estelar, y si buscamos `starship_part`, hay 5 consultas adicionales para las piezas de cada nave estelar.

Esto es lo que ocurre: cogemos las naves estelares, y en cuanto contamos `ship.parts`, Doctrine se da cuenta de que aún no tiene esos datos. Así que busca una a una todas las piezas de cada nave y las cuenta. Ésta es una situación habitual: tenemos una consulta para las naves y luego una consulta adicional para las piezas de cada nave. Se conoce como el problema N+1: 1 consulta para las naves y N consultas para las piezas de cada nave. Es un problema menor de rendimiento que abordaremos más adelante.

## Consultas eficientes

¡Pero aquí hay un problema mayor! Consultamos cada`starship_part` sólo para contarlas. No necesitamos los datos de las piezas, sólo necesitamos saber cuántas tenemos. Esto es menor... hasta que tienes una nave con una tonelada de piezas. 

Para solucionarlo, en el `OneToMany` en la entidad `Starship`, añade una opción `fetch` establecida en `EXTRA_LAZY`:

[[[ code('c97979f279') ]]]

¡Vamos a ver qué ha hecho!

## Contando las Piezas

Vuelve a la página principal. Antes teníamos nueve consultas... ¿Ahora? Siguen siendo nueve consultas, pero la consulta de las piezas ha cambiado. En lugar de consultar todos sus datos, sólo los cuenta. Mucho más inteligente, ¿verdad?

Te estarás preguntando -yo ciertamente lo hice- por qué no utilizamos `fetch="EXTRA_LAZY"`todo el tiempo En primer lugar, se trata de una pequeña optimización del rendimiento de la que no tienes que preocuparte, a menos que tengas una nave llena de piezas y sólo quieras contarlas. Y lo que es más importante, dependiendo de si cuentas o haces un bucle sobre las piezas primero, esto podría causar una consulta extra.

## El sistema de criterios

¡Pasemos al siguiente reto! ¿Qué pasa si sólo queremos las piezas relacionadas de una nave que cuestan más de un precio determinado? ¿Podemos seguir utilizando el acceso directo `ship.parts`o tenemos que hacer una consulta personalizada? Permanece atento, vamos a explorar el sistema de criterios a continuación.
