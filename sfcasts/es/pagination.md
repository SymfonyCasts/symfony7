# Paginación

En el último capítulo, añadimos más naves estelares a esta lista. Sigue siendo manejable, pero si tuviéramos miles de ellas, tendríamos problemas de rendimiento. Necesitamos paginar estos resultados para mostrar sólo unos pocos cada vez, o por página.

Para ello, utilizaremos una biblioteca llamada Pagerfanta, ¡qué nombre más chulo! Es una biblioteca de paginación genérica, pero tiene una gran integración con Doctrine Instala los dos paquetes necesarios:

```terminal
composer require babdev/pagerfanta-bundle pagerfanta/doctrine-orm-adapter
```

Desplázate hacia arriba para ver lo que se ha instalado. `pagerfanta/doctrine-orm-adapter` es el pegamento entre Pagerfanta y Doctrine.

En nuestra página de inicio, estamos utilizando `findIncomplete()` desde nuestro `StarshipRepository` así que ábrelo y busca ese método. Vamos a cambiar el tipo de retorno a `Pagerfanta`que tiene alguna funcionalidad extra relacionada con la paginación. Este objeto también itera sobre nuestras entidades, así que podemos dejar el docblock anterior como está.

Ahora bien, una cosa superimportante que hay que recordar siempre al paginar una consulta es tener un orden predecible. En nuestra consulta, añade `->orderBy('e.arrivedAt', 'DESC')` para ordenar por el campo `arrivedAt` en orden descendente.

En lugar de devolver, añade esto a una variable llamada `$query`. Necesitamos que Pagerfanta pueda manipular la consulta, así que elimina la llamada a `getResult()`.

Ahora, `return new Pagerfanta(new QueryAdapter($query))` y asegúrate de importar estas dos clases.

En el método `homepage()` de `MainController`, necesitamos configurar la página actual y el número de elementos por página. Después de la variable `$ships`, añade `$ships->setMaxPerPage(5)`, para mostrar 5 elementos por página. Después, `$ships->setCurrentPage(1)` para mostrar la página 1. Es importante tener en cuenta que `setCurrentPage()` debe llamarse después de `setMaxPerPage()` o tendrás resultados extraños.

De vuelta a nuestra aplicación... actualiza... y echa un vistazo, ahora sólo mostramos 5 elementos - la primera página.

De vuelta al controlador `homepage()`, cambia a `setCurrentPage(2)` y actualiza la aplicación. Ahora vemos un conjunto diferente de naves estelares: la segunda página. Echa un vistazo rápido al perfil de consultas. Vemos varias consultas nuevas. Éstas han sido añadidas por Pagerfanta y Doctrine para crear una consulta de paginación optimizada.

En lugar de codificar el número de página actual, lo obtendremos de un parámetro de consulta `page` en la URL. Así, `?page=1` muestra la primera página, `?page=2` muestra la segunda, y así sucesivamente.

En nuestro controlador `homepage()`, inyecta `Request $request` - el de `HttpFoundation`y cambia el argumento `setCurrentPage()` por `$request->query->getInt('page', 1)`. Éste será por defecto 1 si no se establece este parámetro de consulta.

Vuelve a nuestra aplicación y actualiza - esta es la página 1 porque no se ha establecido ningún parámetro de consulta. Añade `?page=2`a la URL... ahora estamos en la página 2.

Estaría bien mostrar al usuario alguna información sobre la página actual: el número total de elementos, el total de páginas y en qué página estamos actualmente. ¡Pagerfanta te lo pone fácil!

En el controlador `homepage()`, haz Cmd + clic en `homepage.html.twig` para abrirlo.

Pondremos esta información justo debajo de este `<h1>`, así que cambiaré su margen inferior y añadiré un nuevo `<div>` (con un poco de estilo). Dentro, escribe `{{ ships.nbResults }}` para mostrar el número total de resultados, y entre paréntesis: `Page {{ ships.currentPage }}` para mostrar el número de página actual y luego `of {{ ships.nbPages }}` para mostrar el número total de páginas.

Vuelve a nuestra aplicación y actualiza. ¡Perfecto! Tenemos 14 resultados en total, y estamos en la página 1 de 3. Debido a la aleatoriedad de los datos de fijación, aquí verás números diferentes.

Necesitamos que los usuarios puedan navegar entre páginas, así que crearemos un pequeño widget "anterior/siguiente" debajo de los resultados.

En `homepage.html.twig`, debajo de donde listamos las naves estelares, voy a pegar algo de código. Primero,`if ships.haveToPaginate`: sólo queremos mostrar este widget si se requiere paginación.`if ships.hasPreviousPage`: sólo mostramos el enlace anterior si hay una página anterior a la que ir. Dentro, estamos generando una url a nuestra ruta `app_homepage`. Ahora, estamos inyectando `ships.getPreviousPage`como parámetro de la ruta `page`. Como `page` no está definido en la ruta, se añadirá como parámetro de consulta `page`, ¡que es exactamente lo que queremos! Dentro del enlace, el texto: `Previous`. Se hace exactamente lo mismo con el enlace `Next` pero utilizando `ships.hasNextPage` y `ships.getNextPage`.

Actualiza nuestra aplicación, desplázate hacia abajo y ¡listo! ¡Vemos un enlace `Next`! Haz clic en él... y ahora estamos en la página 2 de 3. La URL también muestra `?page=2`. Abajo, nuestro widget tiene enlaces `Previous` y `Next`. Haz clic de nuevo en `Next`... página 3 de 3. Haz clic en `Previous`, de vuelta a la página 2 de 3 - ¡perfecto!

¡Navegación hecha!

Hemos creado este sencillo widget manualmente, pero Pagerfanta tiene integración Twig y plantillas para widgets de paginación más complejos, como listar todos los números de página como enlaces, para que puedas saltar a cualquier página rápidamente.

A continuación, añadiremos más campos a nuestra entidad Starship y veremos un truco de migración para garantizar que los datos existentes se mantienen válidos
