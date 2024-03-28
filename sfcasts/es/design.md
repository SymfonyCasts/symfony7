# Métodos del modelo inteligente y dinamización del diseño

Añadir el `.value` al final del enum para imprimirlo funciona a las mil maravillas. Pero quiero mostrar otra solución más elegante.

## Añadir métodos de modelo inteligentes

En `Starship`, probablemente será habitual que queramos obtener el estado de la cadena de un `Starship`. Para facilitarlo, ¿por qué no añadir aquí un método abreviado llamado`getStatusString()`? Éste devolverá un `string`, y dentro, devolverá `$this->status->value`.

[[[ code('9cdffd8f58') ]]]

Gracias a esto, en la plantilla, podemos acortar a `ship.statusString`.

[[[ code('2cb8e165e8') ]]]

Ah, ¡y esto es más inteligencia Twig! ¡No hay ninguna propiedad llamada `statusString`en `Starship`! Pero a Twig no le importa. Ve que hay un método `getStatusString()`y lo llama.

Observa: cuando actualizamos, la página sigue funcionando. Me gusta mucho esta solución, así que la copiaré... y la repetiré aquí arriba para el atributo `alt`.

[[[ code('4252263063') ]]]

Y mientras arreglamos esto, en `show.html.twig`, imprimiremos el estado allí también. Así que haré ese mismo cambio... y luego cerraré esto.

[[[ code('c96437117d') ]]]

## Terminando nuestra Plantilla Dinámica

Bien: vamos a terminar de hacer dinámica nuestra plantilla de página de inicio: a partir de aquí todo es coser y cantar. Para el nombre del barco, `{{ ship.name }}`, para el capitán, `{{ ship.captain }}`. Y aquí abajo para la clase, `{{ ship.class }}`.

[[[ code('58d92b3fd5') ]]]

Ah, y rellenemos el enlace: `{{ path() }}` y luego el nombre de la ruta. Estamos enlazando con la página del espectáculo del barco, así que la ruta es `app_starship_show`. Y como esto tiene un comodín `id`, pasa `id` a `ship.id`.

[[[ code('c3ca2a3021') ]]]

Y ahora, ¡mucho mejor! Se ve bien y podemos hacer clic en estos enlaces.

## Rutas de imagen dinámicas

Pero... la imagen sigue rota. Antes, cuando copiamos las imágenes en nuestro directorio`assets/`, incluí tres archivos para los tres estados. Aquí arriba, estamos apuntando "más o menos" al estado en curso... pero ésta no es la forma correcta de referenciar imágenes en el directorio `assets/`. En su lugar, di `{{ asset() }}` y pasa la ruta relativa al directorio `assets/`, llamada ruta "lógica".

Si lo intentamos ahora... estamos más cerca. Pero la parte "en curso" tiene que ser dinámica en función del estado. Algo que podríamos intentar es la concatenación Twig: añadir `ship.status` a la cadena. Eso es posible, aunque es un poco feo.

En lugar de eso, volvamos a la solución que utilizamos hace un momento: hacer que todos los datos sobre nuestro `Starship` sean fácilmente accesibles... desde la clase `Starship`.

Esto es lo que quiero decir: añade un `public function getStatusImageFilename()` que devuelva una cadena. 

[[[ code('713d6d6740') ]]]

Vamos a hacer toda la lógica para crear el nombre de archivo aquí mismo. Pondré una función `match`.

Esto dice: comprueba `$this->status` y si es igual a `WAITING`, devuelve esta cadena. Si es igual a `IN_PROGRESS` devuelve esta cadena y así sucesivamente.

[[[ code('2878484585') ]]]

Y exactamente igual que antes, como tenemos un método `getStatusImageFilename()`, podemos, en Twig, hacer como si tuviéramos una propiedad `statusImageFilename`.

[[[ code('c3ca2a3021') ]]]

Y ahora, ¡ya lo tenemos!

## Últimos detalles para dinamizar el diseño

¡Últimos detalles! Rellenemos algunos enlaces que faltan, como este logotipo que debería ir a la página de inicio. Ahora mismo... no va a ninguna parte.

Recuerda que cuando queremos enlazar a una página, tenemos que asegurarnos de que esa ruta tiene un nombre. En `src/Controller/MainController.php`... nuestra página de inicio no tiene nombre. Vale, tiene un nombre autogenerado, pero no queremos confiar en eso. 

Añade `name:` ajustado a `app_homepage`. O puedes utilizar `app_main_homepage`.

[[[ code('5440bc2365') ]]]

Para enlazar el logo, en `base.html.twig`... aquí está... Utiliza `{{ path('app_homepage') }}`.

[[[ code('e54d14499e') ]]]

Cópialo y repítelo a continuación para otro enlace de inicio. 

[[[ code('2e5bb7042c') ]]]

Dejaremos estos otros enlaces para un futuro tutorial.

De vuelta al navegador, ¡haz clic en ese logotipo! Ya está. El último enlace que falta está en la página del programa. Este enlace "atrás" también debería ir a la página de inicio. Abre`show.html.twig`. Y arriba -ahí está- pegaré ese mismo enlace.

[[[ code('84e9aa66d3') ]]]

Ok equipo, ¡el diseño está hecho! ¡Enhorabuena! Regálate un té... o un café con leche... o un donut o un paseo por la naturaleza para celebrarlo. ¡Porque esto es enorme! Nuestro sitio parece y se siente real. Estoy encantada.

Ahora podemos centrarnos en los detalles más sutiles. Por ejemplo, cuando hacemos clic en este enlace, se supone que la barra lateral se colapsa. Para ello, quiero presentarte mi herramienta favorita para escribir JavaScript: Stimulus.
