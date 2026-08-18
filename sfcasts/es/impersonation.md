# Suplantar la identidad de usuarios con `switch_user`

Es hora de hablar de la suplantación de identidad de usuarios, también conocida como «cambio de usuario». Se trata de
la función que permite que un usuario se haga pasar temporalmente por otro.

Esto resulta muy útil para que los administradores del sitio puedan solucionar un problema que tenga un usuario concreto.
Puedes suplantar su identidad para ver exactamente lo mismo que vería él al iniciar sesión en el
sitio… sin necesidad de saber su contraseña.

## Crear un CRUD de usuarios

Lo primero que vamos a hacer es crear un CRUD de usuarios, para que los administradores puedan elegir el
usuario del que quieren suplantar la identidad en la base de datos.

En tu terminal, ejecuta:

```terminal
symfony console make:crud
```

Esto va a ser para la clase « `User` ». Y la llamaremos « `UserAdminController` ».
¿Queremos pruebas? No.

Vale, ya ha generado todos estos archivos. Ve a tu IDE y busca el nuevo
«`UserAdminController` ».

Lo único que vamos a cambiar aquí es el prefijo de ruta. Cámbialo a `/admin/user`:

[[[ code('9ec23052ee') ]]]

Como estas rutas ahora llevan el prefijo `/admin`, nuestra configuración de `access_control` ya
las restringe solo a los administradores. Así que eso ya lo tenemos de serie.

¡Veamos este nuevo CRUD! En la app, ve a `/admin/user`... y nos redirige a
la página de inicio de sesión. Necesitamos un usuario de admin, así que usa `janeway@starfleet.space`, contraseña
`coffeeblack`.

Este CRUD… no tiene muy buena pinta… así que vamos a darle un poco de estilo. Abre
`templates/user_admin/index.html.twig`. Voy a seleccionar todo y pegar
un código HTML más bonito; lo puedes encontrar en el script de abajo:

[[[ code('ed5e55b002') ]]]

Vuelve al navegador y actualiza la página. ¡Mucho mejor!

Ya ves que he añadido los enlaces «cambiar a», pero todavía no hacen nada.
Así es como un administrador cambiará a otro usuario.

## La configuración de `switch_user` 

Para ver cómo se habilita y configura la función de suplantación de identidad, ve a tu
terminal y ejecuta:

```terminal
symfony console config:dump security firewalls
```

Desplázate hacia arriba... hasta que encuentres la sección « `switch_user` »... aquí la tienes.

Aquí están las opciones de configuración. « `provider` » sirve si quieres usar un proveedor de usuarios
diferente por alguna razón. Recuerda que el proveedor de usuarios es cómo el sistema de seguridad
carga a los usuarios. La mayoría de las apps, como la nuestra, solo tienen un proveedor, así que podemos dejar
esto como está por defecto.

`parameter` es el parámetro de consulta de la URL que activa el cambio. También está bien
dejarlo como está por defecto.

Cambiar de usuario es una acción peligrosa: no querrás que
cualquier usuario pueda suplantar a un administrador de tu sitio. Así que… por defecto, esta función
está restringida a un rol: `ROLE_ALLOWED_TO_SWITCH`. Cualquiera que no tenga
ese rol no podrá cambiar de usuario.

Por último, `target_route` es la página a la que quieres enviar al usuario una vez que se haya producido el cambio.

## Activar `switch_user`

Es hora de configurarlo. Primero, ve a `config/packages/security.yaml`. En la sección « `role_hierarchy` », en
«`ROLE_ADMIN` », añade `ROLE_ALLOWED_TO_SWITCH`:

[[[ code('1277fdb16a') ]]]

Ahora nuestros administradores tendrán este rol.

Y luego, aquí arriba, quita el comentario de « `switch_user: true` »; lo de « `true` » solo activa la función con
todos los valores predeterminados.

Quiero cambiar una cosa: `target_route`. Envíalos a `app_homepage`:

[[[ code('6e3a3f9563') ]]]

Si no lo configuras, por defecto, se conectarán desde la página en la que estén
en ese momento. Y ya te puedes imaginar: si cambiamos a Picard en esta página, él no
tendría acceso, así que nos saldría inmediatamente un 403. Creo que siempre es mejor elegir una
página de destino a la que todo el mundo tenga acceso.

## Configurar el botón de cambio

Ahora vamos a configurar ese botón. Vuelve a la plantilla de índice y busca ese enlace «cambiar a»
… aquí está. Borra `href`. ¡Hay una función de Twig muy útil para generar
la URL por nosotros! Escribe `impersonation_path(user.userIdentifier)`:

[[[ code('4cfab28e2e') ]]]

Haz clic para encontrarlo en nuestra entidad `User`. Si te acuerdas, `getUserIdentifier()` viene
de `UserInterface` y nuestra implementación de `User` devuelve la dirección de correo electrónico.

Ahora vuelve al navegador y actualiza esta página de administración de usuarios. Pasa el cursor por encima de «cambiar a...» de Picard y,
en la parte inferior, el navegador muestra la URL que se ha creado. ¡Haz clic en el botón!

## Suplantando a Picard

Estamos de vuelta en la página de inicio... eso era de esperar... pero ¿nos hemos cambiado a Picard?

Echa un vistazo a la barra de herramientas de depuración web. ¡Estamos autenticados como `picard@enterprise.space`!
¡Ha funcionado!

Aquí incluso hay un poco más de información. Nos muestra quién se está suplantando:
es nuestra cuenta de usuario original. Y tenemos este enlace especial «Salir de la suplantación».

Todo esto está muy bien para el desarrollo. Pero en producción no hay barra de herramientas de depuración web, así que
necesitamos nuestra propia forma de salir de la suplantación. Y deberíamos dejar muy claro que
estás suplantando a alguien, por si se te olvida.

¡Lo veremos a continuación!
