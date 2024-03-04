# Herencia de plantillas Twig

¿Qué tal si añadimos un diseño a nuestra página, como una cabecera y un pie de página? Echa un vistazo al HTML de la página: es sólo el HTML de la plantilla. No hay nada especial en Twig para que un diseño base con un encabezado y un pie de página se envuelva automáticamente alrededor de nuestro contenido. Lo que tengas en tu plantilla es lo que obtendrás en la página.

Sin embargo, la receta Twig añadió un archivo de diseño base llamado `base.html.twig`. 

[[[ code('339b00d890') ]]]

Ahora es muy sencillo, pero aquí es donde añadiremos nuestra navegación superior, el pie de página y cualquier otra cosa que deba aparecer en cada página. La pregunta es: ¿cómo podemos hacer que nuestra plantilla utilice esto?

## Ampliando el diseño base

Con una función genial llamada herencia de plantillas. En `homepage.html.twig`, en la parte superior, escribe `{% extends` y luego el nombre de la plantilla base: `base.html.twig`. Y fíjate: esta es la etiqueta hacer algo. No estamos imprimiendo esta plantilla, le estamos diciendo a Twig que queremos ampliarla.

[[[ code('432c044dc7') ]]]

Si no hacemos nada más y actualizamos, obtendremos un error:

> una plantilla que extiende otra no puede incluir contenido fuera de los bloques Twig.

Hmmm. Cuando extiendes una plantilla, le dices a Twig que quieres renderizar tu plantilla dentro de ese diseño base. Pero... Twig no tiene ni idea de dónde debe ir nuestro contenido. ¿Debería coger el HTML de nuestra página de inicio y ponerlo aquí abajo? ¿O aquí arriba? ¿O justo ahí? No lo sabe Así que lanza ese error.

La forma de decírselo es mediante estos bloques. Los bloques son huecos en los que una plantilla hija puede poner contenido. Y te habrás fijado en un bloque llamado `body`... que es exactamente donde queremos que vaya nuestro contenido. Para ponerlo ahí, rodea todo el contenido con un `{% block body %}`... y en la parte inferior, `{% endblock %}`.

[[[ code('463b23afe2') ]]]

Y ahora... ¡está vivo! No parece muy diferente, pero estamos dentro del diseño base.

Esto se llama herencia de plantillas porque funciona exactamente igual que la herencia de clases PHP. Imagina que tienes una clase `Homepage` que extiende a una clase `Base`. Esa clase`Base` tiene un método `body()`, y nosotros anulamos ese método `body()` en la clase`Homepage`. Es el mismo concepto en Twig.

## Reemplazar el título de la página

Y estos nombres de bloques -como `javascripts`, `stylesheets` y `body` - no son nombres especiales... y no están registrados en ninguna parte. Siéntete libre de crear nuevos bloques como quieras y cuando quieras. Por ejemplo, supongamos que queremos cambiar el `title` de la página desde una plantilla hija. En este caso, la receta ya nos ha proporcionado un bloque llamado`title` para hacerlo. Y este bloque tiene contenido por defecto... por eso ya vemos `Welcome` en la pestaña del navegador. Anulemos esto en nuestra plantilla.

[[[ code('9cd48b3d9f') ]]]

En cualquier lugar fuera del bloque `body`, di `{% block title %}`, escribe algo, y luego`{% endblock %}`.

[[[ code('6c92b85954') ]]]

## Sustituir frente a añadir el bloque padre

Y ahora, ¡ya está! ¡Nuevo título! Y fíjate en que cuando anulamos un bloque, lo anulamos por completo. Ya no vemos la palabra `Welcome`. Ocasionalmente, puede que quieras añadir al bloque padre en lugar de sustituirlo. Puedes hacerlo diciendo`{{ parent() }}`.

¡Esto está muy bien! La función `parent()` coge el contenido del bloque `title` de la plantilla padre. Luego utilizamos `{{` para imprimirlo. Esta vez vemos la bienvenida y luego nuestro título.

Pero como en realidad no queremos eso, lo eliminaré.

Comprobación de estado: estamos devolviendo HTML y tenemos un diseño base. Sí, nuestro sitio sigue siendo horriblemente feo, pero lo arreglaremos dentro de un rato.

A continuación, vamos a ejecutar un comando y acceder al instante a algunas de las herramientas de depuración más potentes de la web.
