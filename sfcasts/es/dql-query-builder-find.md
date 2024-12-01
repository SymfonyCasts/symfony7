# Obtención con DQL, QueryBuilder y find()

Ahora nuestra base de datos está llena de brillantes naves estelares ficticias Pero esta página de inicio sigue mostrando las naves codificadas. ¡Qué pena! Es hora de cargarlas desde la base de datos. ¡Eso mejorará la genialidad de nuestra aplicación x10!

Ve a tu terminal. ¿Recuerdas la consulta SQL para seleccionar todas las naves? Ejecútala de nuevo:

```terminal
symfony console doctrine:query:sql 'select * from starship'
```

Eso es SQL en bruto, pero el ORM de Doctrine tiene su propio lenguaje de consulta llamado DQL: Lenguaje de Consulta Doctrine Es como SQL, pero en lugar de consultar a partir de tablas, con DQL piensas en términos de consulta a los objetos entidad. Ejecuta la misma consulta anterior pero como DQL:

## Escribir DQL manualmente

```terminal
symfony console doctrine:query:dql 'select s from App\Entity\Starship s'
```

Esto parece un poco raro, pero es PHP volcando nuestros objetos `Starship` - y hay tres, igual que en la consulta sin procesar.

Aprovechemos esto en nuestro controlador de página de inicio. Abre`src/Controller/MainController.php` y busca el método `homepage()`. En lugar de inyectar este `StarshipRepository` (es el antiguo del directorio `Model` ), sustitúyelo por `EntityManagerInterface $em` de Doctrine.

[[[ code('3dea9b9394') ]]]

## `EntityManagerInterface`

En el último capítulo, vimos que Doctrine pasa un `ObjectManager` al método `AppFixture::load()`. Este `EntityManagerInterface` es un tipo de `ObjectManager` y es lo que utilizaremos para autocablear el gestor de entidades de Doctrine.

## Utilizando `createQuery()`

A continuación, escribe:`$ships = $em->createQuery()` y pasa la cadena DQL:`SELECT s FROM App\Entity\Starship s`. Por último, llama a `->getResult()`.

[[[ code('9162765bb7') ]]]

Esto ejecuta la consulta, coge los datos pero devuelve una matriz de objetos `Starship` en lugar de los datos sin procesar, ¡lo cual es asombroso!

Deja el resto del método como está.

Gira y actualiza la página de inicio. Está básicamente igual... ¡eso es buena señal! Fíjate bien en la barra de herramientas de depuración web: hay una nueva sección "Doctrine". OooooooOooo. 

## Perfilador Doctrine

Haz clic para abrir el panel del perfilador "Doctrine". Es genial. Muestra todas las consultas que se ejecutaron durante la última petición. Sólo vemos una: ¡tiene sentido!

Podemos ver una consulta formateada que es más legible, una consulta ejecutable que podemos copiar y pegar en nuestra herramienta SQL favorita, un botón "Explicar consulta" para ver información específica de la base de datos sobre cómo se ejecutó la consulta, y un "Ver rastreo de consultas".

¡Éste es mi favorito! Muestra la pila de llamadas que condujo a esta consulta. Resulta muy útil para averiguar qué código desencadenó la consulta, en este caso, nuestro método `homepage()`.

## Utilizar el QueryBuilder

Por suerte, Doctrine tiene un "constructor de consultas". Esta cosa es impresionante: en lugar de escribir la cadena DQL manualmente, la construimos con un objeto. De vuelta a nuestro método `homepage()`, sustituye `$em->createQuery()` por`$em->createQueryBuilder()`. Fuera de él, encadena `->select('s')`, luego`->from(Starship::class, 's')` golpeando la pestaña añade la sentencia `use` de `App\Entity`. ¡Bonus! Podemos utilizar `Starship::class` en lugar de la cadena.

Por último, antes de `->getResult()`, llama a `->getQuery()`.

[[[ code('fe74a27fec') ]]]

De vuelta en la aplicación, actualiza la página de inicio... ¡todavía funciona!

Aún tenemos que refactorizar una cosa. Haz clic en una de las naves... ¡oh no!

> Nave estelar no encontrada.

Ahh, nuestra acción `StarshipController::show()` sigue utilizando el antiguo `StarshipRepository`con los datos codificados. ¡Tenemos que arreglarlo!

Abre `src/Controller/StarshipController.php` y busca el método `show()`. Como necesitamos consultar los datos, sustituye`StarshipRepository $repository` por `EntityManagerInterface $em`.

[[[ code('f2acdd4d13') ]]]

En este caso, la consulta es tan sencilla que hay un método abreviado.

## Utilizando `find()`

Escribe `$ship = $em->find(Starship::class, $id)`.

[[[ code('da8a26a463') ]]]

El primer argumento de `find()` es la clase de entidad que quieres recuperar, y el segundo es el ID. ¡Fácil!

Vuelve a la aplicación y... actualiza. ¡Funciona! Mira la barra de herramientas de depuración web: se ha ejecutado una única consulta para obtener la nave estelar.

Ya hemos terminado con nuestro antiguo directorio `Model/`. Bueno, casi, el `StarshipStatusEnum` sigue siendo necesario, así que muévelo a `Entity/` para mantener las cosas organizadas. PhpStorm se encargará de renombrarlo. Ahora, borra `src\Model` y ¡celebra! ¡Me encanta borrar código sin usar!

¡Siguiente paso! Vamos a echar un vistazo a los repositorios de entidades como forma de mover la lógica de consulta fuera de nuestros controladores.
