# Twig Parciales y para bucles

Acabamos de renovar el diseño de nuestro sitio... lo que significa que hemos actualizado nuestras plantillas para incluir elementos HTML con un montón de clases de Tailwind. ¿El resultado? Un sitio agradable a la vista.

En algunas partes de las plantillas, las cosas siguen siendo dinámicas: tenemos código Twig para imprimir el capitán y la clase. Pero en otras partes, todo está codificado. Y... esto es bastante típico: un desarrollador frontend puede codificar el sitio en HTML y Tailwind... pero dejarte a ti que lo hagas dinámico y le des vida.

## Organizar en una Plantilla Parcial

En la parte superior de `homepage.html.twig`, este largo elemento `<aside>` es la barra lateral. Está bien que este código viva en `homepage.html.twig`... ¡pero ocupa mucho espacio! ¿Y si queremos reutilizar esta barra lateral en otra página?

Una gran característica de Twig es la posibilidad de tomar "trozos" de HTML y aislarlos en sus propias plantillas para que puedas reutilizarlos. Se llaman parciales de plantilla... ya que contienen el código de sólo una parte de la página.

Copia este código, y en el directorio `main/` -aunque esto puede ir en cualquier sitio- añade un nuevo archivo llamado `_shipStatusAside.html.twig`. Pega dentro.

[[[ code('51aa8a0e14') ]]]

De vuelta en `homepage.html.twig`, borra eso, y luego inclúyelo con `{{` - para que diga algo de sintaxis - `include()` y el nombre de la plantilla:`main/_shipStatusAside.html.twig`.

[[[ code('4eea3185dd') ]]]

¡Pruébalo! Y... ¡no hay cambios! La declaración `include()` es sencilla:

> Renderiza esta plantilla y dale las mismas variables que yo tengo

Si te preguntas por qué he antepuesto un guión bajo a la plantilla... ¡no hay motivo! Es sólo una convención que me ayuda a saber que esta plantilla contiene sólo una parte de la página.

## Haciendo un bucle sobre las naves en Twig

En la plantilla de la página de inicio, podemos centrarnos en la lista de naves de abajo, que es esta zona. Ahora mismo, sólo hay una nave... y está codificada. Nuestra intención es listar todas las naves que estamos reparando actualmente. Y ya tenemos una variable `ships` que estamos utilizando en la parte inferior: es una matriz de objetos `Starship`.

Así que, por primera vez en Twig, ¡tenemos que hacer un bucle sobre una matriz! Para ello, eliminaré este comentario, y diré `{%` -así que la etiqueta hacer algo- y luego`for ship in ships`. `ships` es la variable de matriz que ya tenemos y `ship` es el nuevo nombre de la variable en el bucle que representa un único objeto`Starship`. En la parte inferior, añade `{% endfor %}`.

[[[ code('9fdb2495cc') ]]]

Y ya... cuando lo probamos, ¡obtenemos tres naves codificadas! ¡Eso es una mejora!

A continuación: es hora de un giro argumental que nos llevará a crear un enum PHP.
