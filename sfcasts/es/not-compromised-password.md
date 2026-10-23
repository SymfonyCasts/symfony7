# Exigir contraseñas seguras y sin concesiones

Ahora tenemos tres sitios en los que pedimos a alguien que cree una nueva contraseña: nuestro proceso de cambio de contraseña,
el proceso de registro y el proceso de restablecimiento de contraseña.
¿La buena noticia? El restablecimiento de contraseña y el cambio de contraseña ya comparten
`ChangePasswordFormType`. ¿La mala noticia? El registro tiene su propio
`RegistrationFormType`... con su propio conjunto de restricciones de validación, que es diferente.

En nuestro `ChangePasswordFormType`, tenemos cuatro restricciones para el campo de la contraseña: `NotBlank`, `Length`, además de otras dos más interesantes:
`PasswordStrength` y `NotCompromisedPassword`. Ahora echa un vistazo a
`RegistrationFormType`: solo `NotBlank` y `Length`, y ese `Length`
acepta sin problemas una contraseña de 6 caracteres.

Así que, en lugar de copiar las restricciones de un formulario al otro —y luego tener que
mantener las dos listas sincronizadas para siempre—, crea una restricción compuesta personalizada que las incluya a todas
.

## Creación de la restricción compuesta

En `src/`, añade una nueva clase PHP llamada `Password` en el espacio de nombres `App\Validator\Constraints`
. Hazla `final` y extiéndela desde `Compound` —la del componente validador de Symfony—.
A continuación, implementa el único método: `getConstraints()`.

Dentro, devolvemos un array con las restricciones en las que este debería expandirse. Queremos el
conjunto completo, así que vuelve a `ChangePasswordFormType`, copia las cuatro... de vuelta en
`Password::getConstraints()`... vuelve... y pégalas:

[[[ code('269f086e44') ]]]

Ahora, en `ChangePasswordFormType`, sustituye todo ese array por solo `new Password()`:

[[[ code('9cf866f5f0') ]]]

Y haz lo mismo en `RegistrationFormType`:

[[[ code('3ba1d69ef4') ]]]

Perfecto. Es una forma genial de reducir la duplicación.

## Viendo cómo fallan los cuatro a la vez

Probemos esto. Ve a la app y haz clic en el enlace de registro. No me voy a preocupar
por los demás campos; centrémonos en la contraseña. Escribe `password` y regístrate.

Fíjate en el campo de la contraseña: tres errores de validación
distintos. Nuestra contraseña debe tener al menos 12 caracteres, su seguridad es demasiado baja
y la contraseña se ha filtrado en una filtración de datos. Repasemos estos errores uno por
uno.

El primero es fácil: es la restricción de `Length`, que exige un mínimo
de 12 caracteres. Así que vamos a arreglarlo: prueba con `passwordpassword` en su lugar.

Ahora ya solo nos quedan dos errores. El primero —«La contraseña no es lo suficientemente segura»— viene
de `PasswordStrength`. Esa página hace unos cálculos para estimar la entropía de la
contraseña —cuántos caracteres distintos hay, de un conjunto de qué tamaño— y así determinar cuán compleja
es realmente. Y si nos metemos en la clase, podemos ver qué se puede configurar: un
`minScore`. Por defecto, usa `STRENGTH_MEDIUM`, así que no exige una contraseña muy segura —
y ese es el ajuste que tienes que modificar si quieres exigir más o menos seguridad. Por eso `password`
era un problema… y por eso `passwordpassword` sigue siéndolo. Es larga, pero demasiado simple.

Vale, volvamos al formulario con algo más complejo y de más de 12 caracteres. ¿Qué
tal `correcthorsebatterystaple`? Suena bastante complejo… pero seguimos obteniendo un
error:

> Esta contraseña se ha filtrado en una filtración de datos, no debes usarla.

## Cómo funciona `NotCompromisedPassword` 

Eso viene de la restricción « `NotCompromisedPassword` », y hace algo
realmente ingenioso: comprueba la contraseña en
[Have I Been Pwned](https://haveibeenpwned.com/Passwords), un servicio que rastrea
contraseñas encontradas en filtraciones de datos reales. Si la tuya aparece ahí, no puedes usarla.

Y no te preocupes: no envía contraseñas sin cifrar por la red. Envía una pequeña
parte del hash de la contraseña, recibe una lista de posibles coincidencias y las compara
localmente. La contraseña en sí nunca sale de tu servidor.

Así que busquemos algo que supere las cuatro pruebas. Prueba `makeitso1engage?` y regístrate.
Genial: ¡ni un solo error en el campo de la contraseña! Y como es un campo de contraseña,
se borra solo, así que tenemos que rellenarlo cada vez que enviamos el formulario.

Es una forma muy ordenada de agrupar las restricciones de cualquier campo en una sola
restricción que puedes reutilizar en varios sitios.

Siguiente: nuestro formulario de inicio de sesión está limitado, pero el de registro está totalmente abierto. Vamos a
limitar su frecuencia.
