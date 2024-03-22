# Generar URLs

Vamos a crear una "página de presentación" de barcos: una página que muestre los detalles de un solo barco. La página de inicio vive en `MainController`. Y así podríamos añadir otra ruta y método aquí. Pero a medida que mi sitio crezca, probablemente tendré varias páginas relacionadas con naves estelares: quizá para editarlas y eliminarlas. Así que, en lugar de eso, en el directorio `Controller/`, crea una nueva clase. Llámala `StarshipController`, y, como de costumbre, extiende`AbstractController`.

## Crear la página Mostrar

Dentro, ¡manos a la obra! Añade un `public function` llamado `show()`, yo añadiré el tipo de retorno `Response`, luego la ruta, con `/starships/` y un comodín llamado `{id}`. Y de nuevo, es opcional, pero seré extravagante y añadiré el `\d+` para que el comodín sólo coincida con un número.

Ahora, como tenemos un comodín `{id}`, se nos permite tener un argumento `$id` aquí abajo. `dd($id)` para ver cómo vamos hasta ahora.

[[[ code('8d6cc93142') ]]]

Pruébalo. Dirígete a `/starships/2`. ¡Estupendo!

Ahora vamos a hacer algo familiar: tomar este `$id` y consultar nuestra base de datos imaginaria en busca del `Starship` coincidente. La clave para hacerlo es nuestro servicio `StarshipRepository`y su útil método `find()`.

En el controlador, añade un argumento `StarshipRepository $repository`... y luego di que`$ship` es igual a `$repository->find($id)`. Y si no es `$ship`, activa una página 404 con los lanzamientos `$this->createNotFoundException()` y `starship not found`.

¡Genial! En la parte inferior, en lugar de devolver JSON, renderiza una plantilla: devuelve`$this->render()` y sigue la convención de nomenclatura estándar para plantillas:`starship/show.html.twig`. Pasa esta variable: `$ship`.

[[[ code('0988a1b906') ]]]

## Crear la plantilla

Controlador, ¡comprobado! A continuación, en el directorio `templates/`, podríamos crear un directorio`starship/` y `show.html.twig` dentro. Pero quiero mostrarte un atajo del plugin Symfony PhpStorm. Haz clic en el nombre de la plantilla, pulsa Alt+Enter y... ¡fíjate! En la parte superior pone "Twig: Crear plantilla". Confirma la ruta y ¡boom! ¡Ya tenemos nuestra nueva plantilla! Está... escondida por aquí. Ahí está:`starship/show.html.twig`.

Prácticamente todas las plantillas empiezan igual: `{% extend 'base.html.twig' %}`... ¡luego anula algunos bloques! Anula `title`... y esta vez, utiliza la variable`ship`: `ship.name`. Termina con `endblock`.

Y para el contenido principal, añade el bloque `body`... `endblock` y pon un `h1`dentro. Vuelve a imprimir `ship.name` y... Pegaré una tabla con algo de información.

[[[ code('0988a1b906') ]]]

Aquí no hay nada especial: sólo estamos imprimiendo datos básicos del barco.

Cuando probamos la página... ¡está viva!

## Enlazar entre páginas

Siguiente pregunta: desde la página de inicio, ¿cómo podríamos añadir un enlace a la nueva página de presentación de barcos? La opción más obvia es codificar la URL, como `/starships/` y luego el id. Pero hay una forma mejor. En lugar de eso, vamos a decirle a Symfony:

> Oye, quiero generar una URL para esta ruta.

La ventaja es que si más adelante decidimos cambiar la URL de esta ruta, todos los enlaces a ella se actualizarán automáticamente.

Déjame que te lo muestre. Busca tu terminal y ejecuta:

```terminal
php bin/console debug:router
```

Aún no lo he mencionado, pero cada ruta tiene un nombre interno. Ahora mismo, están siendo autogeneradas por Symfony, lo cual está bien. Pero en cuanto quieras generar una URL a una ruta, debemos tomar el control de ese nombre para asegurarnos de que nunca cambie.

Busca la ruta show page y añade una clave `name`. Yo utilizaré `app_starship_show`.

[[[ code('9254226423') ]]]

El nombre podría ser cualquier cosa, pero ésta es la convención que yo sigo: `app` porque es una ruta que estoy creando en mi aplicación, y luego el nombre de la clase del controlador y el nombre del método.

Nombrar una ruta no cambia su funcionamiento. Pero sí nos permite generar una URL hacia ella. Abre `templates/main/homepage.html.twig`. Aquí abajo, convierte el nombre de la ruta en un enlace. Lo pondré en varias líneas y añadiré una etiqueta `a` con`href=""`. Para generar la URL, diré `{{ path() }}` y le pasaré el nombre de la ruta. Pondré la etiqueta de cierre en el otro lado.

Si nos detenemos ahora, esto no funcionará del todo. En la página de inicio:

> Faltan algunos parámetros obligatorios - `id` - para generar una URL para la ruta
> `app_starship_show`.

¡Eso tiene sentido! Le estamos diciendo a Symfony:

> ¡Hola! Quiero generar una URL para esta ruta.

Symfony entonces responde:

> Genial... excepto que esta ruta tiene un comodín. Así que... ¿qué quieres
> quieres que ponga en la URL para la parte `id`? 

Cuando hay un comodín en la ruta, tenemos que añadir un segundo argumento a `path()`con `{}`. Esta es la sintaxis de matriz asociativa de Twig. Es exactamente igual que JavaScript: es una lista de pares clave-valor. Pasa `id` ajustado a `myShip.id`.

[[[ code('9c72287b2c') ]]]

Y ahora... ¡ya está! Mira esa URL: `/starships/3`.

Muy bien, nuestro sitio sigue siendo feo. Es hora de empezar a arreglarlo incorporando Tailwind CSS y aprendiendo sobre el componente AssetMapper de Symfony.
