# Stimulus: Escribir JavaScript profesional

Sabemos cómo escribir HTML en nuestras plantillas. Y manejamos CSS con Tailwind. ¿Qué pasa con JavaScript? Bueno, como con CSS, hay un archivo `app.js`, y ya está incluido en la página. Así que puedes poner aquí el JavaScript que quieras.

Pero te recomiendo encarecidamente que utilices una pequeña, pero malvada, biblioteca JavaScript llamada Stimulus. Es una de mis cosas favoritas de Internet. Tomas una parte de tu HTML existente y lo conectas a un pequeño archivo JavaScript, llamado controlador. Esto te permite añadir un comportamiento: por ejemplo, cuando pulses este botón, se llamará al método `greet` del controlador.

¡Y eso es todo! Seguro que Stimulus tiene más funciones, pero ya entiendes el núcleo de su funcionamiento. A pesar de su simplicidad, nos permitirá construir cualquier funcionalidad JavaScript y de interfaz de usuario que necesitemos, de forma fiable y predecible. Así que vamos a instalarlo.

## Instalar Stimulus

Stimulus es una librería JavaScript, pero Symfony tiene un bundle que ayuda a integrarla. En tu terminal, si quieres ver lo que hace la receta, confirma tus cambios. Yo ya lo he hecho. Luego ejecuta:

```terminal
composer require symfony/stimulus-bundle
```

Cuando esto termine... la receta ha hecho algunos cambios. Veamos los más importantes. El primero está en `app.js`: nuestro archivo JavaScript principal. Ábrelo y ya está.

[[[ code('32152b65bd') ]]]

Añadió un `import` en la parte superior - `./bootstrap.js` - a un nuevo archivo que vive justo al lado de éste. 

[[[ code('4336a80e93') ]]]

El propósito de este archivo es iniciar el motor Stimulus. Además, en`importmap.php`, la receta añadió el paquete JavaScript `@hotwired/stimulus` junto con otro archivo que ayuda a arrancar Stimulus dentro de Symfony.

[[[ code('409fbd94b7') ]]]

Por último, la receta creó un directorio `assets/controllers/`. Aquí es donde vivirán nuestros controladores personalizados. ¡E incluía un controlador de demostración para que pudiéramos empezar! ¡Gracias!

[[[ code('217e917721') ]]]

Estos archivos de controlador tienen una importante convención de nombres. Como se llama `hello_controller.js`, para conectarlo con un elemento de la página, utilizaremos `data-controller="hello"`.

## Cómo funciona Stimulus

Así es como funciona. En cuanto Stimulus vea un elemento en la página con`data-controller="hello"`, instanciará una nueva instancia de este controlador y llamará al método `connect()`. Así, este controlador `hello` cambiará automática e instantáneamente el contenido del elemento al que está unido.

Y ya podemos verlo. Actualiza la página. Stimulus está ahora activo en nuestro sitio. Esto significa que está buscando elementos con `data-controller`. Hagamos algo salvaje: inspecciona los elementos de la página, busca cualquier elemento -como esta etiqueta de anclaje- y añade `data-controller="hello"`. Observa lo que ocurre cuando hago clic en desactivar para activar este cambio. ¡Pum! Stimulus ha visto ese elemento, ha instanciado nuestro controlador y ha llamado al método `connect()`. Y puedes hacer esto tantas veces como quieras en la página.

La cuestión es: no importa cómo llegue un elemento `data-controller` a tu página, Stimulus lo ve. Así que si hacemos una llamada Ajax que devuelva HTML y ponemos eso en la página... sí, Stimulus va a verlo y nuestro JavaScript va a funcionar. Ésa es la clave: cuando escribes JavaScript con Stimulus, tu JavaScript siempre funcionará, independientemente de cómo y cuándo se añada ese HTML a la página.

## Crear un controlador Stimulus que se pueda cerrar

Utilicemos Stimulus para activar nuestro botón de cierre. En el directorio `assets/controller/`, duplica `hello_controller.js` y crea uno nuevo llamado`closeable_controller.js`.

Borraré casi todo y me limitaré a lo más básico: importa`Controller` de Stimulus... y luego crea una clase que lo extienda.

[[[ code('c358612891') ]]]

