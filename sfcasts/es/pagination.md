# Paginación

Foundry nos ayudó a añadir 20 naves. Eso hace que nuestra aplicación parezca más realista. Pero en producción, podríamos tener miles de naves estelares. Esta página sería gigantesca e inutilizable. Probablemente también tardaría mucho en cargarse, ¡tiempo durante el cual es probable que nos asimilen!

¿La solución? Paginar los resultados: mostrar unos pocos cada vez, o por página.

## Instalar Pagerfanta

Para ello, utilizaremos una biblioteca llamada Pagerfanta -¡qué nombre más chulo! Es una biblioteca de paginación genérica, ¡pero tiene una gran integración con Doctrine! Añade los dos paquetes necesarios:

```terminal
composer require babdev/pagerfanta-bundle pagerfanta/doctrine-orm-adapter
```

Desplázate hacia arriba para ver lo que instala. `pagerfanta/doctrine-orm-adapter` es el pegamento entre Pagerfanta y Doctrine.

## Paginar una consulta

En nuestra página de inicio, estamos utilizando `findIncomplete()` de `StarshipRepository`. Ábrelo y busca el método. Cambia el tipo de retorno a `Pagerfanta`: un objeto con superpoderes relacionados con la paginación. Pero puedes hacer un bucle sobre este objeto como si fuera un array, así que deja el docblock como está:

[[[ code('b7a54f2781') ]]]

Ahora, una cosa superimportante que hay que recordar al paginar una consulta es tener un orden predecible. Añade `->orderBy('e.arrivedAt', 'DESC')`:

[[[ code('f1d04c384b') ]]]

Pero en lugar de devolver, añade esto a una variable llamada `$query`, luego elimina`getResult()`: nuestro trabajo cambia de ejecutar la consulta a simplemente construirla. Pagerfanta se encargará de la ejecución real. Devuelve`new Pagerfanta(new QueryAdapter($query))` y asegúrate de importar estas dos clases:

[[[ code('1016dfa6be') ]]]

## Configurar la página

De vuelta a `MainController`, `$ship` es ahora un objeto `Pagerfanta`. Para utilizarlo, tenemos que decirle 2 cosas: cuántas naves queremos en cada página - `$ships->setMaxPerPage(5)` - y en qué página se encuentra actualmente el usuario: utiliza `$ships->setCurrentPage(1)` por ahora. Ah, y asegúrate de llamar a `setCurrentPage()` después de `setMaxPerPage()` o sucederán cosas raras de viajes en el tiempo:

[[[ code('6b552a1e97') ]]]

Muévete... actualiza... ¡y mira! Sólo mostramos 5 elementos: la primera página.

Vuelve a cambiar a `setCurrentPage(2)`:

[[[ code('3c0c65b52f') ]]]

y actualiza de nuevo.

Todavía 5 barcos, pero barcos diferentes: la segunda página. Echemos un vistazo a la consulta. ¡Hay varias! Una para contar el número total de resultados y otra para obtener sólo los de esta página. Muy chulo.

En lugar de codificar la página en 1 ó 2 -una solución temporal y poco convincente-, leámosla dinámicamente desde la URL, como con`?page=1` o `?page=2`.

## Página actual desde la petición

Para ello, autocodifica `Request $request` -la de `HttpFoundation` - y cambia el argumento `setCurrentPage()` por `$request->query->get('page', 1)`para leer ese valor y poner por defecto 1 si falta:

[[[ code('d2ad35f6a6') ]]]

Vuelve y actualiza. Esta es la página 1 porque no hay parámetro `page`. Añade `?page=2`a la URL y... ¡estamos en la página 2!

Vale, ¿qué más estaría bien? ¿Qué tal mostrar el número total de naves, el número total de páginas y el número de la página actual?

## Mostrar información de paginación

De vuelta en el controlador, Cmd + Click `homepage.html.twig` para abrirlo.

Pon esta información debajo de `<h1>`. Cambiaré el margen inferior y añadiré un nuevo `<div>` (con un poco de estilo). Dentro, escribe `{{ ships.nbResults }}`. Luego: Página `{{ ships.currentPage }}` de `{{ ships.nbPages }}`:

[[[ code('fe51415bdf') ]]]

Vuelve a girar y actualiza. ¡Perfecto! Tenemos 14 naves incompletas en total, y estamos en la página 1 de 3. Tus números pueden variar dependiendo de cuántas de tus 20 naves tengan el estado incompleto al azar.

## Enlaces de paginación

¡Vale! ¿Qué falta? ¿Qué tal unos enlaces para navegar entre páginas? Debajo de la lista, voy a pegar algo de código. Primero,`if ships.haveToPaginate`: no se necesitan enlaces si sólo hay una página. Después,`if ships.hasPreviousPage`, vamos a añadir un enlace a la página anterior si existe, no habría página anterior si estamos en la página 1. Dentro, genera una URL a esta página: `app_homepage`. Pero pasa un parámetro: `page` ajustado a `ships.getPreviousPage`. Como `page` no está definido en la ruta, se añadirá como parámetro de consulta `page`. ¡Eso es exactamente lo que queremos! Repítelo para el enlace `Next`: si `ships.hasNextPage` y `ships.getNextPage`:

[[[ code('a8c61ef08c') ]]]

Actualiza, desplázate hacia abajo y ¡listo! ¡Vemos un enlace `Next`! Haz clic en él... y ahora estamos en la página 2 de 3, y la URL tiene `?page=2`. Abajo, nuestro widget tiene enlaces `Previous` y `Next`. Vuelve a hacer clic en `Next`... página 3 de 3, y luego en `Previous`, de vuelta a la página 2 de 3. ¡Perfección de la paginación!

Construimos estos enlaces a mano, lo que nos da un poder de personalización ilimitado. Pero Pagerfanta sí que puede generar esto por nosotros. Si quieres ver cómo, consulta la documentación de Pagerfanta. El inconveniente es que personalizar el HTML es un poco más difícil.

A continuación, vamos a añadir más campos a nuestra entidad `Starship`. ¿Lo mejor? Ver lo fácil que es añadir esa columna a la base de datos. ¡Vamos a hacerlo!
