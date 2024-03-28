# Turbo: Tu aplicación de una sola página

Cuando construyo una interfaz de usuario, quiero que sea bonita, interactiva y fluida. Personalmente, elijo no utilizar frameworks frontales como React o Vue o Next. Pero tú puedes... y no tienen nada de malo: son herramientas estupendas. Además, ¡construir una API en Symfony es genial!

Pero si quieres construir tu HTML en Twig -como a mí me encanta hacer-, ¡podemos tener una interfaz de usuario interactiva, receptiva y súper rica!

Una gran pieza de una interfaz elegante es eliminar las recargas de página completa. Ahora mismo, cuando hago clic, mira: es rápido, pero son recargas de página completa. Eso no ocurre si utilizas algo como React o Vue.

Para eliminarlas, vamos a utilizar otra biblioteca de la misma gente que hizo Stimulus, llamada Turbo. Turbo puede hacer muchas cosas, pero su función principal es eliminar los refrescos de página completa. Al igual que Stimulus, es una biblioteca de JavaScript. Y también como Stimulus, Symfony tiene un bundle que ayuda a integrarla.

## Instalación de Turbo

Busca tu terminal y ejecuta:

```terminal
composer require symfony/ux-turbo
```

Esta vez, la receta ha hecho dos cambios interesantes. Te los mostraré. El primero está en`importmap.php`: añadió el paquete JavaScript `@hotwired/turbo`. 

[[[ code('10866a256b') ]]]

El segundo cambio está en `assets/controllers.json`. Antes no hablamos de este archivo, pero lo añadió la receta StimulusBundle: es una forma de activar los controladores Stimulus que viven dentro de paquetes de terceros.

[[[ code('c1639fbd7f') ]]]

Así que el paquete PHP `symfony/ux-turbo` que acabamos de instalar tiene dentro un controlador JavaScript llamado `turbo-core`. Y como tenemos `enabled: true` aquí, significa que ese controlador está ahora registrado y disponible: es como si viviera en nuestro directorio `assets/controllers/`.

Ahora no vamos a utilizar este controlador directamente: no vamos a adjuntarlo a un elemento. Pero el hecho de que esté cargado y registrado en Stimulus es suficiente para activar Turbo en nuestro sitio.

## Se acabaron los refrescos de página completa

¿Qué diablos significa esto? Es como magia: refresca la página y ¡bam! ¡Las recargas de página completa desaparecen! Fíjate bien: cuando vuelva a hacer clic, no verás que se recarga ¡Boom! Es superrápido y todo ocurre a través de Ajax.

Así es como funciona. Cuando hacemos clic en este enlace, Turbo intercepta el clic y, en lugar de recargar toda la página, hace una llamada Ajax a esa página. Esa llamada Ajax devuelve el HTML completo de esa página y luego Turbo lo pone en esta página.

Esa pequeña cosa transforma nuestro proyecto en una aplicación de una sola página y marca una gran diferencia en la rapidez de nuestro sitio.

## Llamadas AJAX y la barra de herramientas de depuración web

Pero hay una cosa más. Actualizaré para que podamos verlo. Cada vez que haces una llamada Ajax en una aplicación Symfony - ya sea a través de Turbo o de cualquier otra forma - la Barra de Herramientas de Depuración Web lo nota. Míralo por aquí cuando haga clic. Ejecuta una lista de todas las llamadas Ajax realizadas en esta página. Y si queremos ver el perfil de cualquiera de esas peticiones Ajax, podemos hacer clic en el enlace.

Y sí... ahí lo tenemos. Aquí está la petición Ajax que se hizo para la página de inicio. Aunque con Turbo, ni siquiera necesitas recurrir a este truco porque, a medida que hacemos clic, toda esta barra es sustituida por la nueva Barra de Herramientas de Depuración Web para la página.

Ah, y escucha esto: en Turbo 8, que ya está a la venta, tu sitio parecerá aún más rápido, gracias a una nueva función llamada Clic Instantáneo. Con ella, cuando pasas el ratón por encima de un enlace, Turbo hace una llamada Ajax a esa página antes de que hagas clic. Entonces, cuando hagas clic, se cargará instantáneamente... o al menos tendrá una ventaja.

Turbo tiene muchas otras funciones, y utilizamos un montón de ellas en nuestro [Tutorial LAST Stack](https://symfonycasts.com/screencast/last-stack), donde construimos un frontend con popovers, modales, notificaciones tostadas y mucho más.

## Turbo requiere un buen JavaScript

Pero una nota sobre Turbo. Dado que las recargas de página completa ya no existen, tu JavaScript debe estar diseñado para gestionarlas. Mucho JavaScript espera recargas de página completas... y si de repente se añade HTML a la página sin una recarga, se rompe. La buena noticia es que si escribes tu JavaScript en Stimulus, todo irá bien.

Observa. No importa cómo lleguemos a la página de inicio, nuestro JavaScript para cerrar la barra lateral sigue funcionando.

Muy bien equipo, ¡estamos en la recta final! Antes de terminar, quiero hacer un último capítulo extra en el que jugaremos con la impresionante herramienta de generación de Symfony: MakerBundle.
