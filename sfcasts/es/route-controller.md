# Rutas, controladores y respuestas

Bien, ésta es la primicia. Wesley Crusher -el alférez favorito de todos en Star Trek- se ha retirado de la Flota Estelar y colabora con nosotros para poner en marcha un nuevo negocio: La Tienda Estelar de Wesley. Alguien tiene que romper el monopolio ferengi en el negocio de reparación de naves estelares de la galaxia, y nos ha contratado para construir el sitio que lo haga. ¡Estamos a punto de darles a los ferengis una carrera por su latinio!

## Creación del controlador

Y todo empieza con la creación de nuestra primera página. La idea detrás de cada página es siempre la misma. Primer paso: dale una URL chula. Eso se llama la ruta. Paso dos, escribir una función PHP que genere la página. Eso se conoce como el controlador. Y esa página puede ser HTML, JSON, arte ASCII, cualquier cosa.

En Symfony, el controlador es siempre un método dentro de una clase PHP. Así que, ¡necesitamos crear nuestro primer código PHP! ¿Dónde vive el código PHP en nuestra aplicación? Exacto, en el directorio`src/`.

Dentro de este directorio `src/Controller/`, crea un nuevo archivo. Normalmente seleccionaría nueva "clase PHP", pero para esta primera vez, crea un archivo vacío. Haremos cada parte a mano. Llámalo `MainController.php`, pero puedes ponerle el nombre que quieras.

Dentro, añade la etiqueta open PHP, y luego di `class MainController`. Encima de esto, añade un espacio de nombres de `App\Controller`.

[[[ code('51ebc199a2') ]]]

## Espacios de nombres y directorios

Bien, algunas cosas sobre esto. Primero, el hecho de que ponga esta clase dentro de un directorio llamado `Controller` es opcional. Es sólo una convención. Podrías cambiarle el nombre por cualquiera que sea la palabra klingon para `Controller` y todo seguiría igual... ¡y probablemente sería más interesante!

Sin embargo, hay algunas reglas sobre las clases PHP en general. La primera es que cada clase debe tener un espacio de nombres y ese espacio de nombres tiene que coincidir con tu estructura de directorios. Siempre será `App\` y luego el directorio en el que estés. Sin entrar en demasiados detalles, es una regla que encontrarás en todos los proyectos PHP.

La segunda regla es que el nombre de tu clase debe coincidir con el nombre de tu archivo `.php`. Si te equivocas en cualquiera de estas dos cosas, recibirás un error de PHP diciendo que no puede encontrar tu clase. Los Ferengi nunca cometen este error.

## Crear el método controlador y la ruta

De todos modos, nuestro objetivo es crear un controlador, que es un método en una clase que construye la página. Añade una nueva función pública y llámala `homepage`. Pero, de nuevo, el nombre no importa. Y... ¡sí! Aún no está hecho, ¡pero éste es nuestro controlador!

[[[ code('77472b5890') ]]]

Pero recuerda, una página es la combinación de un controlador y una ruta, que define la URL de la página. ¿Dónde ponemos la ruta? Justo encima del método controlador, utilizando una función de PHP llamada atributo. Escribe `#[]` y luego empieza a escribir`Route` con mayúscula `R`. ¡Fíjate en el autocompletado!

Cualquiera de las opciones funcionará, pero utiliza la de `Attribute` -que es más nueva- y luego pulsa tabulador. Cuando hice eso, ocurrió algo superimportante: mi editor añadió una declaración`use` al principio de la clase. Siempre que utilices un atributo PHP, debes tener una declaración `use` correspondiente para él en el mismo archivo.

Estos atributos funcionan casi como las funciones PHP: puedes pasar un montón de argumentos. El primero es la ruta. Establécela en `/`.

[[[ code('08f356a54e') ]]]

Gracias a esto, cuando alguien vaya a la página de inicio - `/` - ¡Symfony llamará a este método controlador para construir la página!

## Controladores y respuestas

¿Qué... debería devolver nuestro método? Sólo el HTML que queremos, ¿verdad? ¿O el JSON si estamos construyendo una API?

Casi. La web funciona con un sistema bien conocido. Primero, un usuario solicita una página. Dice:

> Oye, quiero ver `/products`... o quiero ver `/users.json`.

Lo que les devolvemos, sí, contiene HTML o JSON. Pero es más que eso. También les comunicamos un código de estado -que dice si la respuesta era correcta o tenía un error-, así como estas cosas llamadas cabeceras, que comunican un poco más de información, como el formato de lo que estamos devolviendo.

Todo este hermoso paquete se llama respuesta. Así que sí, la mayoría de las veces, sólo pensaremos en devolver HTML o JSON. Pero lo que realmente estamos enviando es esta cosa más grande y friki llamada respuesta.

Así que todo nuestro trabajo como desarrolladores web -independientemente del lenguaje en el que programemos- consiste en comprender la petición del usuario y, a continuación, crear y devolver la respuesta.

Y esto nos lleva de nuevo a algo que me encanta de Symfony. ¿Qué devuelve nuestro controlador? ¡Un nuevo objeto `Response` de Symfony! Y de nuevo, PhpStorm quiere autocompletar esto, sugiriendo unas cuantas clases diferentes de `Response`. Nosotros queremos la del componente `HttpFoundation` de Symfony. Esa es la librería de Symfony que contiene todo lo relacionado con peticiones y respuestas.

Pulsa tabulador. Una vez más, cuando hicimos eso, PhpStorm añadió una declaración `use` en la parte superior del archivo. Voy a utilizar este truco constantemente. Cada vez que hagas referencia a un nombre de clase, debes tener una declaración `use` correspondiente, de lo contrario PHP te dará un error diciendo que no puede encontrar la clase `Response`.

Dentro de esto, el primer argumento es el contenido que queremos devolver. Empieza con una cadena codificada.

[[[ code('1df1b30322') ]]]

Ruta, ¡comprobado! Controlador que devuelve una Respuesta, ¡comprobado! Probemos esto. En el navegador, esta página era sólo una demo que muestra antes de que tengamos una página de inicio real. Ahora que la tenemos, al actualizar... ¡ahí está!

Sé que aún no es mucho, pero acabamos de aprender la primera parte fundamental de Symfony: que cada página es una ruta y un controlador... y que cada controlador devuelve una respuesta.

Ah, y es opcional, pero como nuestro controlador siempre devuelve un `Response`, podemos añadir un tipo de retorno `Response`. Eso no cambia el funcionamiento de nuestro código, pero lo hace más descriptivo de leer. Y si alguna vez hiciéramos una tontería y devolviéramos algo que no fuera una respuesta, PHP nos lo recordaría claramente.

[[[ code('5d504440d6') ]]]

A continuación: para potenciar nuestro desarrollo, instalemos nuestro primer paquete de terceros y conozcamos el increíble sistema de recetas de Symfony.
