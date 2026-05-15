# ¡Actualizando a Symfony 8.0!

¿Symfony 7.4? Comprobado. ¿Sin obsoletos? Comprobado.

Bien, ¡por fin ha llegado el momento de actualizar a Symfony 8! Por suerte, ya hemos hecho la mayor parte del trabajo duro, así que el proceso de actualización debería ser sencillo. Empecemos abriendo nuestro archivo `composer.json`.

Haremos lo mismo que hicimos al actualizar a 7.4. Editar... Busca... Reemplaza... `7.4.*` y sustitúyelo por `8.0.*`. Reemplaza todo... Bien.

Voy a comprobarlo un momento. La sección `require` tiene buen aspecto... Ah, genial, nuestra opción`extra` `symfony` `require` se ha actualizado. ¡Y `require-dev` también se ve bien!

[[[ code('f0e52d5b2a') ]]]

Dirígete al terminal y ejecuta:

```terminal
symfony composer update
```

## Resolver errores

Ooo, un error, desplacémonos un poco hacia arriba. Vale, nuestro composer.json raíz requiere`symfonycasts/tailwind-bundle` `^0.9.0` . Este bundle requiere algunos paquetes de Symfony, pero por desgracia, las versiones de Symfony 8 no son compatibles. Veamos si hay una versión actualizada.

En el navegador, ve a packagist.org. Busca `tailwind-bundle`... Aquí lo tienes. Aquí a la derecha, están todas las versiones, selecciona `v0.9.0`, la versión en la que estamos. Efectivamente, los paquetes Symfony que requiere sólo soportan hasta Symfony 7. Aquí está la versión más reciente: `v0.12.0`, y ¡qué bien, ésta sí es compatible con Symfony 8!

De vuelta a nuestro `composer.json`... busca el `tailwind-bundle`... y cambia su versión a `^0.12.0`:

[[[ code('3f6a754752') ]]]

Probemos de nuevo la actualización.

```terminal-silent
symfony composer update
```

## Resolviendo más errores

Huh, otro error. Veamos qué está pasando. Nuestro `composer.json` requiere`monolog-bundle ^3.0`, pero parece que `monolog-bundle 3` no es compatible con Symfony 8. Volvamos rápidamente a Packagist y busquemos `monolog-bundle`.

Sí, la versión 3 no soporta Symfony 8... ¡pero la versión 4 sí!

Volvamos a `composer.json`, busquemos `monolog-bundle`... y cambiemos su versión a `^4.0`:

[[[ code('404b6a4b0d') ]]]

***NOTE
Si te preguntas por qué `monolog-bundle` utiliza una estrategia de versionado diferente a la del resto de Symfony, es porque tiene su propio repositorio y ciclo de publicación. No forma parte del monorepo del núcleo de Symfony.
***

¿Será la tercera actualización la vencida?

```terminal-silent
symfony composer update
```

¡Perfecto! ¡Esta vez ha funcionado! Confirmémoslo ejecutando:

```terminal
symfony console --version
```

¡Excelente! ¡Symfony 8.0.10!

## Comprobación de dependencias obsoletas

Ahora déjame mostrarte un práctico comando de Composer que comprueba si tienes dependencias desactualizadas. Es decir, dependencias que tienen versiones más recientes disponibles.

```terminal
symfony composer outdated -D
```

La opción `-D` significa dependencias directas, es decir, sólo los paquetes que están en el archivo `composer.json` de nuestra aplicación. No las dependencias transitivas, que son las dependencias de nuestras dependencias. En realidad, sólo me interesan las directas.

Parece que tenemos 3 paquetes que tienen versiones más recientes disponibles. `phpdocumentor/reflection-docblock`,`symfony/stimulus-bundle` y `symfony/ux-turbo` pueden actualizarse a la siguiente versión mayor. Vamos a hacerlo en nuestro archivo `composer.json`.

Busca `phpdocumentor/reflection-docblock` y cámbialo por `^6.0`. A continuación, `stimulus-bundle`, cámbialo por `^3.0`. Por último, `ux-turbo`, cámbialo por `^3.0`:

[[[ code('38cf8b54ad') ]]]

¡Actualicemos!

```terminal-silent
symfony composer update
```

¡Excelente! Que no haya errores significa que no hay conflictos con los paquetes actualizados, así que estamos listos. Ejecutar `git status` confirma que sólo han cambiado nuestros archivos `composer.lock`, `composer.json` y `reference.php`. Recuerda que el archivo `reference.php` ayuda a tu IDE con la configuración de Symfony basada en matrices y se genera automáticamente. Podemos ignorarlo ya que utilizamos una configuración basada en YAML.

Confirma nuestros cambios con:

```terminal
git commit -a -m "upgrade to Symfony 8!"
```

Hmm, al terminal no le ha gustado mi entusiasmo con el signo de exclamación... Cancelaré este comando y volveré a intentarlo sin él:

```terminal
git commit -a -m "upgrade to Symfony 8"
```

¡Ya está!

## Comprobación de actualizaciones de recetas

Ahora que hemos hecho borrón y cuenta nueva, comprueba si hay actualizaciones de recetas con:

```terminal
symfony composer recipe:update
```

No, ¡no hay recetas nuevas!

## Verificar la actualización

Vuelve al navegador y actualiza la página de inicio. ¡Genial! ¡Estamos en Symfony 8 sin errores!

Ya que hemos actualizado `ux-turbo`, voy a confirmar que funciona haciendo clic en un enlace... Las peticiones turbo se realizan mediante AJAX y, efectivamente, nuestra barra de herramientas de depuración web muestra que se ha realizado una petición AJAX.

También hemos actualizado `stimulus-bundle`, así que vamos a comprobar que funciona correctamente. Abre las herramientas de desarrollador y comprueba la pestaña de la consola... Estos registros muestran que Stimulus y nuestros controladores Stimulus se están inicializando correctamente

¡Eso es todo, amigos! Espero que hayas aprendido algo valioso de este tutorial y consigas actualizar tus aplicaciones a Symfony 8 sin problemas.

Hasta la próxima, ¡feliz programación!
