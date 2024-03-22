# Crear tu propio Servicio

Sabemos que los servicios funcionan, y sabemos que Symfony está lleno de servicios que podemos utilizar. Si Ejecutas:

```terminal
php bin/console debug:autowiring
```

Obtenemos el menú de servicios, en el que puedes pedir cualquiera de ellos añadiendo un argumento de tipo con la clase o interfaz correspondiente.

Por supuesto, también hacemos trabajo en nuestro código... con suerte. Ahora mismo, todo ese trabajo se realiza dentro de nuestro controlador, como la creación de los datos de la Nave Estelar. Claro, esto está codificado ahora mismo, pero imagina que fuera trabajo real: como una consulta compleja a una base de datos. Poner la lógica dentro de un controlador está "bien"... pero ¿y si quisiéramos reutilizar este código en otro sitio? ¿Y si, en nuestra página de inicio, quisiéramos obtener un recuento dinámico de las naves estelares tomando estos mismos datos?

## Crear la clase de servicio

Para ello, tenemos que trasladar este "trabajo" a un servicio propio que puedan utilizar ambos controladores. En el directorio `src/`, crea un nuevo directorio `Repository` y una nueva clase PHP en su interior llamada `StarshipRepository`.

[[[ code('9be4c8e3f6') ]]]

Al igual que cuando creamos nuestra clase `Starship`, esta nueva clase no tiene absolutamente nada que ver con Symfony. Es sólo una clase que hemos decidido crear para organizar nuestro trabajo. Por lo tanto, a Symfony no le importa cómo se llama, dónde vive o qué aspecto tiene. Yo la llamé `StarshipRepository` y la puse en un directorio `Repository` porque es un nombre de programación común para una clase cuyo "trabajo" es obtener un tipo de datos, como los datos de la nave estelar.

## Autocableado del nuevo servicio

Vale, antes de hacer nada aquí, vamos a ver si podemos utilizar esto dentro de un controlador. Y, ¡buenas noticias! Sólo con crear esta clase, ya está disponible para autocableado. Añade un argumento `StarshipRepository $repository` y, para asegurarte de que funciona, `dd($repository)`.

[[[ code('f5d319e9bc') ]]]

Muy bien, gira, vuelve a hacer clic en nuestra ruta, y... ya está. Qué guay! Symfony ha visto la sugerencia de tipo `StarshipRepository`, ha instanciado ese objeto y nos lo ha pasado. Borra el `dd()`... y movamos los datos de la nave estelar dentro. Cópialo... y crea una nueva función pública llamada, qué tal, `findAll()`. Dentro, `return`, y pégala.

[[[ code('32a1c49c94') ]]]

De vuelta en `StarshipApiController`, borra eso... y queda maravillosamente sencillo:`$starships = $repository->findAll()`.

[[[ code('65335f0982') ]]]

¡Listo! Cuando lo probamos, sigue funcionando... y ahora el código para obtener naves estelares está bien organizado en su propia clase y es reutilizable en toda nuestra aplicación.

## Autocableado del Constructor

Con esta victoria en nuestro haber, vamos a hacer algo más difícil. ¿Qué pasaría si, desde dentro de `StarshipRepository`, necesitáramos acceder a otro servicio que nos ayudara a hacer nuestro trabajo? ¡No hay problema! ¡Podemos utilizar el autocableado! Intentemos autocablear de nuevo el servicio logger.

La única diferencia esta vez es que no vamos a añadir el argumento a `findAll()`. Te explicaré por qué en un minuto. En lugar de eso, añade un nuevo `public function __construct()`y realiza el autocableado allí: `private LoggerInterface $logger`.

[[[ code('f4faf9e448') ]]]

A continuación, para utilizarlo, copia el código de nuestro controlador, bórralo, pégalo aquí y actualízalo a `$this->logger`.

[[[ code('9e890e9870') ]]]

