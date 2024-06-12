# El servicio cliente HTTP

Sabemos que Symfony es una colección de un montón de minúsculas librerías PHP independientes, llamadas "componentes". Ahora mismo sólo tenemos instalados un pequeño número de ellos, pero a medida que necesitemos más funciones, instalaremos más componentes. En el último tutorial, instalamos el componente `serializer` para ayudarnos a serializar objetos en JSON. Dirígete y abre `StarshipApiController.php`. Aquí abajo, mantén pulsada la tecla "cmd" o "control" en un Mac y haz clic en este método `json()`. Aquí tenemos nuestro componente `serializer`. Esto comprueba si tenemos este servicio, y si lo tenemos, se llama al método `serialize()`.

Vale, nuestra web es bastante chula, pero ¿no sería mucho más chulo si... digamos... mostráramos la ubicación en tiempo real de la Estación Espacial Internacional (o ISS)? 
¡Claro que sí! Y por suerte, ya existe un sitio web que muestra esa información. Navegaremos hasta `wheretheiss.at` y... ¡comprobaremos! Parece que la ISS está en algún lugar sobre el Océano Pacífico en este momento y -buenas noticias- también tienen una API que podemos utilizar para obtener las coordenadas de la ISS e imprimirlas en nuestro sitio web. ¡Qué práctico! Puedes copiar esta URL y abrirla en una nueva pestaña para ver el JSON.

Pero antes, vamos a comprobar si nuestra aplicación ya tiene un cliente HTTP que nos ayude a ejecutar algunas peticiones a la API. En tu terminal, ejecuta:

```terminal
bin/console debug:autowiring http
```

Y... tenemos algunos servicios relacionados con HTTP, pero ningún cliente HTTP. ¡Y así es! Aún no tenemos un servicio en nuestra aplicación que pueda hacer peticiones HTTP, pero podemos instalarlo. Para ello, necesitamos el componente `http-client`, que, como su nombre indica, es ideal para hacer peticiones HTTP externas. En tu terminal, ejecuta:

```terminal
composer require symfony/http-client
```

Si te preguntas de dónde viene el nombre de este paquete, ¡buena pregunta! Si buscas "symfony http client" en tu navegador, uno de los primeros resultados es esta documentación sobre Symfony HTTP Client. En "Instalación", encontrarás este comando de terminal, junto con información útil sobre el componente.

Ahora, de vuelta a nuestro terminal, ejecutemos

```terminal
bin/console debug:autowiring http
```

y... ¡ahí está nuestro `HttpClient`! Ahora que tenemos nuestro nuevo servicio, podemos teclearlo en nuestra aplicación. Pero... espera... esto no ha instalado ningún bundle. Si ejecutas

```terminal
git status
```

verás que los únicos archivos que han cambiado son `composer.json`y `composer.lock`. ¡No pasa nada! Lo que instalamos fue un paquete PHP puro, y aunque contiene clases de servicio (que no son más que clases que hacen su trabajo), no contiene ninguna configuración que diga:

> ¡Oye! Quiero tener un servicio llamado "http_client",
> que debería ser una instancia de `HttpClientInterface`,
> y debe instanciarse con estos argumentos específicos
> argumentos específicos.

¿Y de dónde viene este servicio? La respuesta es FrameworkBundle. Abre `config/bundles.php`. El primer bundle aquí es `FrameworkBundle`. Es un bundle básico de Symfony, y ha estado en nuestra aplicación desde el principio. El superpoder de este bundle es buscar componentes Symfony recién instalados y registrar automáticamente sus servicios. Súper práctico.

[[[ code('ba6f99b1ed') ]]]

Ahora que tenemos nuestro nuevo `HttpClient`, ¡pongámoslo a trabajar! Abre `MainController.php` y, en `homepage()`, escribamos hint nuestro nuevo servicio. Lo trasladaré a varias líneas... escribe `HttpClientInterface`... y llámalo `$client`. 

[[[ code('c036fc2095') ]]]

Aquí abajo, antes de la declaración `return`, escribe `$client->`. Tenemos unos cuantos métodos para elegir, así que selecciona `request()`. Dentro, escribe `GET`, y luego tenemos que enviar una petición a esta URL. 
Para ahorrarte algo de tiempo, puedes copiar este enlace de la página que hay debajo de este vídeo. 
Por aquí, añade `$response`... y debajo, escribe `$response->toArray()`. 
Ese es un práctico método que descodifica JSON en una matriz. Y por último, añadiremos esta variable `$issData`. 
Para ver si funciona, podemos seguir adelante y escribir `dump($issData)` aquí.

[[[ code('1ce13e55a8') ]]]

En tu navegador, actualiza la página de inicio y, aquí abajo, si pasas el ratón por encima de este icono... ¡qué bien! ¡Esos son nuestros datos! Justo al lado hay otro icono que habrás notado. Es el Cliente HTTP, y nos muestra el número total de peticiones que se han ejecutado en esta página. Haz clic en este icono de Depuración para abrir el Perfilador Symfony e inspeccionarlo. Nuestro Cliente HTTP está integrado con la barra de herramientas de depuración web, y podemos ver que nuestra petición se ha ejecutado. ¡Estupendo!

De vuelta por aquí, elimina el `dump()` y pasa esos datos a la plantilla.

[[[ code('b41efad2e3') ]]]

En `homepage.html.twig`, aquí abajo al final, añade otro `<div>`. Dentro, añade un `<h2>`, y llamémoslo `ISS Location`. También añadiremos algunas clases para que quede bonito. Bien, aquí abajo, vamos a añadir algunas etiquetas `<p>` con nuestros datos: `Time: {{ issData.timestamp|date }}`, `Altitude: {{ issData.altitude }}`,`Latitude: {{ issData.latitude }}`, `Longitude: {{ issData.longitude }}`, y `Visibility: {{ issData.visibility }}`. 

[[[ code('6046624285') ]]]

De vuelta a nuestro navegador, actualizamos y... ¡aquí está! ¡Esta es la ubicación en tiempo real de la Estación Espacial Internacional con todos los datos que acabamos de renderizar! ¡Tiene buena pinta!

Por muy guay que sea, ahora cada vez que alguien navega a nuestra página de inicio, estamos haciendo una petición HTTP a la API, y las peticiones HTTP son lentas. Para solucionarlo, vamos a aprovechar otro servicio de Symfony: el servicio de caché.
