# Argumentos no autoinstalables

Anteriormente, aprendimos que podemos obtener parámetros del contenedor con `getParameter()` en nuestro controlador. También vimos lo fácil que es crear nuestros propios servicios. ¿Y adivina qué? ¡No es lo único que podemos personalizar! También podemos crear nuestros propios parámetros. ¿Cómo? Te lo enseñaré

Abre `config/services.yaml`. Aquí, vemos una sección `parameters` vacía. Dentro, vamos a crear un nuevo parámetro -qué tal `iss_location_cache_ttl` - y vamos a ponerlo en `5`.

[[[ code('17551b3c29') ]]]

Utilizaremos este parámetro en la configuración para evitar codificar nada. 
Pero antes, vuelve a `MainController.php`, y en lugar de volcar `kernel.project_dir`, volquemos nuestro nuevo parámetro: `iss_location_cache_ttl`.

[[[ code('530054cc47') ]]]

En nuestro navegador, actualiza y... ¡ahí está: 5!

Ahora, sabemos que podemos cogerlo con `getParameter()` en nuestros controladores. Pero, ¿qué hacemos si no estamos en un controlador? ¿Cómo podemos utilizar parámetros en los servicios sin este elegante método `getParameter()`? Veamos... Si añadimos un nuevo argumento a la página de inicio - `$issLocationCacheTtl` - y volcamos esto en lugar de `getParameter()`, cuando actualicemos... ¡error! 

[[[ code('ec099e3858') ]]]

Symfony no puede autocablear ese argumento. Puede autocablear servicios, pero esto no es un servicio; es un parámetro. Entonces, ¿cómo lo hacemos? La respuesta: ¡Autoconectarlo! 
Podemos autocablear parámetros como si fueran servicios, y funcionará en el constructor o controlador igual que el autocableado normal. ¡Compruébalo!

De vuelta a nuestro código, vamos a añadir el atributo autowire encima del argumento. Escribe `#[Autowire()]` y, dentro, `param: 'iss_location_cache_ttl'`.

[[[ code('6d0c1a0a1e') ]]]

De vuelta a nuestro navegador, si actualizamos la página... ¡5! ¡Funciona! Vale, vamos a quitar eso y a ver cómo podemos utilizar nuestro nuevo parámetro en nuestra configuración.

Abre `config/packages/cache.yaml`. En lugar de este valor codificado, pon `%iss_location_cache_ttl%`. 

[[[ code('b918d5de36') ]]]

Si lo comprobamos en nuestro navegador... ¡todo sigue funcionando! ¡Fantástico!

Antes de continuar, quiero mostrarte otra forma de autocodificar parámetros: la vinculación de parámetros. Abre `services.yaml` y, en `services`, debajo de `_defaults`, añade una nueva sección: `bind`. Dentro, añade nuestro nombre de variable - `$issLocationCacheTtl` - ajustado a `%iss_location_cache_ttl%`.

En cuanto hagamos coincidir el argumento con el nombre que escribimos en `bind`, Symfony lo autoenlazará automáticamente a este parámetro. También podemos añadir una sugerencia de tipo - `int` - en caso de que queramos autoconectar `$issLocationCacheTtl` con una sugerencia de tipo requerida. En `MainController.php`, tenemos que añadir aquí también `int`.

Cuando lo probamos... ¡esto también funciona! Y como estamos autocableando globalmente, evitamos duplicar nuestros atributos PHP autocableados en varios sitios. Como actualmente no utilizamos ese parámetro en ningún sitio excepto en nuestra configuración, podemos deshacernos de él por ahora.

A continuación: Veamos cómo podemos autocablear servicios no autocableables. Es sorprendentemente fácil.
