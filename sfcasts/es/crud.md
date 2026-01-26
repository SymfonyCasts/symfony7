# Adoptar las operaciones CRUD de Entity

¡Hola, compañeros desarrolladores! Hoy quiero hablaros de una de mis herramientas favoritas para aumentar la productividad: el CRUD. No, no las cosas asquerosas, sino las operaciones de creación, lectura, actualización y eliminación que son la columna vertebral de la mayoría de las secciones administrativas. En lugar de perder un tiempo precioso escribiendo controladores, formularios y plantillas manualmente, Symfony tiene un ayudante de confianza que se encarga de todo lo tedioso por nosotros. Es hora de volver a recurrir a nuestro fiable aliado, MakerBundle.

Vayamos al terminal y ejecutemos:

```terminal
symfony console make:crud
```

En cuanto lo hagas, MakerBundle entrará en acción y te hará algunas preguntas. Para la entidad, vamos a utilizar `Starship`. Para el nombre del controlador, ya tenemos un `StarshipController`, pero como estamos tratando con cosas de administración, vamos a llamarlo `StarshipAdminController`. En cuanto a las pruebas unitarias de PHP, me las saltaré por ahora y -lo has adivinado- te toca hacer los deberes.

Pulsa enter y, ¡guau! Esta vez se han creado un montón de archivos. Un controlador, un tipo de formulario y un montón de plantillas para listar, mostrar, crear y editar entidades. Este es el tipo de boilerplate que preferirías no escribir a mano cada vez.

## Solucionar el problema de los Enums en la página de lista

Ahora, echemos un vistazo al interior del nuevo controlador. En PhpStorm, navegaré hasta `StarshipAdminController` en nuestro directorio `src/Controller/`. Lo primero que quiero cambiar es la ruta. `Maker` eligió una ruta razonable, pero me gusta la coherencia entre mis rutas de administración, así que cambiaré la ruta a `/admin/starship`. Perfecto

Abre la URL `/admin/starship` en el navegador. Ah, ¡el error! Dice

> No se ha podido convertir el objeto de clase `StarshipStatusEnum` en una cadena.

Problema clásico. Arreglémoslo abriendo la plantilla responsable de esta ruta: debería ser `starship_admin/index.html.twig`. Actualmente, intenta renderizar directamente el estado de la Nave Estelar, pero como no es una cadena.

Si abres la entidad `Starship` - verás que la propiedad de estado es un objeto de `StartshipStatusEnum`. Los Enums no son cadenas, son objetos, y necesitamos acceder explícitamente a sus valores.

Aunque MakerBundle ha hecho mucho trabajo por nosotros, parece que aún no comprende del todo los enums de PHP. Pero no temas, lo tenemos. Todo lo que tenemos que hacer es sustituir `starship.status` por `starship.status.value` en la plantilla.

Tras actualizar la página, tenemos una bonita lista de todas las naves estelares de nuestra base de datos, con algunas acciones útiles que podemos realizar sobre ellas, como mostrar y editar.

## Arreglar los Enums en la página Mostrar

Haz clic en el enlace Mostrar y aparecerá el mismo error. Pero ahora que somos avezados solucionadores de problemas, busquemos el controlador y la acción responsables y apliquemos la misma corrección.

WDT nos dice que la acción responsable de esta página es `show()`. Encuéntrala en `StarshipAdminController`, y actualiza el campo a `starship.status.value`.

Ahora actualiza la página y todo volverá a la normalidad. Ahora podemos ver los detalles individuales de la nave estelar y, lo que es más importante, editarlos o eliminarlos.

Si hago clic en el botón Eliminar - se activa un diálogo de confirmación de JavaScript. Un detalle pequeño pero significativo para evitar eliminaciones accidentales - una característica que aprecio mucho.

Voy a cancelar el clic en Editar - nos encontramos con otro error similar, sin embargo esta vez es lanzado desde el tema del formulario Symfony por defecto:

