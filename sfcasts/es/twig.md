# Herencia con Twig

¿Ves cada una de estas filas de naves estelares de aquí? Muestran las propiedades genéricas de la nave estelar. 
Quiero hacerlas un poco más interesantes mostrando en estas filas las propiedades personalizadas de las naves estelares para nuestras subnavegaciones. Llamemos a esta fila "teaser". 

## Crear una Parcial Teaser

Lo primero que haremos será extraer el HTML de nuestro teaser en un parcial.

En nuestro directorio `templates`, crea un nuevo directorio `starship`y, a continuación, crea un archivo Twig llamado `teaser.html.twig`.

Luego, en la plantilla de nuestra página de inicio, busca el HTML del teaser, que es todo lo que hay dentro de esta sección "for ship in ships". Coge todo ese trozo y córtalo. A continuación, pégalo en nuestro nuevo archivo `teaser.html.twig`:

[[[ code('2f9dba8a2f') ]]]

De vuelta en `homepage.html.twig`, inclúyelo como parcial con:`{{ include('starship/teaser.html.twig') }}`. Para los parámetros, pasa`{ship: ship}`:

[[[ code('2c5de85302') ]]]

Realiza una comprobación rápida actualizando la aplicación para ver si todo sigue en orden. Perfecto. ¡Sin regresiones!

## Añadir propiedades al teaser

Ahora que tenemos el teaser en su propio parcial, vamos a animarlo con propiedades de los subbuques. Por ejemplo, quiero mostrar la capacidad de carga de los cargueros. Podríamos hacerlo con una sentencia if... como `if ship.type == 'freighter'`, pero eso se complicaría muy rápido. En su lugar, utilicemos la herencia de plantillas Twig. Herencia de plantillas Twig con herencia Doctrine, ¡me encanta!

Primero, en el directorio `templates/starship`, crea un nuevo directorio `teaser`. Éste será el hogar de todas nuestras plantillas teaser heredadas. Crea la primera, `freighter.html.twig`.

De vuelta en `teaser.html.twig`, necesitamos crear un bloque que pueda ser anulado por nuestras plantillas heredadas. Debajo del "llegado a", añade `{% block extra %}{% endblock %}`:

[[[ code('b021befd20') ]]]

Esto está vacío en la plantilla teaser genérica, pero va a permitir que las plantillas que la extiendan, lo anulen.

Salta a nuestra nueva plantilla `freighter`. Ups, tengo un error tipográfico en el nombre del archivo. Debería ser`.twig` con un `i`.

Primero, haz que amplíe `starship/teaser.html.twig`. Ahora, anula el bloque `extra` y, dentro, añade `<div>Cargo Capacity: {{ ship.cargoCapacity }}</div>`. Podríamos llamar aquí a `parent()` para mostrar el bloque padre, pero como está vacío, me lo saltaré por ahora:

[[[ code('f3df8490e6') ]]]

## Herencia dinámica de plantillas

¿Listo para ver algo de magia? En nuestra plantilla de página de inicio, incluiremos dinámicamente la plantilla teaser correcta en función del tipo de nave. Dentro de `include()`, rompe la cadena después de `teaser` y añade algunos `~`'s para concatenar. Dentro de ellos, añade `ship.type`:

[[[ code('24a6babc4a') ]]]

De vuelta en nuestra aplicación, actualiza y... ¡error! "No se puede encontrar la plantilla `starship/teaserfreighter.html.twig`". Uy, se me ha olvidado un `/` después de `teaser`. Lo arreglo y actualizo de nuevo...

Sigue habiendo un error, ¡pero diferente! "No se puede encontrar la plantilla `starship/teaser/scout.html.twig`". Ahh, sólo hemos creado un teaser para el `freighter`, pero también tenemos un `scout` y un `mining_freighter`.

Para que esto funcione, tenemos que crear un teaser para cada tipo de nave. Pero, ¿y si no quiero tener ese requisito? Me gustaría simplemente recurrir al teaser genérico si no existe uno específico. ¡No hay problema!

## Teaser alternativo

En `homepage.html.twig`, envuelve el nombre de la plantilla en la función `include()` en una matriz. Cuando pases una matriz a `include()`, Twig intentará encontrar cada plantilla en la matriz. Utilizará la primera que encuentre. Así, para el segundo elemento de la matriz, utiliza nuestro teaser genérico: `starship/teaser.html.twig`:

[[[ code('5b10e58b32') ]]]

Actualiza nuestra aplicación... ¡y ya está! ¡Nuestros cargueros muestran ahora su capacidad de carga! Y a continuación, los exploradores y los cargueros mineros utilizan el teaser genérico.

## Añadir una plantilla de carguero minero

Pasemos a otro nivel y añadamos un teaser para el `mining_freighter`.

En `templates/starship/teaser`, crea un nuevo archivo Twig: `mining_freighter.html.twig`. Al igual que nuestra entidad `MiningFreighter` extiende `Freighter`, esta plantilla extenderá el teaser `freighter.html.twig`.

Añade el bloque `extra`. Esta vez, renderiza el bloque `parent()` para incluir la capacidad de carga del teaser `freighter`. A continuación, añade `<div>Laser Power: {{ ship.laserPower }}</div>`:

[[[ code('64de479483') ]]]

Gracias a nuestra lógica de herencia dinámica de plantillas, esto es todo lo que deberíamos hacer. Actualiza la aplicación... desplázate hasta nuestros cargueros mineros... ¡y genial, muestran tanto la capacidad de carga como la potencia láser!

Creo que esto es bastante elegante, ¿y tú?

A continuación veremos cómo funcionan conjuntamente la Herencia de Doctrine y las rutas