Esto no hace nada, pero ya podemos adjuntarlo a un elemento de la página. Éste es el plan: vamos a adjuntar el controlador a todo el elemento `aside`. Luego, cuando pulsemos este botón, eliminaremos el elemento `aside`.

Ese elemento vive en `templates/main/_shipStatusAside.html.twig`. Para adjuntar el controlador, añade `data-controller="closeable"`. ¿Ves ese autocompletado? Proviene de un plugin de Stimulus para PhpStorm.

[[[ code('0f2eb5ecb1') ]]]

Si nos desplazamos y actualizamos, aún no ocurrirá nada: el botón de cerrar no funciona. Pero abre la consola de tu navegador. ¡Qué bien! Stimulus añade útiles mensajes de depuración: que se está iniciando y luego - lo que es importante - `closeable initialize`,`closeable connect`.

Esto significa que sí vio el elemento `data-controller` e inicializó ese controlador.

Así que volvamos a nuestro objetivo: cuando pulsemos este botón, queremos llamar a código dentro del controlador cerrable que elimine el `aside`. En `closeable_controller.js`, añade un nuevo método llamado, qué tal, `close()`. Dentro, digamos `this.element.remove()`.

[[[ code('33bc901668') ]]]

En Stimulus, `this.element` será siempre el elemento al que esté unido tu controlador. Por tanto, este elemento `aside`. Pero por lo demás, este código es JavaScript estándar: cada Elemento tiene un método `remove()`.

Para llamar al método `close()`, en el botón, añade `data-action=""` luego el nombre de nuestro controlador - `closeable` - un signo `#`, y el nombre del método: `close`.

[[[ code('c1c7a2a870') ]]]

## Animar el cierre

Ya está Hora de probar. ¡Clic! ¡Ya está! ¡Pero quiero que sea más elegante! Quiero que se anime al cerrarse en lugar de ser instantáneo. ¿Podemos hacerlo? ¡Claro que sí! Y no necesitamos mucho JavaScript... porque el CSS moderno es increíble.

Sobre el elemento `aside`, añade una nueva clase CSS -puede ir en cualquier sitio- llamada`transition-all`.

Es una clase Tailwind que activa las transiciones CSS. Esto significa que si cambian ciertas propiedades de estilo -como que la anchura se ponga de repente a 0- hará una transición de ese cambio, en lugar de cambiarlo instantáneamente.

También añade `overflow-hidden` para que, al reducirse la anchura, no cree una extraña barra de desplazamiento.

Si probamos esto ahora, se sigue cerrando instantáneamente. Eso es porque no hay nada que transicionar: no estamos cambiando la anchura... sólo eliminando el elemento.

Pero fíjate en esto. Inspecciona el elemento y busca el `aside`: aquí está. Cambia manualmente la anchura a 0. ¡Genial! ¡Vas pequeñito, grande, pequeñito, grande, pequeñito! El lado CSS de las cosas está funcionando.

De vuelta en nuestro controlador, en lugar de eliminar el elemento, tenemos que cambiar la anchura a cero, esperar a que termine la transición CSS y luego eliminar el elemento. Podemos hacer lo primero con `this.element.style.width = 0`.

[[[ code('c1c7a2a870') ]]]

La parte complicada es esperar a que termine la transición CSS antes de eliminar el elemento. Para ayudarte con eso, voy a pegar un método en la parte inferior de nuestro controlador.

[[[ code('b9a70df082') ]]]

Si no estás familiarizado, el signo `#` hace que éste sea un método privado en JavaScript: un pequeño detalle. Este código parece lujoso, pero tiene una función sencilla: pedir al elemento que nos diga cuándo han terminado todas sus animaciones CSS.

Gracias a eso, aquí arriba, podemos decir `await this.#waitForAnimation()`. Y siempre que utilices `await`, tienes que poner `async` en la función alrededor de esto. No entraré en detalles sobre `async`, pero eso no cambiará el funcionamiento de nuestro código.

[[[ code('81b776d135') ]]]

¡Comprobemos el resultado! Actualiza. Y... Me encanta.

A continuación, todo el mundo quiere una aplicación de página única, ¿verdad? Un sitio en el que no haya refrescos de página completa. Pero para construir una, ¿no necesitamos utilizar un framework JavaScript como React? ¡No! Vamos a transformar nuestra aplicación en una aplicación de una sola página en... unos 3 minutos con Turbo.
