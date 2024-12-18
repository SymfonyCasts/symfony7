# Paginación

Foundry nos ayudó a añadir 20 naves. Eso hace que nuestra aplicación parezca más realista. Pero en producción, podríamos tener miles de naves estelares. Esta página sería gigantesca e inutilizable. Probablemente también tardaría mucho en cargarse, ¡tiempo durante el cual es probable que nos asimilen!

¿La solución? Paginar los resultados: mostrar unos pocos cada vez, o por página.

Para ello, utilizaremos una biblioteca llamada Pagerfanta -¡qué nombre más chulo! Es una biblioteca de paginación genérica, ¡pero tiene una gran integración con Doctrine! Añade los dos paquetes necesarios:

```terminal
composer require babdev/pagerfanta-bundle pagerfanta/doctrine-orm-adapter
```

Desplázate hacia arriba para ver lo que se instala. `pagerfanta/doctrine-orm-adapter` es el pegamento entre Pagerfanta y Doctrine.

En nuestra página de inicio, estamos utilizando `findIncomplete()` de `StarshipRepository`. Ábrelo y busca el método. Cambia el tipo de retorno a `Pagerfanta`: un objeto con superpoderes relacionados con la paginación. Pero puedes hacer un bucle sobre este objeto como si fuera un array, así que deja el docblock como está.

Ahora, una cosa superimportante que hay que recordar al paginar una consulta es tener un orden predecible. Añade `->orderBy('e.arrivedAt', 'DESC')`.

Pero en lugar de devolver, añade esto a una variable llamada `$query`, luego elimina`getResult()`: nuestro trabajo cambia de ejecutar la consulta a simplemente construirla. Pagerfanta se encargará de la ejecución real. Devuelve`new Pagerfanta(new QueryAdapter($query))` y asegúrate de importar estas dos clases.

De vuelta a `MainController`, `$ship` es ahora un objeto `Pagerfanta`. Para utilizarlo, tenemos que decirle 2 cosas: cuántas naves queremos en cada página - `$ships->setMaxPerPage(5)` - y en qué página se encuentra actualmente el usuario: utiliza `$ships->setCurrentPage(1)` por ahora. Ah, y asegúrate de llamar a `setCurrentPage()` después de `setMaxPerPage()` o se producirán extraños viajes en el tiempo.

Muévete... actualiza... ¡y mira! Sólo mostramos 5 elementos: la primera página.

Vuelve a cambiar a `setCurrentPage(2)` y actualiza de nuevo. Siguen siendo 5 naves, pero naves diferentes: la segunda página. Echemos un vistazo a la consulta. ¡Hay varias! Una para contar el número total de resultados y otra para obtener sólo los de esta página. Muy chulo.

En lugar de codificar la página en 1 ó 2 -una solución temporal y poco convincente-, leámosla dinámicamente desde la URL, como con`?page=1` o `?page=2`.

Para ello, autocodifica `Request $request` -el de `HttpFoundation` - y cambia el argumento `setCurrentPage()` por `$request->query->get('page', 1)`para leer ese valor y poner por defecto 1 si falta.

Vuelve y actualiza. Esta es la página 1 porque no hay parámetro `page`. Añade `?page=2`a la URL y... ¡estamos en la página 2!

Vale, ¿qué más estaría bien? ¿Qué tal mostrar el número total de naves, el número total de páginas y el número de la página actual?

De vuelta al controlador, Cmd + Click `homepage.html.twig` para abrirlo.

Pon esta información debajo de `<h1>`. Cambiaré el margen inferior y añadiré un nuevo `<div>` (con un poco de estilo). Dentro, escribe `{{ ships.nbResults }}`. Luego: Página `{{ ships.currentPage }}` de `{{ ships.nbPages }}`.

Vuelve a girar y actualiza. ¡Perfecto! Tenemos 14 naves incompletas en total, y estamos en la página 1 de 3. Tus números pueden variar dependiendo de cuántas de tus 20 naves tengan el estado incompleto al azar.

¡Ok! ¿Qué falta? ¿Qué tal unos enlaces para navegar entre páginas? Debajo de la lista, voy a pegar un código. Primero,`if ships.haveToPaginate`: no se necesitan enlaces si sólo hay una página. Después,`if ships.hasPreviousPage`, vamos a añadir un enlace a la página anterior si existe, no habría página anterior si estamos en la página 1. Dentro, genera una URL a esta página: `app_homepage`. Pero pasa un parámetro: `page` ajustado a `ships.getPreviousPage`. Como `page` no está definido en la ruta, se añadirá como parámetro de consulta `page`. ¡Eso es exactamente lo que queremos! Repítelo para el enlace `Next`: si `ships.hasNextPage` y `ships.getNextPage`.

Actualiza, desplázate hacia abajo y ¡listo! ¡Vemos un enlace `Next`! Haz clic en él... y ahora estamos en la página 2 de 3, y la URL tiene `?page=2`. Abajo, nuestro widget tiene los enlaces `Previous` y `Next`. Vuelve a hacer clic en `Next`... página 3 de 3, y luego en `Previous`, de vuelta a la página 2 de 3. ¡Perfección de la paginación!

Construimos estos enlaces a mano, lo que nos da un poder de personalización ilimitado. Pero Pagerfanta sí que puede generar esto por nosotros. Si quieres ver cómo, consulta la documentación de Pagerfanta. El inconveniente es que personalizar el HTML es un poco más difícil.

A continuación, vamos a añadir más campos a nuestra entidad `Starship`. ¿Lo mejor? Ver lo fácil que es añadir esa columna a la base de datos. ¡Vamos a hacerlo!
