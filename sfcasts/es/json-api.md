# Creación de rutas API JSON

Si quieres crear una API, puedes hacerlo absolutamente con Symfony. De hecho, es una opción fantástica, en parte gracias a API Platform. Se trata de un marco para crear APIs construido sobre Symfony que agiliza la construcción de tu API y crea una API más robusta de lo que puedas imaginar.

Pero también es bastante sencillo devolver JSON desde un controlador. Veamos si podemos devolver algunos datos del barco como JSON.

## Creación de la nueva Ruta y Controlador

Esta será nuestra segunda página. Bueno, en realidad es un "punto final", pero será nuestra segunda combinación de ruta y controlador. En `MainController`, podríamos añadir otro método aquí. Pero para organizarnos, vamos a crear una clase de controlador totalmente nueva. Iré a Nuevo -> Clase PHP y la llamaré `StarshipApiController`.

Como he ido a Nuevo -> Clase PHP, me ha creado la clase y el espacio de nombres ¡Súper bien! Además, en adelante, cada vez que cree un controlador, extenderé inmediatamente `AbstractController`... porque esos atajos son agradables y no hay inconveniente.

[[[ code('e002409119') ]]]

Añade un `public function getCollection()` porque esto devolverá información sobre una colección de naves estelares. Y, como siempre, puedes añadir el tipo de retorno `Response` u omitirlo. Encima de esto, añade la ruta con `#[Route()]`. Selecciona la de`Attribute` y pulsa tabulador.

Así que acabo de utilizar el autocompletado para añadir las declaraciones `use` para `AbstractController`,`Route`, y `Response`. Asegúrate de que las tienes todas. Para la URL, ¿qué tal`/api/starships`.

En su interior, pegaré una variable `$starships` que se establecerá en una matriz de tres matrices asociativas de datos de naves estelares.

## Devolver JSON

Probablemente puedas imaginar qué aspecto tendrá esto como JSON. ¿Cómo lo convertimos en JSON? Bueno, puede ser así de sencillo: `return new Response` con`json_encode($starships)`.

¡Pero podemos hacerlo mejor! En lugar de eso, devuelve `$this->json($starships)`.

[[[ code('e52ace2207') ]]]

¡Vamos a probarlo! Busca tu navegador y dirígete a `/api/starships`. Vaya, ha sido fácil. Si te preguntas por qué el JSON está estilizado y tiene un aspecto chulo, no es cosa de Symfony. Tengo instalada una extensión de Chrome llamada JSONVue.

## Añadir una clase modelo

Ahora, en el mundo real, cuando empecemos a consultar la base de datos, vamos a trabajar con objetos, no con matrices asociativas. No añadiremos una base de datos en este tutorial, pero podemos empezar a utilizar objetos para nuestros datos para hacer las cosas más realistas. En el directorio`src/`, crea un nuevo subdirectorio llamado `Model`.

Vale, algo importante: lo que vamos a hacer no tiene absolutamente nada que ver con Symfony. Simplemente estoy mirando este array y pensando:

> ¿Sabes qué? En lugar de pasar por este array asociativo con `name`,
> `class`, `captain`, y `status` claves, prefiero tener una clase `Starship` y
> pasar objetos.

Así que, por mi cuenta, independientemente de Symfony, he decidido crear un directorio `Model`-que podría llamarse cualquier cosa- y dentro una nueva clase llamada `Starship`. Y como esta clase es sólo para ayudarnos, podemos darle el aspecto que queramos, y no necesita extender ninguna clase base.

[[[ code('9e8dc4aef1') ]]]

Crea un `public function __construct()` con cinco propiedades: una `private int $id`, y luego cuatro propiedades más para cada una de las cuatro claves que tenemos en la matriz:`private string $name`, `private string $class`, `private string $captain` y`private string $status`.

[[[ code('5fac6bec3b') ]]]

Ah, y mi editor está resaltando este archivo porque hemos instalado PHP-CS-Fixer y ha encontrado una violación del estilo del código. Puedo hacer clic aquí para corregirlo o ir aquí y pulsar Alt+Enter para corregirlo allí. ¡Súper bonito!

De todas formas, si no estás familiarizado con la sintaxis de este constructor, esto crea un constructor con cinco argumentos y, al mismo tiempo, crea cinco propiedades que se establecerán a lo que pasemos a estos argumentos.

Pero, como he decidido que estas propiedades sean privadas, si instanciáramos un nuevo objeto `Starship`... ¡no podríamos leer ninguno de los datos! Para permitirlo, podemos crear métodos getter. Pero, no voy a hacer esto a mano. En su lugar, ve a la opción de menú Código -> Generar -o Cmd + N en Mac-, selecciona getters y genera un getter para cada propiedad.

[[[ code('c29463d2b4') ]]]

¡Qué bien! Cinco nuevos y brillantes métodos getter públicos.

## Crear los objetos modelo

Vale, de vuelta en nuestro controlador, convirtamos estas matrices en objetos: `new Starship()` - pulsa tabulador, para que añada la declaración `use` - luego dale un id de, qué tal, 1... y transfiere los otros valores para `name`, `class`, `captain`, y finalmente `status`.

Y así de fácil, ¡ya tenemos nuestro primer objeto! Resaltaré las otras dos matrices y pegaré los dos objetos para ahorrar tiempo.

[[[ code('419fec735e') ]]]

Ahora tenemos una matriz de 3 objetos `Starship`... que queda más bonita. Y los pasamos a `$this->json()`. ¿Seguirá funcionando? Por supuesto que no ¡Obtenemos una matriz de tres objetos vacíos!

Eso es porque, internamente, `$this->json()` utiliza la función PHP `json_encode()`... y esa función no puede manejar propiedades privadas. Lo que necesitamos es algo más inteligente: algo que pueda reconocer que, aunque la propiedad `name` es privada, tenemos un método público `getName()` al que se puede llamar para leer el valor de esa propiedad.

## Hola Serializador Symfony

¿Existe alguna herramienta que haga eso? Bueno, ¿recuerdas que Symfony es un enorme conjunto de componentes que resuelven problemas individuales? Un componente se llama serializador, y su trabajo consiste en tomar objetos y serializarlos a JSON... o tomar JSON y deserializarlo de nuevo a objetos. Y puede manejar totalmente situaciones en las que tienes propiedades privadas con métodos getter públicos.

Así que ¡a instalarlo!

```terminal
composer require serializer
```

Y una vez más, amigos, sí, esto es un alias... y es un alias de un paquete. Este paquete instala el paquete `symfony/serializer`, así como algunos otros que lo hacen funcionar de forma realmente robusta.

Ahora, sin hacer nada más, vuelve atrás, actualiza, ¿y funciona? ¿Cómo?

Resulta que el método `$this->json()` es inteligente. Para verlo, mantén pulsado Comando en un Mac o Ctrl en otras máquinas y haz clic en el nombre del método para saltar al archivo principal de Symfony donde se encuentra.

¡Ah! El código aquí aún no tendrá todo el sentido, pero detecta si el sistema serializador está disponible.... y, si lo está, lo utiliza para transformar el objeto a JSON.

Pero, ¿qué quiero decir exactamente con "sistema serializador"? ¿Y cuál es la clave `serializer`... dentro de esta cosa del contenedor? O, ¿qué pasaría si necesitáramos transformar un objeto a JSON en otro lugar que no fuera nuestro controlador... donde no tuviéramos acceso al acceso directo`->json()`? ¿Cómo podríamos acceder al sistema serializador desde allí?

Amigos, es hora de conocer el concepto más importante de Symfony: los servicios.
