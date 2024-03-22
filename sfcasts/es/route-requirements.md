# Rutas más sofisticadas: Requisitos, comodines y más

Con toda la nueva organización del código, celebrémoslo creando otra ruta API para obtener un único `starship`. Empieza como siempre: crea un `public function` llamado, qué tal, `get()`. Incluiré el tipo de retorno opcional `Response`. Encima de éste añade el `#[Route]` con una URL de `/api/starships/`... hmm. Esta vez, la última parte de la URL tiene que ser dinámica: debe coincidir con`/api/starships/5` o `/api/starships/25`. ¿Cómo podemos hacerlo? ¿Cómo podemos hacer que una ruta coincida con un comodín?

La respuesta es añadiendo `{`, un nombre, el `}`.

El nombre dentro de esto puede ser cualquier cosa. No importa lo que sea, ahora esta ruta coincidirá con`/api/starships/*`. Pero sea cual sea el nombre que le pongas, ahora puedes tener un argumento con un nombre que coincida: `$id`.

A continuación, vuelca esto para asegurarte de que funciona.

[[[ code('b16bd7698e') ]]]

## Restringir el comodín a un número

¡Vale! Acércate a `/api/starships/2` y... ¡funciona!

En nuestra app, el ID será un número entero. Si pruebo con algo que no sea un número entero -como `/wharf` - la ruta sigue coincidiendo y llama a nuestro controlador. Y eso casi siempre está bien. En una aplicación real, si consultáramos la base de datos con`WHERE ID = 'wharf'`, no se produciría un error: ¡simplemente no encontraría un barco coincidente! Y entonces podríamos lanzar una página 404, que pronto te enseñaré cómo hacer.

Pero a veces podemos querer restringir estos valores. Puede que queramos decir

> Sólo coincide con esta ruta si el comodín es un número entero.

Para ello, dentro de la llave, después del nombre, añade un `<`, `>` y dentro, una expresión regular `\d+`.

[[[ code('69b82678c5') ]]]

Esto significa: coincide con un dígito de cualquier longitud. Con esta configuración, si actualizamos la URL `wharf`, obtenemos un error 404. Sencillamente, nuestra ruta no coincidió -ninguna ruta coincidió-, por lo que nunca se llamó a nuestro controlador. Pero si volvemos a `/2`, sigue funcionando.

Y como ventaja añadida, ahora que esto sólo coincide con dígitos, podemos añadir un tipo `int` al argumento. Ahora, en lugar de la cadena `2`, obtenemos el `integer` 2. Estos detalles no son superimportantes, pero quiero que sepas qué opciones tienes.

## Restringir el método HTTP de la ruta

Algo habitual en las API es hacer que las rutas sólo coincidan con un determinado método HTTP, como `GET` o `POST`. Por ejemplo, si quieres obtener todas las naves estelares, los usuarios deben hacer una petición a `GET`... lo mismo si quieres obtener una sola nave. Si siguiéramos construyendo nuestra API y creáramos una ruta que pudiera utilizarse para crear un nuevo `Starship`, la forma estándar de hacerlo sería utilizar la misma URL: `/api/starships` pero con una petición a `POST`.

Ahora mismo, esto no funcionaría. Cada vez que el usuario solicitara `/api/starships` -no importa si utiliza una petición `GET` o `POST`, coincidiría con esta primera ruta.

Por eso, es habitual en una API añadir una opción `methods` establecida en una matriz, con `GET` o `POST`. Haré lo mismo aquí abajo: `methods: ['GET']`.

[[[ code('c62385b5de') ]]]

No puedo probarlo fácilmente en un navegador, pero si hiciéramos una petición POST a`/api/starships/2`, no coincidiría con nuestra ruta.

Pero podemos ver el cambio en nuestro terminal. Ejecuta:

```terminal
php bin/console debug:router
```

¡Perfecto! La mayoría de las rutas coinciden con cualquier método... pero nuestras dos rutas API sólo coinciden si se realiza una petición `GET` a esa URL.

## Poner un prefijo a cada URL de ruta

Vale, tengo otro truco de enrutamiento que enseñarte... ¡y es divertido! Todas las rutas de este controlador empiezan con la misma URL: `/api/starships`. Tener la URL completa en cada ruta está bien. Pero si queremos, podemos prefijar automáticamente la URL de cada ruta. Encima de la clase, añade un atributo `#[Route]` con `/api/starships`.

A diferencia de cuando lo ponemos encima de un método, esto no crea una ruta. Sólo dice: cada ruta de esta clase debe ir prefijada con esta URL. Así que para la primera ruta, elimina la ruta por completo. Y para la segunda, sólo necesitamos la parte del comodín.

[[[ code('23887d008f') ]]]

Prueba de nuevo con `debug:router`... y observa estas URL:

```terminal-silent
php bin/console debug:router
```

¡No cambian!

## Finalizando la nueva ruta API

Muy bien. Vamos a terminar nuestra ruta. Tenemos que encontrar el barco que coincida con este ID. Normalmente consultaríamos la base de datos: `select * from ship where id =` este ID. Nuestras naves están codificadas ahora mismo, pero aún podemos hacer algo que se parecerá más o menos a lo que será, una vez que tengamos una base de datos.

Ya tenemos un servicio - `StarshipRepository` - cuyo trabajo consiste en obtener datos sobre naves estelares. Démosle un nuevo superpoder: la capacidad de obtener un único`Starship` para un id. Añade `public function find()` con un argumento `int $id` que devolverá un `Starship` anulable. Por tanto, un `Starship` si encontramos uno para este id, si no `null`.

Ahora mismo, la forma más fácil de escribir esta lógica es hacer un bucle sobre `$this->findAll()`como `$starship`... luego si `$starship->getId() === $id`, devolver `$starship`. Cambiaré mi `uf` por `if`. Mucho mejor.

Y si no encontramos nada, al final, `return null`.

[[[ code('28e91b0460') ]]]

Gracias a esto, nuestro controlador es muy sencillo. Primero, autocablea el repositorio añadiendo un argumento: `StarshipRepository` y llámalo `$repository`. Por cierto, el orden de los argumentos en un controlador no importa.

Después `$starship = $repository->find($id)`. Termina al final con`return $this->json($starship)`.

[[[ code('89a45b2246') ]]]

Creo que ya estamos listos Actualiza. ¡Perfecto!

## Activar una página 404

Pero prueba con un id que no exista en nuestra base de datos falsa - como `/200`. La palabra `null` no es... lo que queremos. En esta situación, deberíamos devolver una respuesta con un código de estado 404.

Para ello, vamos a seguir un patrón común: consulta un objeto y comprueba si devuelve algo. Si no devuelve nada, lanza un 404. Hazlo con throw `$this->createNotFoundException()`. Le pasaré un mensaje.

[[[ code('d378dcbf47') ]]]

Fíjate en la palabra clave `throw`: estamos lanzando una excepción especial que desencadena un 404. Eso está bien porque, en cuanto llegue a esta línea, no se ejecutará nada de lo que venga después.

¡Pruébalo! ¡Sí! ¡Una respuesta 404! El mensaje - "Nave no encontrada"- sólo se muestra a los desarrolladores en modo dev. En producción, se devolvería una página -o JSON- totalmente diferente. Puedes consultar la documentación para obtener más información sobre las páginas de error de producción.

A continuación: vamos a construir la versión HTML de esta página, una página que muestra detalles sobre una única nave estelar. Luego aprenderemos a enlazar entre páginas utilizando el nombre de la ruta.
