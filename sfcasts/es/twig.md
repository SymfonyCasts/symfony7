# Twig y plantillas

Quiero devolver HTML para esta página. Podríamos poner ese HTML dentro del controlador... pero eso se va a poner feo rápidamente. Afortunadamente, hay una forma mejor: utilizar una biblioteca de plantillas llamada Twig.

## Instalación de Twig

En tu terminal, asegúrate de haber confirmado tus cambios, porque quiero ver qué añade la receta de este nuevo paquete a nuestro proyecto. Ya lo he hecho. Instálalo con

```terminal
composer require twig
```

## Composer "Paquetes"

Probablemente reconozcas que `twig` es un alias... esta vez de un paquete llamado`symfony/twig-pack`. Y la palabra "paquete" es importante en Symfony. Un paquete es... una especie de paquete falso que ayuda a instalar varios paquetes a la vez.

Observa: abre `composer.json`. En lugar de un nuevo paquete aquí llamado`symfony/twig-pack`, tenemos tres nuevos paquetes... ¡y `twig-pack` ni siquiera es uno de ellos! 

[[[ code('525a45cd85') ]]]

Los tres paquetes nos dan todo lo que necesitamos para una configuración Twig completa y robusta. Así que cuando veas la palabra "paquete", no es gran cosa: sólo un atajo para instalar varios paquetes a la vez.

## Paquetes Symfony

Vale, ¡vamos a ver qué ha hecho la receta! Ejecuta:

```terminal
git status
```

Vemos los habituales `composer.json`, `composer.lock` y `symfony.lock`. Pero, por primera vez, también vemos una modificación de `config/bundles.php`. Un bundle es un paquete PHP que se integra con Symfony... es básicamente un plugin de Symfony. Siempre que instales un bundle, tienes que activarlo en este archivo `bundles.php`. Pero sinceramente, el sistema de recetas siempre lo hará por nosotros... así que es bueno darse cuenta, pero nunca editaremos este archivo a mano.

[[[ code('3e93886604') ]]]

## La receta Twig

Lo segundo que hizo la receta fue crear un archivo `config/packages/twig.yaml`. El propósito de cada archivo en `config/packages/` es configurar un bundle.

[[[ code('0fae61d6d1') ]]]

Por ejemplo, `twig.yaml` controla el comportamiento de TwigBundle. Esta línea de aquí le dice a Twig:

> ¡Oye! Todos mis archivos de plantilla terminarán en `.twig`.

Hay muchas más cosas que podríamos configurar, pero no hace falta. Y profundizaremos en estos archivos de configuración en el próximo tutorial.

Lo último que hizo la receta fue añadir un directorio `templates/`, que.... ¡lo has adivinado! Es donde vivirán nuestros archivos de plantilla Incluso nos inició con un archivo `base.html.twig`del que hablaremos en unos minutos.

## Renderizar una plantilla

Así que ¡vamos a renderizar nuestra primera plantilla! Para ello, haz que tu controlador extienda una clase base llamada `AbstractController`. Asegúrate de pulsar el tabulador para que se añada la sentencia `use`en la parte superior. Extender esta clase base es opcional, pero nos proporciona un montón de métodos abreviados.

[[[ code('57df7a6331') ]]]

Por ejemplo, copia la cadena y luego, para renderizar una plantilla escribe`return $this->render()` y pasa un nombre de archivo a una plantilla. Utiliza:`main/homepage.html.twig`.

[[[ code('f95ec85e49') ]]]

El nombre de archivo de tu plantilla puede ser el que quieras, pero lo estándar es tener un directorio que coincida con el nombre de tu controlador y un nombre de archivo que coincida con el nombre de tu método.

¡Vamos a crearlo! En `templates/`, añade un nuevo directorio llamado `main`. Y dentro de él, un archivo llamado `homepage.html.twig`. Luego añade un `h1` y ponlo alrededor de todo.

[[[ code('79313fecbf') ]]]

¡Hagamos esto! Actualiza. ¡Ya está!

Y por cierto, ¿qué devuelve nuestro controlador? Sigue siendo un objeto `Response`! Lo sé porque tenemos un tipo de retorno `Response`... y nuestro código no está explotando.`render()` es sólo un atajo para renderizar esta plantilla, coger esa cadena de HTML y ponerla en un objeto `Response`. Así que, aunque estemos renderizando una plantilla, seguimos volviendo a la idea de que un controlador devuelve una respuesta.

## Pasar datos a una plantilla

¿Qué hay de pasar datos a la plantilla? Quizá consultemos la base de datos y le pasemos el número total de naves estelares. Aún no tenemos una base de datos en nuestra aplicación, así que vamos a fingirlo diciendo que `$starshipCount` es igual a... No sé... 457.
Parece un número falso creíble.

