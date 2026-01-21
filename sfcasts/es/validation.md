# Validación del lado del cliente frente a validación del lado del servidor

Es hora de hablar de la validación de formularios. Porque, al igual que una nave estelar sin escudos, un formulario sin validación impresiona a la vista... pero está a sólo un asteroide del desastre total. Por lo tanto, preparemos nuestro formulario para cualquier asteroide que llegue.

Sabemos que cada `StarshipPart` debe tener al menos un `name` y un `price`. Sencillo, ¿verdad? Pero, ¿qué ocurre cuando accidentalmente (¿o a propósito?) enviamos un formulario vacío? 

## Validación HTML5

Curiosamente, el navegador salva el día mostrando un error de validación. Esto es la validación HTML5 en acción. Es una validación del lado del cliente que maneja completamente nuestro navegador. Es rápido, fácil de usar y bastante limpio. Sin embargo, ¡no es algo en lo que podamos confiar plenamente!

Esto se debe a varias razones. En primer lugar, no todos los navegadores lo soportan completamente. En segundo lugar, los usuarios pueden desactivarlo fácilmente. Además, como puedes ver en la práctica, sólo muestra un error cada vez en lugar de todos los errores a la vez, por lo que los usuarios pulsarán ese botón una y otra vez hasta que desaparezcan todos los errores. Y... los bots pueden saltárselo fácilmente, y no queremos que los bots se metan con nuestra base de datos de piezas de naves estelares, ¿verdad?

Si se omite la validación HTML5, nuestro formulario se envía al servidor. Veamos primero qué ocurre entonces. 

### Desactivar la validación HTML5

Podemos desactivar la validación HTML5 en la plantilla Twig, pero probemos otra forma más friki. Abre nuestro `StarshipPartType`, y para el botón, añade la opción `validate` establecida en `false`:

[[[ code('6a42815e18') ]]]

Ahora, actualiza la página e inspecciona el botón en el inspector HTML de tu navegador. Ajá, ha añadido un atributo HTML especial al botón:`formnovalidate="formnovalidate"`.

Y así es como cualquiera puede desactivar la validación HTML5 en nuestro sitio web, o en cualquier sitio web. Puedes probarlo tú mismo en el inspector de Chrome editando el código HTML del otro botón, guardarlo y hacer clic en él. 

Si pulso el botón de envío con ese atributo en un formulario vacío... Se produce un error en la base de datos:

> Se ha producido una excepción al ejecutar una consulta: Restricción de integridad
> violación: Campo de restricción NOT NULL: starship_part.name

Este error procede directamente de nuestro servidor, y eso es un problema porque acabamos de intentar a ciegas guardar datos no válidos en la base de datos. 

## Validación del lado del servidor con el componente validador de Symfony

La buena noticia es que podemos arreglar esto con la validación del lado del servidor. Symfony tiene un Componente Validador dedicado a este fin. Y funciona armoniosamente con el Componente Formulario. Vamos a instalarlo primero. Vuelve al terminal y ejecuta:

```terminal
symfony composer require validator
```

Empecemos poco a poco. Queremos ver un error de validación si el campo `name` está vacío. El componente viene con un montón de restricciones de validación incorporadas, que podemos adjuntar directamente a nuestros campos de formulario.

### Añadir una restricción de validación

Abre nuestra clase `StarshipPartType`.

Para el campo `name`, pasa `null` como segundo argumento, que es su valor por defecto, y un array vacío como tercero. Dentro, añade una clave `constraints`, y luego añade una nueva restricción `NotBlank`:

[[[ code('0ae3574af3') ]]]

Ahora, vuelve al navegador e intenta enviar de nuevo el formulario vacío. Hmm... seguimos teniendo el mismo error de base de datos, pero mira en la barra de herramientas de depuración web. Hay una nueva sección relativa a la validación.

Pasa el ratón por encima de este pequeño icono y verás "Llamadas al validador: 1" y "Número de violaciones: 1". Junto al icono del Validador, hay un icono de Formularios que nos muestra "Número de formularios: 1" y "Número de errores: 1".

Si haces clic en el icono del Validador, verás nuestro error `NotBlank` con su mensaje por defecto:

> Este valor no debe estar en blanco.

## Manejo de errores de formulario

