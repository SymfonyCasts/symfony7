# Atributos de autenticación

Symfony Security tiene algunos atributos de autenticación especiales. Están definidos
como constantes en la clase ` `AuthenticatedVoter` `. Los encontrarás en el componente Symfony
Security. Estoy en PhpStorm, así que usaré la combinación de teclas «Shift+Shift» para activar
la función de búsqueda. Ahora buscaré « `AuthenticatedVoter` »...
¡Ahí está!

## Entender los atributos de seguridad

Antes hemos hablado un poco de los roles: todos nuestros usuarios tienen un `ROLE_USER`. Luego
usamos la función de Twig `is_granted()` para comprobar si el usuario tiene ese rol.
Estos atributos son parecidos a los roles, en el sentido de que son cadenas de texto, pero
no son lo mismo. Los roles están asociados al objeto de usuario. Estos atributos de autenticación
se determinan en tiempo de ejecución según el estado actual de sesión. Nos indican si el
usuario ha iniciado sesión, pero también cómo lo ha hecho.

La mejor forma de entender estos atributos es verlos en acción.

## Demostración práctica de los atributos de seguridad

Abre `src/Controller/MainController` y busca nuestro método «homepage». Añade « `dump()` », y
dentro tendremos un array asociativo con las siguientes claves y valores:

`ROLE_USER => $this->isGranted('ROLE_USER')`. Sé que dijimos que estos atributos no son roles,
pero se usan de la misma manera, así que primero comprobaremos si tienes el rol « `ROLE_USER` ».

El método `isGranted()` está disponible porque nuestra clase de controlador hereda de `AbstractController`.
Esta clase base nos ofrece un montón de métodos útiles, incluidos los ayudantes de seguridad.

Siguiente punto: ` `AuthenticatedVoter::IS_AUTHENTICATED_FULLY => $this->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)``.
Aquí estoy usando las constantes, pero los valores de cadena sin formato también funcionarían igual de bien.

Voy a repasar rápidamente el resto...

[[[ code('acf64538a7') ]]]

¿Los he pillado todos? 1, 2, 3, 4, 5, y en `AuthenticatedVoter`... 1, 2, 3, 4, 5, 6. Me salto
`IS_IMPERSONATOR` por ahora, ya que tiene que ver con suplantar la identidad de usuarios, y lo veremos
en detalle más adelante.

## Prueba en vivo de los atributos de seguridad

Es hora de ver estos atributos en acción. Ve a la página de inicio de nuestra app... asegúrate de que no has
iniciado sesión... y actualiza la página.

Genial, aquí está nuestro volcado.

Todos son «false», excepto `PUBLIC_ACCESS`, que es «true». 

`PUBLIC_ACCESS` es el que se sale de la norma. A diferencia de `IS_AUTHENTICATED_FULLY` o `IS_REMEMBERED`, no
describe al usuario actual. Se usa principalmente en tu configuración de seguridad para
marcar específicamente una ruta o camino como público. Como comprobación de `isGranted()`, es básicamente inútil porque
casi siempre devolverá `true`.

Ahora, ve a la página de inicio de sesión e inicia sesión con el nombre de usuario `picard@enterprise.space` y la contraseña: `makeitso`. Mantén
marcada la opción «Recordarme».

Ahora echa un vistazo al volcado. `ROLE_USER` es `true`, nada de extrañar, todos los usuarios tienen este rol.

`IS_AUTHENTICATED_FULLY` `true` solo aparece cuando el usuario ha iniciado sesión durante la sesión actual,
como acabamos de hacer. es porque no solo estamos «recordados», sino que estamos totalmente autenticados. 
 es siempre que estés autenticado, ya sea totalmente o como usuario «recordado». `IS_REMEMBERED` `false``IS_AUTHENTICATED` `true` 

Repitamos nuestro truco para borrar la cookie de sesión. Inspecciona la página, abre la pestaña «Aplicación»,
busca la cookie `PHPSESSID` y bórrala. Ahora actualiza la página y comprueba el volcado.

¿Qué ha cambiado? Bueno, `ROLE_USER` y `IS_AUTHENTICATED` siguen siendo verdaderos, pero `IS_AUTHENTICATED_FULLY` ahora es falso, y `IS_REMEMBERED` ahora es verdadero.

Ya ves cómo se pueden usar estos atributos para saber cómo está autenticado el usuario. ¿Por qué es esto importante?
¿Por qué no basta con tener solo `IS_AUTHENTICATED` y ya está? Bueno, un usuario que ha activado la opción «Recordarme» se considera menos seguro que
un usuario totalmente autenticado. Imagina a un usuario que ha iniciado sesión en un ordenador público y ha marcado la opción «Recordarme».
Cierra el navegador y se marcha. La siguiente persona abre el navegador y visita nuestra web.
¡Seguiría conectada como el usuario anterior!

Seguramente habrás visto que algunas páginas te piden que vuelvas a introducir tu contraseña al realizar acciones delicadas,
como cambiar tu dirección de correo o tu contraseña. ¡Así es como puedes hacer lo mismo en tus aplicaciones!

Puedes restringir ciertas acciones solo a los usuarios totalmente autenticados asegurándote de que tengan el
atributo «`IS_AUTHENTICATED_FULLY` », mientras permites a los usuarios que han marcado «Recordarme» realizar acciones menos delicadas
simplemente comprobando el atributo « `IS_AUTHENTICATED` ».

## `IS_AUTHENTICATED_REMEMBERED`

Probemos una última variante: cierra sesión… y vuelve a iniciar sesión, pero esta vez desmarca la opción «Recordarme»…

Echa un vistazo al volcado. Son todos los mismos valores que cuando iniciamos sesión con «Recordarme» marcado… incluso
`IS_AUTHENTICATED_REMEMBERED` … ¿Qué pasa aquí?

`IS_AUTHENTICATED_REMEMBERED` Es una pequeña peculiaridad histórica en la nomenclatura de Symfony. No significa:
«¿Está este usuario autenticado mediante la opción “Recordarme”?». Significa: «¿Está este usuario autenticado al menos tanto
como un usuario con la opción “Recordarme”?».

`IS_AUTHENTICATED` Se añadió más tarde para significar lo mismo, pero sin ese nombre tan confuso. Puede que
veas `IS_AUTHENTICATED_REMEMBERED` en código antiguo de Symfony, pero a partir de ahora deberías usar `IS_AUTHENTICATED`
.

## Conclusión

¡Uf! ¡Ya hemos terminado con estos atributos especiales! A continuación, profundizaremos en los roles y el
sistema de jerarquía de roles.