¡Genial! En el controlador, podemos eliminar ese argumento porque ya no lo vamos a utilizar.

¡Hora de probar! ¡Actualiza! No hay error: buena señal. Para ver si se ha registrado algo, ve a `/_profiler`, haz clic en la petición superior, Registros, y... ¡ahí está!

Te explicaré por qué hemos añadido el argumento de servicio al constructor. Si queremos obtener un servicio -como el registrador, una conexión a una base de datos, lo que sea-, ésta es la forma correcta de utilizar el autocableado: añadir un método `__construct` dentro de otro servicio. El truco que vimos antes -en el que añadimos el argumento a un método normal- sí, eso es especial y sólo funciona para los métodos del controlador. Es una comodidad adicional que se añadió al sistema. Es una gran característica, pero la forma del constructor... así es como funciona realmente el autocableado.

Y esta forma "normal", funciona incluso en un controlador. Podrías añadir un método `__construct()`con un argumento autocableable y funcionaría perfectamente.

La cuestión es: si estás en un método controlador, claro, añade el argumento al método, ¡está bien! Sólo recuerda que es algo especial que sólo funciona aquí. En cualquier otra parte, autowire a través del constructor.

## Utilizar el Servicio en otra Página

Celebremos nuestro nuevo servicio utilizándolo en la página principal. Abre`MainController`. Este `$starshipCount` codificado es tan de hace 30 minutos. Autocablea`StarshipRepository $starshipRepository`, luego di`$ships = $starshipRepository->findAll()` y cuéntalos con `count()`.

[[[ code('9b65357996') ]]]

Ya que estamos aquí, en lugar de esta matriz `$myShip` codificada, vamos a coger un objeto `Starship` al azar. Podemos hacerlo diciendo `$myShip` igual a`$ships[array_rand($ships)]`

[[[ code('5823e7aff9') ]]]

¡Vamos a probarlo! Busca en tu navegador y dirígete a la página de inicio. ¡Ya está! Vemos el barco que cambia aleatoriamente aquí abajo, y el número de barco correcto aquí arriba... porque lo estamos multiplicando por 10 en la plantilla.

## Imprimiendo objetos en Twig

¡Y acaba de ocurrir algo alucinante! Hace un momento, `myShip` era una matriz asociativa. Pero lo hemos cambiado para que sea un objeto Starship. Y aún así, el código de nuestra página siguió funcionando. Acabamos de ver accidentalmente un superpoder de Twig. Ve a`templates/main/homepage.html.twig` y desplázate hasta el final. Cuando dices`myShip.name`, Twig es realmente inteligente. Si `myShip` es una matriz asociativa, cogerá la clave `name`. Si `myShip` es un objeto, como lo es ahora, cogerá la propiedad `name`. Pero aún más, si miras `Starship`, la propiedad `name` es privada, por lo que no podemos acceder a ella directamente. Twig se da cuenta de ello. Mira la propiedad`name`, ve que es privada, pero también ve que hay una`getName()` pública. Así que llama a esa.

Todo lo que tenemos que decir es `myShip.name`... y Twig se encarga de los detalles de cómo obtenerlo, lo cual me encanta.

Vale, un último pequeño ajuste. En lugar de pasar el `starshipCount` a nuestra plantilla, podemos hacer el recuento dentro de Twig. Elimina esta variable y, en su lugar, pasa una variable `ships`. 

[[[ code('1e0a8b1e9e') ]]]

En la plantilla, ahí lo tenemos, para el recuento, podemos decir `ships`, que es una matriz, y luego utilizar un filtro Twig: `|length`.

[[[ code('7ba898b73e') ]]]

Así queda bien. Hagamos lo mismo aquí abajo... y cambiémoslo a mayor que 2. Pruébalo. ¡Nuestro sitio sigue funcionando!

Lo siguiente: creemos más páginas y aprendamos a hacer rutas aún más inteligentes.