> Se ha lanzado una excepción durante la renderización de la plantilla
> ("El objeto de clase `StarshipStatusEnum` no se ha podido convertir a cadena")
> en `form_div_layout.html.twig.

## Arreglar las páginas del formulario

Busca la acción `edit()` en el controlador y abre la plantilla relacionada: archivo`edit.html.twig`. Ahora bien, este archivo no contiene lo que necesitamos, pero sí incluye otra plantilla: `_form.html.twig`. Continúa abriéndola.

Aquí es donde se renderiza el formulario. Si abres `starship_admin/new.html.twig`verás que estamos utilizando el mismo formulario tanto para acciones nuevas como para acciones de edición, y la única diferencia es la etiqueta del botón de envío, que pasamos como argumento a este `_form.html.twig`.

Pero no renderizamos manualmente ese campo de estado. El problema esta vez reside en el tipo de formulario, no en la plantilla Twig. MakerBundle creó `StarshipType`para nosotros - ábrelo en el directorio `src/Form/`. El campo de estado aquí es el culpable, parece que adivinar el tipo de campo de formulario Enum no funciona demasiado bien para Enums todavía.

No te preocupes, podemos especificar explícitamente el tipo. Pasa `EnumType::class` como 2º argumento y ve a actualizar la página. Otro error:

> Falta la opción requerida `class` para este EnumType.

Los mensajes de error de Symfony son bastante útiles, así que puede que ya tengas una idea del problema y de cómo solucionarlo. Pero vamos a confirmarlo. En tu terminal, ejecuta el comando ya conocido:

```terminal
symfony console debug:form EnumType
```

Te mostrará que la opción `class` es necesaria para este tipo de formulario, y debe apuntar a la clase concreta Enum. En nuestro caso, sería`StarshipStatusEnum`.

Añade una matriz vacía como 3er argumento, y dentro establece la opción `class` a`StarshipStatusEnum::class`.

Vuelve a actualizar la página. Ya está El formulario se muestra correctamente, y podemos editar los detalles y actualizar la entidad. Todo funciona como se esperaba. No hay mensajes flash, MakerBundle no los ha añadido, pero puedes añadirlos si quieres - tienes control total sobre ello.

En la página de la lista, desplázate hacia abajo y encontrarás un enlace "Crear nuevo". Al hacer clic en él, aparecerá el mismo formulario, pero sin datos rellenados de antemano: perfecto para crear una nave estelar nueva, ¿no? Esta es una de las mejores partes de la generación CRUD: un formulario que se reutiliza tanto para operaciones de creación como de actualización.

## Mejorar el estilo de las páginas CRUD

Vale, seamos sinceros. El código generado ahora funciona muy bien, pero visualmente no es el premio al diseño. Rápidamente le daré un poco de estilo y lo aceleraré un poco para ti, pero no te preocupes, puedes copiar/pegar el mismo código de los bloques de código que hay debajo del vídeo.

Yo pegaré algunas clases CSS de Tailwind en los botones del formulario. También, algo de HTML con clases CSS adecuadas para que las páginas se vean mejor en `edit.html.twig`,`index.html.twig`, `new.html.twig`, y finalmente `show.html.twig`.

Una vez que hayamos terminado, vuelve al navegador y actualiza la página: los nuevos estilos aportan un diseño más limpio y botones más intuitivos, incluido un botón "Crear nuevo" en la parte superior para facilitar el acceso. ¡Ahora tiene mucho mejor aspecto!

Lo importante es que sólo hemos hecho retoques de estilo, la funcionalidad principal sigue estando generada al 100% por el MakerBundle. Te da un muy buen comienzo, pero puedes tomar el control si quieres. Incluso puedes añadir mensajes flash que nos faltan aquí. ¡Depende de ti!

## Aplicando el tema del formulario Symfony Globalmente

El único detalle que queda - nuestro formulario Starship tiene un aspecto diferente del formulario StarshipPart. Podemos aplicar el tema de formulario predeterminado CSS de Tailwind en `_form.html.twig`, igual que hicimos con el formulario StarshipPart. Pero, ¡espera! Prefiero no repetir esto para cada formulario de mi aplicación.

En lugar de eso, vamos a aplicar ese tema globalmente para todos los formularios de tu aplicación. ¿Cómo? En el terminal, ejecuta:

```terminal
symfony console config:dump twig
```

Y busca la clave `form_themes` en la salida, en algún lugar al principio ¡Aquí está! Está configurado con el tema de formulario predeterminado de Symfony, pero podemos anularlo en la configuración.

Abriré el `new.html.twig` de StarshipPart y comentaré la etiqueta del tema del formulario. A continuación, copia el nombre de la plantilla del tema y ve al archivo`config/packages/twig.yaml`.

Debajo de la clave, añade la opción `form_themes`, y debajo, añade`- tailwind_2_layout.html.twig`. Ya está Ahora, todos los formularios utilizan automáticamente el tema CSS de Tailwind. Y sí, tienes razón, es una lista, así que puedes añadir más temas aquí, lo que es útil para aplicar algunos parches y personalizaciones al tema predeterminado del formulario. Pero por ahora, mantendré las cosas sencillas.

Vuelve al navegador para asegurarte de que el formulario se aplicó tanto para las páginas nuevas como para las de edición, y asegurémonos de que nuestro formulario StarshipPart también lo sigue utilizando incluso después de que comentáramos que el formulario se aplicaba en la plantilla - sí, se ve muy bien, no hay regresión.

Y lo mejor es que, aunque hayamos establecido ese tema globalmente, puedes anularlo aplicando otro tema directamente en la plantilla a un formulario específico, como hicimos al principio.

## Para terminar

En un abrir y cerrar de ojos, tenemos un controlador rico en operaciones CRUD y una base sólida que podemos personalizar a nuestro antojo. MakerBundle se encarga de las cosas aburridas, permitiéndonos centrarnos en cosas increíbles.

***SEEALSO
Si quieres un generador de administración aún más potente para tu aplicación Symfony, con operaciones CRUD ya implementadas y otras funciones geniales, echa un vistazo al [curso EasyAdminBundle](https://symfonycasts.com/screencast/easyadminbundle).
***

A continuación, crearemos un nuevo formulario y lo enviaremos mediante el método GET. Pero por ahora, ¡disfruta de tu CRUD recién generado y ve a añadir más naves estelares a tu flota!
