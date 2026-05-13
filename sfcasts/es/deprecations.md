# Rastrear y corregir las desapropiaciones

Volvamos a las deprecaciones, ya que es importante corregirlas. Como se mencionó anteriormente, antes de que puedas saltar con seguridad a Symfony 8.0, debes asegurarte de que estás ejecutando Symfony 7.4 libre de depreciaciones. Esto significa que no debería haber ninguna deprecación en toda tu aplicación.

Sabemos que podemos encontrar las deprecaciones en la barra de herramientas de depuración web, pero... esto sólo muestra las deprecaciones de la página actual... Aquí no hay ninguna... pero haz clic en esta nave estelar. 
Esta página sí tiene una desaprobación.

## Profundizando en el panel de obsoletos

Exploremos el panel de obsoletos con más detalle. Haz clic en el icono de la barra de herramientas para abrirlo. Esta desaprobación parece provenir de Doctrine, algo sobre el acceso a valores de campo sin procesar... Pero ¡mira esto! ¡Un enlace a un pull request justo en el mensaje! Muy útil, ¡me encanta! Copia el enlace y pégalo en tu navegador...

Este es el PR de Doctrine que modificó algunos comportamientos y provocó esta desaprobación. Parece que soluciona algunos problemas de memoria. Hay una gran discusión para ayudarnos a tener más contexto si queremos. Pero, ¿cómo lo arreglamos? En la descripción del PR, hay una sección de ruta de migración. El nuevo comportamiento es opcional, y puede activarse pasando `accessRawFieldValues: true`al constructor `Criteria`.

Vale... ¿cómo encontramos dónde necesita nuestra aplicación esta corrección? ¡El panel de desaprobación al rescate!

Haz clic en "mostrar seguimiento" debajo del mensaje de desaprobación. Este es el seguimiento de cómo tu aplicación ha llegado a esta desaprobación. Puedes leerlo de arriba abajo e ignorar las líneas que no están en tu directorio `src`. La primera línea de nuestro directorio `src` es probablemente la que está provocando la eliminación. En este caso, es la línea 23 de `StarshipPartRepository`. Incluso podemos ampliarla para ver el código real que está provocando la eliminación. ¡Genial!

También en este panel está el enlace "mostrar contexto". Muestra la excepción real lanzada, pero en su mayor parte muestra la misma información. El mensaje... y el seguimiento de la pila. Normalmente, sólo utilizo el enlace de seguimiento de la pila.

Así que... para encontrar todas las imprecisiones con la barra de herramientas de depuración web, tendríamos que ir localmente a cada página, a cada envío de formulario, a cada ruta de la API, etcétera. Eso no es muy eficiente. Entonces, ¿cuál es la mejor forma de encontrar las desaprobaciones?

Hay dos formas alternativas principales. La primera es tu conjunto de pruebas. PHPUnit puede configurarse para mostrar las depreciaciones, pero... a menos que tengas una cobertura de pruebas del 100%, esto no lo detectará todo. Vaya, ni siquiera tengo pruebas en esta aplicación...

## Registrar las imprecisiones

La última forma es un método infalible. Despliegas tu aplicación Symfony 7.4 en producción durante un tiempo, unas semanas o un mes. Durante ese tiempo, registra todas las depreciaciones que se produzcan en producción. Recuerda que las depreciaciones no son errores, por lo que no romperán tu aplicación. Cuando se produzcan, o al final del periodo de tiempo, revisa esos registros y corrige las depreciaciones. Haz este ciclo unas cuantas veces hasta que no tengas más depreciaciones en producción. Ahora estás listo para actualizar a Symfony 8.0 de forma segura.

Veamos cómo funciona el registro y cómo se puede mejorar. Abre `config/packages/monolog.yaml`. Desplázate hasta el gestor `deprecation` en la configuración de producción. Este gestor sólo escribe las deprecaciones en el flujo de error estándar. Por supuesto, puedes personalizarlo a tu gusto. Mi favorito es llenar de ellas el canal de Slack de mi equipo

Vamos a añadir este controlador a nuestro entorno de desarrollo para verlo en acción. Copia el manejador `deprecation` y pégalo en la sección `when@dev`... No quiero enviar esto a error estándar aquí, así que copia la ruta del manejador anterior y pégala aquí. Sufija el archivo con `deprecations.json`: 

