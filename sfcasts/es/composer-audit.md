# Auditoría de Composer y actualizaciones de seguridad

¡Una última cosa! Un tema extra: las actualizaciones de seguridad.
Esto no tiene por qué estar relacionado con la actualización a Symfony 8, pero es un
tema importante, ahora más que nunca.

Hace poco, el ecosistema de Symfony [se sometió a una gran revisión de seguridad](https://symfony.com/blog/claude-mythos-audited-symfony-and-found-19-vulnerabilities)
que sacó a la luz muchas vulnerabilidades en varios paquetes. ¿El motivo? La IA ha
cambiado las reglas del juego.

Las modernas herramientas de auditoría asistidas por IA pueden ayudar a los investigadores de seguridad a analizar
grandes bases de código e identificar posibles vulnerabilidades mucho más rápido que
antes. Esto está dando lugar a más descubrimientos, más revelaciones y,
en última instancia, más correcciones.

Y eso es algo bueno.

Encontrar estas vulnerabilidades no significa que Symfony sea cada vez menos
seguro. Más bien al contrario. Unas herramientas mejores permiten que los problemas
se identifiquen y se solucionen más rápido.

Como desarrolladores, eso significa que es más importante que nunca estar al día de
las actualizaciones de las dependencias y prestar atención cuando se
publican avisos de seguridad.

Por suerte, Composer tiene una herramienta integrada que lo hace súper fácil.

## Composer Audit

Para verlo en acción, en tu terminal, ejecuta:

```terminal
symfony composer audit
```

Esto comprueba los paquetes que tienes instalados actualmente en tu proyecto con respecto a los
avisos de seguridad conocidos.

Y… ¡parece que tengo unas cuantas! Este resultado se ve bastante mal en esta pantalla tan pequeña,
así que volveré a ejecutar el comando con `--format=plain`.

Para cada vulnerabilidad, Composer muestra el paquete afectado, un
nivel de gravedad y enlaces donde podemos obtener más información sobre el problema.

La mayoría de las veces, la solución es sencilla: actualiza tus dependencias.

A menudo, Composer actualizará el paquete vulnerable a una versión parcheada
y el problema desaparece.

Pero no siempre.

A veces, la corrección solo está disponible en una versión principal más reciente a la que aún no
estás preparado para actualizar. Otras veces, la actualización introduce cambios que
provocan fallos en tu aplicación.

Si te encuentras en cualquiera de estos casos, es importante que entiendas
la vulnerabilidad y el riesgo que supone para tu aplicación.

## Sigue el aviso

Empecemos por la última vulnerabilidad de mi lista.

Cada aviso incluye una URL con más información. Si abrimos esta,
nos redirige a una entrada de blog en la web de Symfony con todos los detalles.

Normalmente, este es el mejor sitio para empezar nuestra investigación.

La entrada explica:
* en qué consiste la vulnerabilidad;
* qué versiones se ven afectadas;
* en qué circunstancias se puede aprovechar;
* y qué versiones incluyen la corrección.

No todos los paquetes utilizan una entrada de blog para compartir los detalles de la vulnerabilidad. Volviendo a nuestra terminal,
podemos seguir el enlace del ID del aviso, que nos lleva al aviso en packagist.org. Aquí se muestran
los detalles relacionados con el paquete y, si se creó a través de GitHub, el enlace al aviso de GitHub.

Es este enlace de GHSA de aquí. Si lo sigues, verás una página de vulnerabilidad estandarizada.
Aquí tienes el nombre del paquete, las versiones afectadas, las versiones parcheadas, la gravedad y una
descripción del problema y cómo lo resuelve la corrección.

Para ver todos los avisos de seguridad publicados para un paquete de GitHub, desde la página de su repositorio, haz clic en
«Seguridad y calidad». Aquí está la lista completa. Al hacer clic en uno de ellos, te lleva a su página de aviso.

## ¿Qué es un CVE?

Seguramente te habrás dado cuenta de que cada vulnerabilidad tiene un identificador CVE. ¿Qué es eso?

CVE son las siglas de «Common Vulnerabilities and Exposures» (Vulnerabilidades y exposiciones comunes).

Es una base de datos pública de vulnerabilidades de seguridad en la que a cada problema se le
asigna un identificador único. El objetivo es sencillo: ofrecer a todo el mundo una forma común
de referirse a la misma vulnerabilidad.

Y esto no es específico de PHP, Composer ni siquiera del software de código abierto.
Los CVE se utilizan en todo el sector del software. Los sistemas operativos,
los navegadores web, las bases de datos, las plataformas en la nube, los dispositivos de hardware y las
bibliotecas de código abierto pueden tener asignados CVE.

Por ejemplo, si alguien menciona `CVE-2026-46634`, los desarrolladores, los
investigadores de seguridad, los responsables de paquetes y las herramientas de seguridad saben que
están hablando exactamente del mismo problema.

Los CVE los asignan unas organizaciones llamadas «Autoridades de Numeración de CVE»
o CNA.

GitHub es una CNA, lo cual es una gran noticia para los mantenedores de código abierto porque
pueden crear avisos de seguridad y realizar peticiones para obtener identificadores CVE directamente
a través de las herramientas de GitHub.

Puedes consultar los registros oficiales de CVE en [cve.org](https://www.cve.org/).

En cuanto a las vulnerabilidades creadas a través de GitHub, me parece que su interfaz es un poco
más fácil de usar que la de cve.org, pero es bueno conocer ambos sitios.

## Ignorar una vulnerabilidad

Si no puedes actualizar a una versión parcheada de inmediato, y entiendes
la vulnerabilidad y el riesgo que supone, tienes la opción de ignorarla
con Composer.

Copia el ID del aviso (el identificador CVE también sirve) y abre tu archivo ` `composer.json` `.

Bajo la clave « `config` », añade una sección « `audit` » con una clave « `ignore` ». Usa el ID que has copiado
como clave... y, como valor, indica el motivo por el que la ignoras. Esto es importante para que
tú y tus compañeros de equipo entendáis en el futuro por qué aceptasteis el riesgo. A mí también me gusta
incluir cuándo podremos eliminarla.

[[[ code('92b9949f7a') ]]]

Ahora, si ejecuto:

```terminal
symfony composer audit
```

La vulnerabilidad no ha desaparecido. En cambio, se ha trasladado a una
sección de «Avisos ignorados», justo al principio de la lista. Aquí también se muestra el motivo.

Es importante que no la ocultemos por completo. Simplemente debe quedar constancia de que
la hemos revisado, entendemos el riesgo y, de momento, hemos decidido no
abordarla.

Por supuesto, ignorar una vulnerabilidad debería ser la excepción, no la
regla. Siempre que sea posible, actualizar a una versión parcheada es la opción
más segura.

Vamos a eliminar esta configuración de «ignorar» y a solucionar el problema como es debido.

## Aplicar las correcciones

Actualiza nuestras dependencias con:

```terminal
symfony composer update
```

> No se han encontrado avisos de vulnerabilidades de seguridad.

Genial, parece que ha funcionado. Comprobémoslo ejecutando de nuevo el comando de auditoría.

```terminal-silent
symfony composer audit
```

¡Genial! Ya no hay vulnerabilidades.

## Automatizar las auditorías de seguridad

Por último, no te fíes de tu memoria para ejecutar las auditorías manualmente. Automatízalas.

Si usas GitHub para alojar tu código, una solución genial es una acción programada de GitHub que
ejecute `composer audit` de forma periódica. Al ejecutar ese comando, la acción fallará
si se encuentran vulnerabilidades, lo que os alertará a ti y a tu equipo del problema.

¡Te voy a enseñar la que yo uso! Crea un nuevo archivo: `.github/workflows/composer-audit.yaml`. Voy a pegar
la definición, pero también la puedes encontrar en el script de abajo:

[[[ code('ea4efff6ad') ]]]

Repasemos este flujo de trabajo. En primer lugar, se activa según una programación: todos los lunes a mediodía (UTC).

***NOTE
Ten en cuenta que las tareas programadas se ejecutan en tu rama predeterminada.
***

Vale, pasemos a la tarea en sí. Primero, hacemos un checkout del código. Luego configuramos PHP y Composer usando una acción muy común.
Por último, ejecutamos `composer audit`. Fíjate en este indicador « `--locked` ». Esto le indica a Composer que compruebe las versiones instaladas
en `composer.lock` con respecto a los avisos de seguridad. Así nos ahorramos tener que ejecutar `composer install`, lo que nos ahorra unos
minutos de acción. Además, significa que no tenemos que especificar la versión de PHP en el paso anterior, lo que simplifica el proceso.

Esta tarea fallará si se encuentra alguna vulnerabilidad que no esté ignorada.

Si tú y tu equipo usáis Slack, esta sección comentada es un ejemplo de cómo puedes publicar una alerta allí
en `failure()`.

***TIP
Un último consejo: cuando puedas, crea pull requests específicas para las actualizaciones de seguridad. Mantenerlas separadas de
el resto del trabajo facilita las revisiones y ayuda a que las correcciones de seguridad se fusionen rápidamente.
***

Y ahí lo tienes.

Actualizar tu aplicación es solo una parte de la historia. El mantenimiento continuo
implica estar atento a tus dependencias, entender los avisos de seguridad
y aplicar las actualizaciones cuando sea necesario.

Por suerte, Composer nos ofrece herramientas excelentes para facilitar mucho ese proceso.

***SEEALSO
Para más información, echa un vistazo a nuestra entrada del blog sobre
[avisos de seguridad de Composer](https://symfonycasts.com/blog/composer-security-advisory).
***

Hasta la próxima,
¡Que disfrutes programando sin vulnerabilidades!