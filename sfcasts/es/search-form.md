# Formularios sin clase de datos

Estamos casi en la línea de meta de nuestro viaje con Symfony Forms. Pero antes de terminar, vamos a sumergirnos en algo divertido y práctico. Si navegas a la página `/parts`, verás una simple entrada de búsqueda. Es un formulario HTML básico, y eso está muy bien. Pero he aquí una idea: ¿podemos recrear esto utilizando Symfony Forms? ¡Por supuesto!

## Crear un formulario sin una entidad

Cuando hablamos de Formularios Symfony, solemos vincularlos a una entidad o a una clase de datos. Sin embargo, esto no es inamovible. Los Formularios Symfony pueden funcionar solos, sin ningún mapeo de objetos entre bastidores. Todos los datos del formulario se almacenan en matrices PHP. Nuestro formulario de búsqueda es un gran ejemplo de esto.

Por supuesto, podríamos crear este formulario directamente en el controlador utilizando el constructor de formularios. Pero para mantener las cosas limpias y ordenadas, vamos a seguir con el tipo de formulario. Además, aprenderemos algunos trucos útiles por el camino.

Dirígete a tu terminal y ejecuta el siguiente comando:

```terminal
symfony console make:form
```

Nombra el formulario `PartSearchType`. Esta vez, cuando te pida una entidad o clase de datos, déjalo en blanco. A continuación, localiza el`PartSearchType` generado en el directorio `src/Form` y ábrelo.

Verás un campo marcador de posición llamado `field_name`:

[[[ code('3b2582c88d') ]]]

Cámbialo por `query` para que coincida con el nombre de la entrada de búsqueda heredada:

[[[ code('5bfda05670') ]]]

## Utilizar el formulario en el controlador

Ahora, dirígete a la acción `index()` en `src/Controller/PartController.php`. Al principio, crea un formulario con `$this->createForm()` pasando`PartSearchType::class` y guárdalo en una variable llamada `$searchForm`. Después, pásalo a la plantilla:

[[[ code('203f9dccd8') ]]]

En `templates/part/index.html.twig`, renderiza todo el formulario con`{{ form(searchForm) }}`:

[[[ code('8d0ad4e6a4') ]]]

Por ahora, conservemos el original para poder compararlos. Ya lo arreglaremos más adelante.

Después de actualizar tu navegador, verás dos entradas de búsqueda. La nueva tiene una etiqueta, mientras que la antigua no la tiene. Vamos a solucionar esto primero.

## Ocultar la etiqueta del campo de formulario

De vuelta en `PartSearchType`, para el campo `query`, pasa `null` para el tipo, y un array para las opciones. Dentro de ella, añade la opción `label`. Puedes establecerla en cualquier cadena que quieras que sea la etiqueta. O, simplemente, ponle `false`. Esto indica a Symfony que no muestre la etiqueta:

[[[ code('e7aaa1957d') ]]]

Ya que estamos aquí, pulamos un poco las cosas. Añade la opción `attr` para los atributos, y dentro de ella, añade `placeholder` ajustado a `Search...` para que coincida con el formulario heredado. En la siguiente línea, `class`. Coge las clases CSS del formulario original y pégalas aquí:

[[[ code('8d24961378') ]]]

Vuelve a tu navegador y actualiza la página. Ahora debería ser la viva imagen del campo de búsqueda original, menos el icono de búsqueda, del que nos ocuparemos más adelante.

Si intentas enviar el formulario ahora, verás que utiliza el método POST, que es el comportamiento por defecto. Sin embargo, nuestro formulario de búsqueda utiliza GET, que es más adecuado para una función de búsqueda.

## ¿Y ahora qué?

A continuación, cambiaremos el método del formulario de POST a GET y aprenderemos a gestionarlo correctamente en el controlador.
