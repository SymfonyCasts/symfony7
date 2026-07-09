# Activar la función «Recordarme»

Echemos un vistazo entre bastidores para entender cómo nuestro
usuario permanece conectado mientras navega de una página a otra. Como puedes ver,
cuando vamos de una página a otra, seguimos conectados, tal y como esperamos.
Pero, ¿cómo ocurre esa magia? El truco está en las sesiones de PHP, que almacenan
los datos del usuario actual.

Echa un vistazo a la página, ve a la pestaña «Aplicación» y busca las cookies de
nuestra web. PHPSESSID es nuestra cookie de sesión. Este valor es un identificador único
para nuestra sesión. Apunta a unos datos almacenados en nuestro servidor. El navegador
envía esta cookie al servidor con cada petición, lo que permite al servidor
recuperar los datos relacionados. Estos datos pueden ser de todo tipo, como mensajes flash
o algún tipo de configuración guardada del sitio. Y como estamos usando el autenticador `form_login`,
este almacena al usuario autenticado en la sesión.

Fíjate en que el valor de «Expiry» está establecido en «Session». Esto significa que la cookie se borra
cuando cerramos el navegador. Y si no hay cookie, no hay forma de encontrar nuestra sesión: nos hemos desconectado.

Pruébalo: borra la cookie (para simular que cierras el navegador)... y vuelve a cargar la página...
¡Ya no estamos conectados!

## Personalizar la cookie

Solo una nota rápida: puedes ajustar esta cookie a tu gusto. Para ver las opciones,
ejecuta lo siguiente en tu terminal:

```terminal
symfony console config:dump framework session
```

Recuerda que la sesión no es específica de seguridad, por eso se configura a
nivel del framework.

Aquí puedes ver que podemos cambiar el almacenamiento. Por defecto, utiliza el
sistema de archivos, que es el valor predeterminado de PHP. Pero tienes opciones como
Redis o una base de datos. Puedes configurar un montón de opciones relacionadas con las cookies, como
el nombre, la ruta, el dominio y mucho más. Los valores por defecto son adecuados para la mayoría de los casos,
pero las opciones están ahí por si necesitas ajustarlas.

## Función «Recordarme»

Una función muy popular en muchos sitios web es «recordarme», que garantiza que los usuarios
sigan conectados, incluso entre sesiones (al cerrar y abrir el
navegador). Vamos a activarla. En tu IDE, abre `config/packages/security.yaml`.
Debajo de nuestro firewall principal, añade `remember_me: ~`. La tilde significa: activa esta
función y usa la configuración por defecto.

Para ver la configuración predeterminada, en tu terminal, ejecuta:

```terminal
symfony console config:dump security firewalls
```

Desplázate hacia arriba hasta encontrar la sección «remember_me».

Los datos de la cookie consisten en el identificador de usuario, una fecha de caducidad y una firma
para garantizar que no se haya manipulado. Por defecto, utiliza nuestro secreto del kernel para generar
la firma.

La opción « `signature_properties` » es interesante. Son las propiedades del
usuario que, si cambian, invalidarán la cookie. Por defecto, solo usa `password`. ¿Te acuerdas de cuando hablamos antes de cerrar la sesión de los usuarios en diferentes dispositivos?
Siempre que la contraseña esté incluida en las propiedades de la firma, la función
de la que hablamos también cerrará la sesión de los usuarios recordados.

Puedes añadir aquí propiedades adicionales, como el correo electrónico o el nombre de usuario. Así, cuando cualquiera
de ellas cambie, la cookie se invalidará (y el usuario se desconectará).

Esta opción « `token_provider` » te permite personalizar el almacenamiento del token «recordarme».
Por defecto, utiliza un enfoque sin estado, por lo que no se almacena nada en el servidor. La cookie
contiene toda la información necesaria para iniciar sesión. Aquí puedes personalizar este
comportamiento con tu propio servicio, o usar el proveedor integrado `doctrine`, que guarda el token en una
tabla de la base de datos. Creo que la configuración por defecto es suficiente para la mayoría de los casos, pero está bien saber que tienes opciones.

A continuación están todas las opciones de las cookies. De nuevo, los valores por defecto son adecuados para la mayoría de los casos, pero
merece la pena mencionar el «lifetime». Es el tiempo que durará la cookie, en segundos, antes de que caduque.
El valor por defecto es de 1 año, así que quizá quieras cambiarlo a algo más corto, como 1 mes.

`always_remember_me` te permite recordar siempre al usuario, sin necesidad de esa casilla de inicio de sesión.
Esto es útil en algunas aplicaciones, pero en la mayoría de los casos preferimos que el usuario pueda elegir.
Así que dejaremos el valor por defecto en «false».

Este « `remember_me_parameter` » es el nombre del parámetro que hay que pasar al iniciar sesión
para activar la función «Recordarme».

La opción «Recordarme» también tiene que estar activada en nuestro autenticador `login_form`. Desplázate hacia arriba para encontrar su configuración
por defecto... Aquí está: `remember_me`, y el valor por defecto es `true`. Perfecto, así que siempre
que pasemos el parámetro `_remember_me` al iniciar sesión, se activará la opción «Recordarme».

Añadamos esta casilla de selección a nuestro formulario de inicio de sesión. Abre `templates/security/login.html.twig` y
desplázate hacia abajo hasta que encuentres este `div` comentado. Lo añadió el `maker-bundle`.
Descoméntalo.

Aquí tenemos un campo de casilla de selección, y el nombre es `_remember_me`. ¡Perfecto! Solo voy a
cambiar una cosa: añadir el atributo `checked` al campo para que venga marcado por defecto.

Probemos esto. Vuelve al navegador y ve a la página de inicio de sesión. ¡Genial! Aquí está
nuestra casilla «Recordarme» ya marcada de antemano. Inicia sesión con `picard@enterprise.space`...
contraseña: `makeitso`.

Ya hemos iniciado sesión... y, en realidad, no se nota ningún cambio... 

Inspecciona la página, ve a la pestaña «Aplicación» y busca las cookies de este sitio. Efectivamente,
tenemos una nueva cookie llamada `REMEMBERME`. Fíjate en la fecha de caducidad: está configurada para una fecha futura,
dentro de un año según nuestra configuración. Lo importante es que esta cookie no está
vinculada a la sesión.

Elimina la cookie `PHPSESSID` para simular que cierras el navegador... y actualiza la página...

¡Seguimos conectados! ¡Nuestro sistema de «recordarme» funciona!

A continuación, vamos a ver algunos atributos especiales de autenticación.
