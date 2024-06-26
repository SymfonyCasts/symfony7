# Servicios no autoconmutables

En el capítulo anterior, autoconectamos un argumento no autoconectable. Esta vez vamos a intentar autoconectar un servicio no autoconectable. Pero antes tenemos que encontrar un servicio no autoconectable. Para ello, en tu terminal, ejecuta:

```terminal
bin/console debug:container
```

Si crees que este servicio Twig no se puede autoconectar porque sólo es un ID, piénsalo otra vez. Si nos desplazamos hacia arriba, veremos `Twig\Environment`. Se trata de un alias de nuestro servicio Twig. Por el contrario, `twig.command.debug` no es autoconectable. Es el servicio que alimenta el comando `debug:twig` que utilizamos en capítulos anteriores. Cuando lo ejecutamos en nuestro terminal,

```terminal-silent
bin/console debug:twig
```

nos da una lista de todos los filtros y funciones Twig disponibles en nuestra aplicación. Eso significa que, aunque sea un poco raro, podemos coger este servicio y utilizarlo directamente. ¡Es bueno saberlo!

De vuelta aquí, en `homepage()`, teclea `DebugCommand` (el de Twig) y llamémoslo `$twigDebugCommand`. Si volvemos a nuestro navegador y actualizamos... obtendremos un error:

> No se puede autohilar el argumento `$twigDebugCommand` de
&gt `App\Controller\MainController::homepage()`

Si has adivinado que tendremos que utilizar el atributo encima del argumento como hicimos con nuestros parámetros, estás en lo cierto, pero la sintaxis de los servicios es un poco diferente. Aquí, encima de nuestro `DebugCommand`, añade un nuevo atributo: `#[Autowire()]`. Dentro, pondremos `service` al nombre del servicio. Haré trampas y copiaré el nombre exacto del servicio de la lista en nuestro terminal. Bien, si volvemos atrás y actualizamos de nuevo la página de inicio... se ha autocableado correctamente. Muy bien

Muy bien, veamos si podemos ejecutar ese comando. Debajo de `Response`, escribe `$twigDebugCommand->run()`. El primer argumento debe ser una entrada, así que podemos decir `new ArrayInput`. El segundo argumento debe ser la salida que utilizaremos a continuación, pero antes tenemos que crear una variable de salida. Arriba, escribe `$output = new BufferedOutput()`. Ahora podemos añadir aquí `$output` como segundo argumento. Vale, nuestro editor está contento, así que finalmente, abajo, escribamos `dd($output)`. Si vamos a nuestro navegador y actualizamos... caramba... error. Parece que tenemos que pasar un array vacío a la clase `ArrayInput()`. Si lo hacemos y volvemos a actualizar... ¡boom! Obtenemos una lista de funciones y filtros. Funciona. Esto era sólo un ejemplo, así que podemos deshacernos de ese código, pero lo fundamental es recordar que, aunque algo no sea autoconectable por defecto, puedes hacerlo autoconectable con un atributo `#[Autowire]`, independientemente de si necesitas un servicio o un parámetro.

A continuación: Vamos a hablar de las variables de entorno y de la finalidad del archivo `.env` que vimos antes. También veremos cómo podemos aprovecharlas en nuestra aplicación para que se comporte de forma diferente en determinados entornos.