[[[ code('53ecd5f082') ]]]

Esto ya está usando el formateador JSON y usar esa extensión me ayudará a hacerlo bonito en PhpStorm.

De vuelta en nuestra aplicación, actualiza la página que provoca la depreciación. Ahora, abre `var/log`... Hmm, no veo el archivo. Quizás PhpStorm aún no lo ha cogido. Lo recargaré desde el disco... ¡y ahí está!

`dev.deprecations.json`. Ábrelo. Daré formato al código para que sea más fácil de leer.

## Incluir trazas de pila en los registros

Vemos el mismo mensaje de desaprobación que vimos en el panel del perfilador... y un montón de cosas más... pero... falta la traza de pila. Por defecto, es difícil saber dónde se dispara la depreciación.

Por suerte, ¡podemos incluir el seguimiento de pila! Borraré este archivo de registro para que podamos empezar de cero.

De vuelta en `monolog.yaml`, en la configuración de nuestro gestor de desaprobación dev, añade `include_stacktraces: true`:

[[[ code('a5f5a65ecb') ]]]

Actualiza la página... comprueba el archivo de registro... formatéalo... ¡y ya está! El seguimiento de pila tiene el mismo aspecto que en el panel del perfilador. Y efectivamente, podemos ver que la línea 23 de `StarshipPartRepository` está activando la depreciación.

## Procesadores de registro

Hay una cosa que creo que todavía falta... Estaría bien saber qué URL lo desencadenó. Esto ayudaría a duplicar el problema localmente y confirmar la solución. Monolog tiene algo llamado procesadores que pueden añadir datos adicionales a las entradas del registro. Hay un montón de procesadores incorporados, uno para añadir detalles de autenticación, detalles de comandos de consola e información de depuración adicional. El que quiero activar es el `WebProcessor`. Éste añade los detalles de la petición, como la URL, el método HTTP y la IP.

Los procesadores incorporados no se registran como servicios por defecto, pero es superfácil hacerlo nosotros mismos. 

Volveré a borrar este archivo de registro.

Abre `config/services.yaml` y en la parte inferior, debajo de `services`, añade `monolog.processor.web` con la clase `WebProcessor`. Elige la del Puente Monolog, ya que está preparada para funcionar con Symfony:

[[[ code('8105a00509') ]]]

¡Ya está! Está autocableado y autoconfigurado, así que no necesitamos hacer nada más.

Vuelve a actualizar la página... comprueba el archivo de registro... formatéalo... ¡y ya está! Tenemos el mensaje, la traza de la pila, y abajo del todo, en `extra`, tenemos la URL, la IP y el método HTTP.

Sólo tienes que modificar nuestra configuración para producción en tus aplicaciones y desplegar. ¡Encontrar, duplicar y corregir depreciaciones será pan comido!

## Arreglar la depreciación

Bien, ¡ahora vamos a arreglar esta deprecación! Se activó en `StarshipPartRepository` en la línea 23, ¿verdad? Abre eso... y busca la línea 23... Recuerda que tenemos que pasar `true` al constructor `Criteria`. Este`Criteria::create()` no es el constructor real, sino que es un constructor con nombre. Cuando saltamos a ese método... efectivamente, tiene el parámetro `accessRawFieldValues`... pero está comentado. ¿Qué pasa con eso? Forma parte del proceso de desaprobación. No pueden añadir el nuevo parámetro sin más. Esta clase y este método no son definitivos, por lo que, para mantener la compatibilidad con versiones anteriores, tienen que conservar la firma actual, por lo que comentan el nuevo parámetro y activan la depreciación si no se pasa. Esto de `func_num_args()` es una forma de comprobar si se ha pasado el nuevo parámetro sin añadirlo realmente a la firma del método.

La próxima versión principal de este paquete tendrá el parámetro real en la firma del método, y se eliminará la forma antigua. Este es el último paso del ciclo de desaprobación.

Basta de charla, ¡arreglémoslo! De vuelta en `StarshipPartRepository`, pasa `true` a `Criteria::create()`:

[[[ code('2cd4b32bdd') ]]]

De vuelta al navegador. Cuando actualicemos, esta desaprobación debería haber desaparecido... ¡Y así es!

¡Por fin estamos listos para pasar a Symfony 8! ¡Eso a continuación!
