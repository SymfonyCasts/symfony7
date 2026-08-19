# Crear un votante personalizado

Abre `src/Controller/StarshipAdminController.php`. Tenemos una configuración CRUD bastante estándar
para nuestras naves espaciales aquí. Y ahora mismo, el prefijo de ruta para todas ellas es
`/admin`. Si te acuerdas, todo lo que empieza por `/admin` queda restringido a
`ROLE_ADMIN` gracias a nuestro `access_control`.

Lo primero que quiero hacer es hacer esto público… y luego añadir un
sistema de permisos más detallado encima. Así que quita el prefijo `/admin`:

[[[ code('d05672c206') ]]]

Ve al navegador, asegúrate de que no has iniciado sesión y entra en `/starship`.
Ahí está nuestro CRUD… pero es totalmente público. Podemos editar naves, eliminarlas y
crearlas como usuario anónimo. Desde luego, no es lo que queremos.

Este es el objetivo: los usuarios que hayan iniciado sesión solo deberían poder editar su propia nave, si
tienen una. Recuerda que añadimos una propiedad `starship` a nuestra clase `User`. Así que si
Picard hubiera iniciado sesión, solo podría editar y eliminar esta fila superior, porque
esa es su nave estelar. La visualización puede seguir siendo pública para todo el mundo: usuarios anónimos,
otros usuarios que hayan iniciado sesión, cualquiera. Son las operaciones que realmente cambian los datos en
la base de datos las que requerirán permisos especiales.

## Ocultar acciones con `is_granted()`

Empieza por ocultar estas acciones tras algunas llamadas a ` `is_granted()` ` en nuestras plantillas de Twig
.

Ve a `templates/starship_admin/` —aquí es donde están todas las plantillas para este
CRUD— y abre primero `index.html.twig`. Justo en la parte superior, esta etiqueta de anclaje es
el botón «Crear nuevo». Para la creación, lo vamos a mantener sencillo: solo los administradores
pueden crear nuevas naves. Así que envuélvelo en `{% if is_granted('ROLE_ADMIN') %}` y
`{% endif %}`:

[[[ code('1ecdab3788') ]]]

Perfecto. Ahora solo los administradores pueden crear nuevas naves espaciales.

Lo siguiente es el botón de editar. Pero primero, actualiza la página... «Crear nuevo» ha
desaparecido. Ahora elimina ese botón de editar para los usuarios anónimos.

Aquí abajo tenemos los botones «Mostrar» y «Editar». Envuelve el botón de edición en una
comprobación de`is_granted()`. Y para este, no vamos a usar un rol. Vamos
a usar un atributo personalizado genérico, a veces llamado «permiso». Ponle un nombre
sencillo y claro: `edit`.

Pero aún no hemos terminado: pasa un segundo argumento a ` `is_granted()``. Este segundo
argumento se llama «sujeto», y nuestro sujeto va a ser ` `starship``. Ahora
cierra todo con un ` `{% endif %}``:

[[[ code('aaffa8d444') ]]]

Actualiza la página... y ese botón de editar desaparecerá.

Hay otro sitio más: la página de visualización. Si haces clic en «Mostrar», queremos excluir
estas acciones si no tienes permiso, con esas mismas comprobaciones de `is_granted()`. Así que
ve a la plantilla de visualización y desplázate hasta el final, donde encontramos el enlace de edición
. Envuélvelo en `{% if is_granted('edit', starship) %}` y, a continuación, ciérralo con
`{% endif %}`:

[[[ code('619c053edc') ]]]

El botón de borrar va a usar un permiso diferente:
`{% if is_granted('delete', starship) %}`:

[[[ code('92db7250eb') ]]]

Vuelve atrás y actualiza esta página... y ya no están, porque somos anónimos.

## Iniciar sesión no cambia... nada

Vale, inicia sesión como Picard: `picard@enterprise.space`, contraseña `makeitso`.

Ahora visita la lista de naves espaciales... y tenemos exactamente los mismos permisos que un
usuario anónimo. De hecho, esta de aquí es nuestra nave, pero lo único que podemos hacer es mostrarla
; no podemos editarla ni borrarla.

