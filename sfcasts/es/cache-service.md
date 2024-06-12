# Servicio de Caché y Pools de Caché

Vale, hemos inyectado `HttpClientInterface` y hemos hecho una petición HTTP para obtener unos datos JSON que hemos renderizado en nuestro sitio web. Pero ejecutar una petición HTTP en cada carga de la página no es una buena idea. Las peticiones HTTP son lentas, y ya podemos ver que eso ocurre aquí, donde la carga de nuestra página de inicio tarda más de lo que tardaba antes. Y la ISS se mueve rápido, así que no es muy eficiente actualizar esta información constantemente. ¿Existe algún servicio que pueda almacenar esos datos en caché? ¡Pues claro!

Abre tu terminal y ejecuta

```terminal
bin/console debug:autowiring cache
```

para ver si tenemos algún servicio relacionado con la caché y... ¡lo tenemos! Estos alias de `cache.app`están listos para ser utilizados en nuestra aplicación. Otra cosa a tener en cuenta es esto `CacheItemPoolInterface`. Los pools no son más que espacios de nombres únicos para los elementos almacenados en caché. Puedes pensar en ellos como en "subcarpetas" del directorio de caché global, lo que significa que puedes borrar un grupo de caché sin afectar a los demás. Hablaremos de ello más adelante.

Por ahora, vamos a simplificar las cosas y a utilizar `CacheInterface`. De vuelta a nuestro código, dentro de `homepage()`, escribe `CacheInterface` (el de Contratos) y llámalo `$cache`. 

[[[ code('988434a43c') ]]]

Ahora, aquí abajo, copia estas dos líneas, bórralas y escribe `$issData = $cache->get()`. 
El primer argumento debe ser la clave de caché, que llamaremos... ¿qué tal `iss_location_data`. 
El segundo argumento debe ser un anónimo `function ()`. Ahora podemos pegar a continuación las dos líneas que hemos copiado antes. Pero en lugar de establecer la variable, vamos a poner `return`. Pero antes de poder utilizar esta variable `$client` en una función anónima, tenemos que utilizarla. Escribe `use($client): array`. 

[[[ code('6bae6b469d') ]]]

Si nos dirigimos a nuestro navegador y lo actualizamos... seguimos haciendo la petición del Cliente HTTP y, por aquí, ahora tenemos un icono de caché que nos muestra si se ha escrito algo en la caché. 
¡Ya lo tenemos! Haré clic en este icono de caché para abrir el perfilador, y... ¿a que mola? No hemos creado un pool personalizado para esto, así que se está utilizando el pool por defecto, pero podemos crear pools personalizados y lo haremos en un momento.

Si volvemos a la página de inicio y la actualizamos... la petición HTTP ha desaparecido. Y si pasamos el ratón por encima del icono de la caché... no se ha escrito nada. Y ahora, la carga de nuestra página es notablemente más rápida. Ahora mismo, esos datos se almacenan en caché para siempre a menos que borremos la caché, pero por motivos de desarrollo, vamos a cambiar eso en nuestra función. Añade `ItemInterface` como primer argumento y llámalo `$item`. Dentro, escribe `$item->expiresAfter()` y pasa `time: 5`. 

[[[ code('4843905263') ]]]

Este número está en segundos, transcurridos los cuales expirará la caché. De vuelta a nuestro navegador, si actualizamos, nada cambia porque el valor ya estaba en la caché. Para ver nuestros cambios, tenemos que borrarlo manualmente para que se vuelva a almacenar en caché con nuestro nuevo plazo de cinco segundos.

El adaptador de caché por defecto es un sistema de archivos, lo que significa que la caché se almacena en el directorio `var/cache/dev/pools/`. Aquí podemos ver nuestra carpeta `/app`, que corresponde a nuestra caché `app`. Podríamos eliminar este directorio manualmente, pero hay una forma mejor. En tu terminal, ejecuta:

```terminal
bin/console cache:pool:list
```

Esta es la lista de pools disponibles en nuestra aplicación. Para borrar el pool `cache.app`, podemos utilizar otro comando:

```terminal
bin/console cache:pool:clear cache.app
```

Y... ¡cache borrada! Si volvemos a nuestro navegador y refrescamos ahora... aquí está nuestra petición HTTP. Si volvemos a actualizar rápidamente... ahora los datos que tenemos proceden de nuestra caché. Si refrescamos una vez más después de que hayan pasado cinco segundos... ¡aquí está de nuevo nuestra petición HTTP!

A continuación: Aprendamos a configurar nuestro servicio de caché.