[[[ code('ab8debff59') ]]]

Para pasar variables a la plantilla, añade un segundo argumento a `render()`: una matriz. Pasa `numberOfStarships` ajustado a `$starshipCount`. La clave se convertirá en el nombre de la variable dentro de la plantilla Twig. 

[[[ code('1477181f00') ]]]

## Renderizar variables

En la plantilla, añadiré un div y algo de texto. Para imprimir el número, escribe `{{`, el nombre de la variable, cierra `}}`.

[[[ code('ae442a20f9') ]]]

¡Vale! Muévete y pruébalo. ¡Ya está! ¡Y acabamos de ver nuestro primer código Twig!

Twig es su propio lenguaje, pero es superamigable. Sólo tiene tres sintaxis diferentes. La primera es `{{` y yo la llamo la sintaxis "decir algo". Si estás imprimiendo algo, utilizarás `{{`. Dentro de los rizos, estamos escribiendo Twig, que es muy similar a JavaScript.

## Etiquetas Twig y la sintaxis "hacer algo

Por ejemplo, podríamos imprimir la cadena `'numberOfStarships'`... o la variable `numberOfStarships`... o incluso `numberOfStarships` veces 10.

[[[ code('c208f5aa76') ]]]

La segunda sintaxis de las tres empieza por `{%`. Yo la llamo la sintaxis "hacer algo". Esto no imprime nada. En su lugar, se utiliza para construcciones del lenguaje como las sentencias `if`, los bucles for o establecer una variable.

Para hacer una sentencia if, di `if numberOfStarships > 400`, y ciérrala con`{% endif %}`. Dentro, añadiré un comentario.

[[[ code('4445d1588b') ]]]

¡Pruébalo! ¡Eso también funciona!

Twig es su propia biblioteca, pero está mantenida por Symfony... así que sus documentos están en https://twig.symfony.com. Haz clic en el enlace "Docs" y desplázate hacia abajo. ¿Ves las "etiquetas"? Resulta que hay un número finito de cosas que puedes utilizar con la sintaxis "hacer algo": son estas etiquetas. Por ejemplo, no puedes decir `{% applesauce`... simplemente no funcionará. Sólo puedes usar `{%` y luego una de estas etiquetas. La lista es bastante corta... y probablemente sólo utilice 5 de ellas a diario.

La tercera y última sintaxis de Twig ni siquiera es una sintaxis: es para los comentarios.`{#` para escribir un comentario.

[[[ code('c856ab7a8f') ]]]

## Representación de una matriz asociativa

Así que estamos pasando un simple número a Twig e imprimiéndolo. Pero Twig puede manejar cualquier dato complejo que le pases. Por ejemplo, en el controlador, crea una nueva variable `$myShip`, configurada como una matriz asociativa. Luego pásala a la plantilla como una nueva variable: `myShip`.

[[[ code('783a5d57f8') ]]]

En la plantilla, añade otro `div`... algo de texto y una tabla para imprimir los datos. En el `<td>`, no podemos simplemente imprimir `myShip`... porque imprimir una matriz asociativa no tiene sentido en PHP... y por tanto no tiene sentido en Twig. Obtendrás el famoso error sobre la conversión de array a cadena.

Lo que queremos es imprimir la clave `name` de esa matriz. La forma de hacerlo es exactamente igual que en JavaScript: `myShip.name`.

Ya está Y... funciona. Voy a pegar el resto de nuestra plantilla, que imprime las demás claves de la matriz. Tiene buena pinta.

[[[ code('7bba3c747c') ]]]

## Funciones y filtros Twig

Twig tiene algunos trucos más en la manga, pero nada complejo. Tiene funciones... que funcionan como las funciones de cualquier lenguaje. También tiene algo llamado pruebas, que son un poco exclusivas de Twig, pero bastante sencillas de entender. Mi concepto favorito son probablemente los filtros, que son básicamente funciones con una sintaxis más fresca y moderna.

Por ejemplo, hay un filtro llamado `upper` para enviar una cadena a mayúsculas. Para utilizar un filtro, busca la cadena que quieres convertir a mayúsculas y añade`|` y `upper`.

[[[ code('58924f0878') ]]]

El valor de la izquierda se pasa a través del filtro, muy parecido a utilizar una tubería en la línea de comandos. Funciona de maravilla.... y puedes volverte loco con los filtros: pasar a `upper`, luego a `lower` y después a `title` mayúsculas sólo para confundir a tus compañeros.

[[[ code('dcd9fd0237') ]]]

Vale, acabamos de aprender prácticamente todo Twig en una sesión, excepto una cosa: la herencia de plantillas. Eso a continuación.