Para gestionar esos permisos tan detallados, necesitamos un «votante» personalizado.

## `make:voter`

¡Y hay un «maker» para eso! En tu terminal, ejecuta:

```terminal
symfony console make:voter
```

Llámalo « `StarshipVoter` ».

Ahora echa un vistazo a lo que se ha creado: `src/Security/Voter/StarshipVoter.php`.

Te ha añadido algo de código estándar, incluyendo dos constantes de permisos. Para la primera,
cambia el valor real a simplemente « `edit` », igual que lo que usamos en nuestra
plantilla.

¿Y esta constante `VIEW`? No vamos a tener permisos tan detallados para
la visualización: cualquiera puede ver una nave espacial. Así que cambia esta constante a `DELETE`... y
cambia también su valor a `delete`:

[[[ code('a2b6b7907e') ]]]

## `supports()`

¿Y qué hace realmente un «voter»? Como esta clase hereda de `Voter`, se
configura automáticamente con Symfony. No tienes que hacer nada en cuanto a servicios:
ya está registrado automáticamente.

Cada vez que el sistema de seguridad calcula qué «voter» debe usarse, llama a
`supports()`. Ahí es donde le indicamos cuándo usar este «voter». El `$attribute` es
lo que pasamos a `is_granted()` como primer argumento, y el `$subject` es el
segundo argumento.

En este caso, el código generado ya está casi correcto. Tenemos
`in_array($attribute, [self::EDIT, self::VIEW])` —cambia `VIEW` por `DELETE` — y
luego `$subject instanceof Starship`. Eso es justo lo que queremos. Solo voy a
ordenarlo un poco importando `Starship`:

[[[ code('f30591b3fc') ]]]

## `voteOnAttribute()`

Cuando `supports()` devuelve `true`, se llama a `voteOnAttribute()`.

Aquí también hay un poco de código repetitivo. Este método tiene un
argumento ``TokenInterface $token` `. Lo único que realmente necesitas saber sobre el
token ahora mismo es que es lo que utiliza el sistema de seguridad para envolver al usuario. Puedes obtener
el usuario a partir de él con ` `getUser()` `, y eso es lo que está pasando aquí.

Esta comprobación de `instanceof` garantiza que `$user` no sea nulo. Como solo tenemos una clase de usuario,
cámbiala por nuestra entidad `User`. Eso te ayudará con el autocompletado más abajo.

Si no tenemos ningún usuario, añadimos un motivo para la depuración y devolvemos `false`, lo que significa
que la comprobación de `is_granted()` fallará.

Aquí abajo, activamos el atributo. Así puedes tener una lógica completamente diferente
para `EDIT` a la hora de determinar si alguien debería poder editar, y
otra lógica diferente para borrar. Y si ninguna de ellas coincide, `return false`, lo que hace que
la comprobación falle.

En nuestro caso, `EDIT` y `DELETE` van a tener la misma lógica, así que podemos agrupar todo
esto.

Pero primero, en la parte de arriba, para este tema: como `supports()` ya ha garantizado
que se trata de una instancia de `Starship`, podemos estar seguros de que este `mixed $subject` es
de hecho un `Starship`. Así que genera un docblock. Borra todo menos el
tema... y escríbelo como `Starship`:

[[[ code('de16dfb53c') ]]]

Más abajo, tenemos al usuario y tenemos la nave espacial (como sujeto). Lo que significa que ahora podemos
comprobar si el usuario forma parte de la nave espacial:
`return $user->getStarship()?->getId() === $subject->getId()`:

[[[ code('d63073d9af') ]]]

## Probémoslo

¡Ya está! Vuelve al navegador y actualiza la página; recuerda que hemos iniciado sesión como
Picard.

¡Perfecto! Parece que funciona. Podemos editar solo esta nave, porque es la nuestra.
¿Y todas las demás? No tienen botón de edición. Si pulsamos «Mostrar» en nuestra nave, se ven las acciones.
Haz clic en «Editar».

Tenemos un pequeño problema… bueno, en realidad no tan pequeño… a ver si adivinas cuál es. Lo
arreglaremos en el próximo capítulo.
