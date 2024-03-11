# Los Servicios: La columna vertebral de todo

Hablemos de los servicios. Son el concepto más importante en Symfony. Y una vez que los entiendas, sinceramente, serás capaz de hacer cualquier cosa.

## ¿Qué es un Servicio?

En primer lugar, un servicio es un objeto que hace un trabajo. Eso es todo. Por ejemplo, si instancias un objeto `Logger` que tiene un método `log()`, ¡eso es un servicio! Funciona: ¡registra cosas! O si creaste un objeto de conexión a la base de datos que hace consultas a la base de datos, entonces... ¡sí! Eso también es un servicio.

Entonces... si un servicio es sólo un objeto que funciona... ¿qué objetos perezosos no son servicios? Nuestra clase `Starship` es un ejemplo perfecto de no-servicio. Su función principal no es hacer trabajo: es guardar datos. Claro, tiene unos cuantos métodos públicos... e incluso podrías poner algo de lógica dentro de estos métodos para hacer algo. Pero en última instancia, no es un trabajador, es un poseedor de datos.

¿Y las clases controladoras? Sí, también son servicios. Su trabajo consiste en crear objetos de respuesta.

De todas formas, todo el trabajo que se hace en Symfony lo hace en realidad un servicio. ¿Escribir mensajes de registro en este archivo? Sí, hay un servicio para eso. ¿Descubrir qué ruta coincide con la URL actual? ¡Ese es el servicio `router`! ¿Y la representación de una plantilla Twig? Sí, resulta que el método `render()` es un atajo para encontrar el objeto de servicio correcto y llamar a un método en él.

## El contenedor y debug:container

A veces también oirás que estos servicios están organizados en un gran objeto llamado "contenedor de servicios". Puedes pensar en el contenedor como en una gigantesca matriz asociativa de objetos de servicio, cada uno con un identificador único. ¿Quieres ver una lista de todos los servicios de nuestra aplicación ahora mismo? Yo también

Busca tu terminal y ejecuta:

```terminal
bin/console debug:container
```

¡Son muchos servicios! Déjame hacerlo más pequeño para que cada uno quepa en su propia línea... mejor.

A la izquierda, vemos el ID de cada servicio. Y a la derecha, la clase del objeto al que corresponde el ID. Genial, ¿verdad?

Vuelve a nuestro controlador y mantén pulsado control o comando para abrir de nuevo el método `json()`. ¡Ahora tiene más sentido! Está comprobando si el contenedor tiene un servicio cuyo ID es `serializer`. Si es así, coge ese servicio del contenedor y llama al método `serialize()` sobre él.

Cuando trabajemos con servicios, no será exactamente así. Pero lo superimportante es que ahora entendemos lo que está pasando.

## Los bundles proporcionan servicios

Mi siguiente pregunta es: ¿de dónde vienen estos servicios? Por ejemplo, ¿quién dice que hay un servicio cuyo ID es `twig`... y que cuando se lo pedimos al contenedor, éste debe devolver un objeto Twig `Environment`? La respuesta es: totalmente de bundles. De hecho, ése es el objetivo principal de instalar un nuevo bundle. Los bundles nos proporcionan servicios.

¿Recuerdas cuando instalamos `twig`? Añadió un bundle a nuestra aplicación. ¿Y adivinas qué hizo ese bundle? Sí: nos proporcionó nuevos servicios, incluido el servicio`twig`. Los bundles nos dan servicios... y los servicios son herramientas.

## Autocableado

Y aunque hay muchos servicios en esta lista, la gran mayoría son objetos de servicio de bajo nivel que nunca utilizaremos ni nos interesarán. Tampoco nos importará el ID de los servicios la mayoría de las veces.

En su lugar, ejecuta un comando relacionado llamado:

```terminal
php bin/console debug:autowiring
```

Esto nos muestra todos los servicios que son autocableables, que es la técnica que utilizaremos para obtener servicios. Básicamente, es una lista curada de los servicios que es más probable que necesitemos.

## Autoconexión del servicio Logger

Hagamos un reto: registremos algo desde nuestro controlador. He aquí un vistazo a cómo enfoco este problema en mi cerebro:

> Vale, ¡necesito registrar algo!
> Y... registrar es trabajo.
> Y... ¡los servicios funcionan!
> Por tanto, ¡tiene que haber un servicio de registro que pueda utilizar!
> ¡Quod erat demonstrandum!

