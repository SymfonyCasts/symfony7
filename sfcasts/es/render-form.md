# Renderizar el formulario

En el último capítulo, nos sumergimos en el componente Formulario de Symfony, construimos nuestro primer tipo de Formulario y creamos un objeto Formulario a partir del tipo en el controlador.

Ahora, vamos a pasar a la parte divertida: darle vida al formulario. El componente Formulario de Symfony incluye un montón de funciones de ayuda que facilitan la renderización del formulario. Abre tu plantilla y cambia el volcado por: `{{ form(form) }}`

Ahora, vuelve a tu navegador y actualiza la página. Uhh... bien, tenemos un formulario... pero es difícil ver los campos. Están todos ahí, sólo que totalmente reseteados por Tailwind. Por defecto, el CSS de Tailwind elimina todo el estilo de los elementos del formulario para que tengas una pizarra limpia con la que trabajar.

## Comprender los métodos de envío de formularios

Si abres tu inspector, notarás algo interesante. El formulario se envía mediante el método POST. Symfony utiliza por defecto el método POST para los formularios, aunque puedes anularlo. Más adelante veremos cómo enviar un formulario mediante GET.

No hay atributo `action`. Esto significa que el formulario se envía a la misma URL, lo que es increíblemente cómodo cuando necesitas utilizar el mismo formulario en diferentes páginas que se envían a varios lugares. Claro que podrías especificar una acción explícitamente, pero la mayoría de las veces no es necesario.

## Estilo con Tailwind CSS

Ahora al menos tenemos que hacer visibles los campos de nuestro formulario. Tailwind CSS tiene un plugin Forms que proporciona un estilo básico para los elementos del formulario. De vuelta en PhpStorm, abre`assets/styles/app.css`, y en la parte superior, añade`@plugin "@tailwindcss/forms";`

No olvides el punto y coma al final.

Actualiza tu navegador y notarás una ligera mejora. Hemos pasado de campos invisibles a un formulario con aspecto de los 90. Al menos es un comienzo...

Vamos a añadir un pequeño detalle para que nuestros campos de formulario se mezclen mejor con el fondo. En el archivo CSS, añade esta parte:

```css
input, textarea, select {
    background-color: inherit;
}
```

Actualizar... y... ¡Ya se ve un poco mejor!

## Añadir un botón de envío

A nuestro formulario le falta el elemento más importante: un botón de envío. ¿Cómo añadimos uno? 
Hay dos formas de hacerlo. Hay un campo `SubmitType` que podemos añadir a nuestra clase de tipo de formulario personalizado. La alternativa es añadir el botón manualmente en la plantilla Twig. Empezaremos con el método manual, pero no te preocupes, ya veremos cómo añadir un botón al tipo de formulario más adelante.

De vuelta en la plantilla Twig, debajo del formulario, añade: 
`<button>Create</button>`, `type="submit"`. Añadiré algunas clases CSS de Tailwind para que quede más bonito. Puedes copiar/pegar esta larga lista de clases CSS del script de abajo.

Sin embargo, aquí tenemos un problema, este botón debe estar dentro de la etiqueta `form`; de lo contrario, no importa cuántas veces hagas clic en él, no hará nada. Así que pasaremos de renderizar todo el formulario a la vez a renderizarlo de una forma que nos dé más control.

Sustituye la función `form()` por `{{ form_start(form) }}`, añade `{{ form_end(form) }}`debajo de ella, y pon un `{{ form_widget(form) }}` especial entre ellas para renderizar todos los campos del formulario. Mueve nuestro botón de envío justo antes de `form_end()`.

## Una nota rápida sobre Turbo desactivado

Por cierto, en este proyecto he desactivado temporalmente Turbo Drive de forma global. Verás algo de código Turbo comentado en `assets/app.js`.

Asegúrate de tener también desactivado Turbo para que tu comportamiento coincida con lo que se ve en los vídeos. ¿Por qué? Porque con Turbo activado, la navegación por la página se realiza mediante peticiones AJAX. Y lo sé, parece una locura, ¡pero incluso los envíos de formularios se convierten en AJAX! Esto puede dificultar la depuración cuando aprendas cómo funcionan los formularios. Por ahora, las cargas de página completa mantienen las cosas simples y predecibles.

## Probar nuestro formulario

Por último, es hora de probar nuestro formulario. Rellénalo con algunos datos divertidos y pulsa el botón "Crear" ¿Funcionó?

Por ahora, no estamos haciendo nada con los datos enviados internamente: ni guardarlos, ni validarlos, ni redirigirlos. Lo verás cuando intentes recargar la página y tu navegador te pregunte si debe volver a enviar el formulario.

Pero no te preocupes, en el próximo vídeo veremos cómo procesar el formulario y almacenar el nuevo`StarshipPart` en la base de datos. ¡Permanece atento!