¿Qué ocurre aquí? Aunque el formulario no sea válido, seguimos intentando guardar el objeto en la base de datos, que no es lo que queremos, ¿verdad? En el controlador admin, no deberíamos persistir ningún dato si el formulario no es válido. En su lugar, deberíamos simplemente volver a mostrar el formulario con los errores para que el usuario pueda corregirlos, y volver a enviar el formulario. Para ello, añade otra condición en la sentencia `if` después de comprobar que el formulario se ha enviado: `&& $form->isValid()`:

[[[ code('0c83947215') ]]]

Actualiza la página y vuelve a enviar el formulario vacío. ¡Ya está! Ya no hay excepción, y el error se muestra correctamente dentro del formulario.

### Personalizar los mensajes de error

¿Pero podemos personalizar el mensaje por defecto? ¡Claro que sí! Abre `StarshipPartType`, y establece el segundo argumento de `NotBlank` en... ¿qué te parece?

> ¡Cada parte debe tener un nombre!

[[[ code('a8da0ea8db') ]]]

Volvemos al navegador, enviamos de nuevo el formulario vacío y ahí tenemos nuestro mensaje personalizado. Pero espera, ¿por qué no está en rojo? 

## Solución de problemas con las clases CSS de Tailwind

Si abres el Inspector de HTML de Chrome y miras el error renderizado, verás que el texto de error tiene la clase `text-red-700` y el campo no válido tiene la clase `border-red-700`. Estas clases proceden del tema de formulario CSS incorporado de Tailwind que aplicamos anteriormente en este curso, y son clases CSS válidas de Tailwind, ¿por qué nos faltan estilos en ellas? ¿No tenemos instalado el `SymfonyCasts/tailwind-bundle`?

Aquí está el truco. Estas clases CSS se añaden dinámicamente y Tailwind no las recogió durante la compilación porque viven en un archivo de proveedor que Tailwind ignora por defecto:

[[[ code('94d453e8f5') ]]]

### Actualizar la configuración CSS de Tailwind

Abre `tailwind_2_layout.html.twig` y verás las clases CSS definidas allí. ¿Cómo podemos pedir a Tailwind que vigile también este archivo externo mientras compila nuestro CSS?

Como estamos en Tailwind 4, ya no existe `tailwind.config.js`. En su lugar, abre `app.css` en el directorio `assets/styles/`. Después de`@import` y `@plugin`, añade `@source`. Iré a copiar la ruta larga a la plantilla `tailwind_2_layout.html.twig` y la pegaré aquí:

[[[ code('f8ea8214a1') ]]]

Tenemos que ajustar la ruta anteponiéndole `./../../` para que parta del directorio raíz del proyecto. Y ya está.

Ten en cuenta que esto requiere Tailwind 4 y no funcionará en versiones anteriores, pero aquí deberíamos estar en la última. Puedes volver a comprobar la versión exacta en el archivo de configuración `config/packages/symfonycasts_tailwind.yaml` - aquí está, `v4.1.11`:

[[[ code('bde93edd3d') ]]]

Vale, es hora de probarlo - actualiza el navegador y... ¿el texto sigue sin aparecer en rojo? Hm, he copiado/pegado la ruta, así que debería ser la correcta. ¿Probablemente se deba a la caché del navegador? Probemos primero con "Vaciar caché y recargar fuerte" - sigue sin cambiar nada.

Bien, ponte el sombrero de depuración y ve a tu terminal. Podemos ejecutarlo manualmente:

```terminal
symfony console tailwind:build
```

Para ver si puede construir nuestro CSS final con éxito. Aha, hay un error:

> `@source` no puede tener cuerpo.

Comprobemos `app.css`. Ah, sí, es difícil de ver, pero olvidé añadir el punto y coma al final:

[[[ code('48d9916711') ]]]

En el terminal, ejecuta de nuevo el comando de compilación... y... ¡éxito! Esta vez no hay errores.

Vuelve al navegador... actualiza... ¡y ahí está! El mensaje de error ahora es rojo, ¡y también el borde alrededor del campo asociado!

¡Y ya está! Hemos aprendido a añadir validación de formularios real, del lado del servidor, en un proyecto Symfony. En el próximo capítulo, hablaremos más sobre las restricciones de validación. ¡Nos vemos allí!