Perdonadme, frikis del latín. La cuestión es: si queremos registrar algo, sólo tenemos que encontrar el servicio que hace ese trabajo. ¡De acuerdo! Vuelve a ejecutar el comando pero busca log:

```terminal-silent
php bin/console debug:autowiring log
```

¡Boom! Ha encontrado unos 10 servicios, todos ellos empiezan por `Psr\Log\LoggerInterface`. Hablaremos de cuáles son esos otros servicios en el próximo tutorial. Por ahora, céntrate en el principal. Esto me dice que hay un servicio en el contenedor para un registrador. Y para obtenerlo, podemos autoconectarlo utilizando esta interfaz.

¿Qué significa esto? En el método del controlador donde queremos el logger, añade un argumento de tipo `LoggerInterface` - pulsa tabulador - y luego di `$logger`.

[[[ code('6b3f42abe8') ]]]

En este caso, el nombre del argumento no es importante: podría ser cualquier cosa. Lo que importa es que el `LoggerInterface` -que corresponde a esta declaración `use` - coincida con el `Psr\Log\LoggerInterface` de `debug:autowiring`.

¡Así de sencillo! Symfony verá esta sugerencia de tipo y dirá:

> ¡Oh! Como ese type-hint coincide con el tipo de autocableado de este servicio, deben
> querer que les pase ese objeto de servicio.

No sé por qué Symfony suena como una rana en mi cabeza. En fin, veamos si esto funciona. Añade `dd($logger)`: `dd()` significa "volcar y morir" y viene de Symfony.

[[[ code('7f28368d2c') ]]]

¡Actualiza! ¡Sí! Imprimió el objeto maravillosamente y luego detuvo la ejecución. ¡Funciona! Symfony nos pasa un objeto `Monolog\Logger`, que implementa ese`LoggerInterface`.

El truco que acabamos de hacer -llamado autocableado- funciona exactamente en dos lugares: los métodos de nuestro controlador y el método `__construct()` de cualquier servicio. Veremos esta segunda situación en el próximo capítulo.

## Controlar el comportamiento de los servicios

Y si te estás preguntando de dónde salió este servicio `Logger` en primer lugar... ¡ya sabemos la respuesta! De un bundle. En este caso, `MonologBundle`. Y... ¿cómo podríamos configurar ese servicio... para que, no sé, se registre en un archivo diferente? La respuesta es: `config/packages/monolog.yaml`.

Esta configuración -incluida esta línea- configura `MonologBundle`... lo que en realidad significa que configura cómo funcionan los servicios que nos proporciona MonologBundle. Aprenderemos sobre esta sintaxis porcentual en el próximo tutorial, pero esto le dice al servicio `Logger`que registre en este archivo `dev.log`.

## Utilizar el Logger

Bien, ahora que tenemos el servicio `Logger`, ¡vamos a utilizarlo! ¿Cómo? Bueno, por supuesto, puedes leer la documentación. Pero gracias a la sugerencia de tipo, ¡nuestro editor nos ayudará!`LoggerInterface` tiene un montón de métodos. Utilicemos `->info()` y digamos

[[[ code('d3a5032330') ]]]

> Colección de naves recuperada.

Pruébalo: actualizar. La página funcionó... ¿pero registró algo? Podríamos comprobar el archivo `dev.log`. O podemos utilizar la sección Registro del perfilador para esta petición.

## Ver el Perfilador de una petición API

Pero... ¡espera! Esto es una petición API... ¡así que no tenemos esa genial barra de herramientas de depuración web en la parte inferior! Es cierto... ¡pero Symfony sigue recopilando toda esa información! Para acceder al perfilador de esta petición, cambia la URL a `/_profiler`. Esto muestra las peticiones más recientes a nuestra aplicación, con la más reciente en la parte superior. ¿Ves ésta? ¡Es nuestra petición a la API de hace un minuto! Si haces clic en este token... ¡bum! Estamos viendo el perfilador de esa llamada a la API en todo su esplendor... incluyendo una sección de Registro... con nuestro mensaje.

Bien, ahora que hemos visto cómo utilizar un servicio, ¡vamos a crear nuestro propio servicio! Somos imparables!
