# Cómo funciona la autocableado

Eh, ¡mira! ¡Es nuestro comando favorito!

```terminal
bin/console debug:autowiring
```

Nos muestra una lista de los servicios que podemos autocablear en nuestro código. Pero, ¿cómo funciona realmente la autoconexión? Vamos a ejecutar otro comando:

```terminal
bin/console debug:container
```

Esto nos da una enorme lista de servicios, y cualquier ID de servicio que resulte ser un nombre de clase o interfaz es autocableable. Esto significa que podemos indicarlo en el constructor de nuestro servicio y el contenedor de servicios inyectará ese servicio. Por el contrario, si un ID de servicio no es un nombre de interfaz o de clase, no es autoconectable. Esto es así por diseño, ya que la mayoría de los servicios son de bajo nivel y sólo existen para ayudar a otros servicios entre bastidores. Rara vez necesitaremos utilizar directamente esos servicios de bajo nivel, y por eso no podemos obtenerlos mediante autocableado. Y por eso `debug:container`tiene muchas más entradas que `debug:autowiring`.

## Depurar el contenedor

El contenedor de servicios es básicamente una matriz gigante en la que cada servicio tiene un nombre único que apunta al objeto de servicio correspondiente. En el caso de `twig`, por ejemplo, el contenedor sabe que para instanciar este servicio, necesita crear una instancia de esta clase `Twig\Environment`. Y aunque aquí no veamos los argumentos, sabe exactamente cuáles debe pasar para instanciarlo. Como ventaja, si hacemos una petición del mismo servicio en más de un sitio, el contenedor de servicios sólo crea una instancia, por lo que tendremos exactamente la misma instancia en todas partes.

También te habrás fijado en estas clases de servicio. Este `CacheInterface`, por ejemplo, se utilizó antes como alias de nuestro servicio `cache.app`. Esto no es más que una forma de hacer que un servicio como `cache.app` sea autodireccionable. La gran mayoría de estos servicios utilizan la estrategia de nomenclatura snake case, así que para hacerlos autowireables en nuestro código, los bundles añaden algunos alias -nombres de clases, o interfaces- que podemos teclear en nuestro código. Así que los alias son básicamente como enlaces simbólicos que sólo hacen referencia a otros servicios. Sin embargo, puede haber ocasiones en las que haya varios servicios en el contenedor que implementen la misma clase o interfaz.

## Grupo de caché personalizado

Para manejar esto, volvamos a nuestro código y creemos una reserva de caché personalizada. En `config/packages/cache.yaml`, aquí abajo, descomenta la clave `pools`, y en lugar de este ejemplo, di `iss_location_pool: null`. 

[[[ code('f0c79a1800') ]]]

Ahora, en tu terminal, ejecuta:

```terminal
bin/console debug:autowiring
```

Y... ¡compruébalo! Esta configuración ha añadido un nuevo servicio - `iss_location_pool` - que tiene el mismo `CacheInterface`que `cache.app`. 
Volvamos a `src/Controller/MainController.php`, dentro de `homepage()`, cambiemos el nombre de esta variable por `$issLocationPool` y mantengamos el mismo typehint `CacheInterface`. 
Copia ese nombre de variable y, aquí abajo, pégalo. 

[[[ code('e50f44c53d') ]]]

Esto se llama "autocableado con nombre": nuestro contenedor de servicios mira el nombre de la variable y su typehint para inyectar el servicio correcto. Es bastante raro, pero también podemos verlo con nuestro servicio `logger`.

De vuelta a nuestro navegador, actualiza la página y comprueba el perfil de caché. Aquí está nuestro `iss_location_pool` y nuestro `iss_location_data` está escrito en ese pool. Si alguna vez necesitamos borrar la caché de este pool, en nuestro terminal, ejecuta:

```terminal
bin/console cache:pool:clear iss_location_pool
```

Esto borrará la caché de este grupo concreto sin afectar a los demás grupos. ¡Muy práctico!

También podemos configurar este pool de forma diferente a los demás. Por ejemplo, vamos a establecer el tiempo de caducidad de nuestro nuevo pool en el archivo de configuración. En `cache.yaml`, en lugar de `null`, en una nueva línea, escribe `default_lifetime: 5`.

[[[ code('23724b4fe9') ]]]

El `5` está en segundos. Esto debería afectar a todos los elementos de caché de esta reserva. Ahora, en `MainController.php`, podemos eliminar `$item->expiresAfter()`. También podemos deshacernos por completo de este argumento `$item`. 

[[[ code('d5223646dd') ]]]

Para asegurarnos de que esto funciona, en nuestro navegador, actualiza de nuevo la página de inicio y... no hay errores. ¡Funciona!

Siguiente: Hablemos de los entornos: conjuntos de configuraciones que nos ayudan a desarrollar localmente frente a la producción.
