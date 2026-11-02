# Actualización a Symfony 8.2

¡Hola, amigos! Bienvenidos a nuestro recorrido por las novedades del sistema de seguridad de Symfony 8.2.
Symfony 8.2 se está convirtiendo en una de las versiones más importantes de la historia,
¡probablemente la más importante desde el salto de la 1.4 a la 2.0! Una de las principales razones es que los flujos de trabajo asistidos por IA
han acelerado mucho la incorporación de nuevas funciones y la corrección de errores. Muchos de esos
cambios afectan al componente de seguridad y, como acabamos de terminar unos cursos sobre seguridad, es
el momento perfecto para analizarlos. Muchas de estas funciones refuerzan de verdad la seguridad de
tu app, así que veamos cómo se usan.

## Configuración

Este curso continúa justo donde lo dejó nuestro curso [Symfony Security: Going Further](https://symfonycasts.com/screencast/symfony8-security-extra)
. Si ya lo has hecho y aún tienes el código en tu ordenador, puedes seguir trabajando
directamente con él. Si quieres empezar desde cero, descárgate el código del curso desde esta página. En el directorio « `start/`» 
encontrarás un archivo « `README.md` » con todos los pasos de configuración. Una vez que inicies el
servidor con:

```terminal
symfony serve -d
```

Verás nuestra aplicación Starshop. En la barra de herramientas de depuración web, fíjate en que estamos en Symfony 8.0.
Paso uno: ¡actualiza a la versión 8.2!

## Actualizar las versiones de Symfony

En `composer.json`, la forma más fácil de hacerlo es con la función «Buscar y reemplazar». Busca`8.0.*` y cámbialo por `8.2.*`. Hay 23 coincidencias: pulsa «Reemplazar todo»:

[[[ code('2395355c26') ]]]

Ahora ve al terminal y ejecuta:

```terminal
symfony composer update
```

## Habilitar la estabilidad de desarrollo

Y… ¡nos sale un error! Desplázate hacia arriba. Dependiendo de cuándo estés siguiendo estos pasos, puede que
no veas este mensaje. Dice que no se puede instalar ninguna de las versiones de Symfony 8.2 porque
no cumplen con nuestro nivel mínimo de estabilidad. En `composer.json`, tenemos
`"minimum-stability": "stable"`. Si la versión 8.2 ya se ha lanzado oficialmente cuando veas
esto, no te saldrá este error. Pero como estoy grabando antes del lanzamiento, cámbialo por
`dev`:

[[[ code('0fb9aaf740') ]]]

Así es como siempre puedes probar las versiones preliminares de Symfony, y cuanta más gente
lo haga, más ayuda le das al equipo principal.

Ejecuta la actualización nuevamente:

```terminal-silent
symfony composer update
```

## Aumentar la versión de la plataforma PHP

¡Más errores! Este es un poco más sutil: `symfony/asset` 8.2-dev requiere PHP 8.4.1 o
superior. En la sección « `config.platform.php` », tenemos configurado simplemente `8.4`. Tiene que ser al menos
`8.4.1`, así que cámbialo a una versión superior a esa... `8.4.26`:

[[[ code('e4590636b6') ]]]

Ejecuta nuevamente la actualización de Composer:

```terminal-silent
symfony composer update
```

¿Ves esas advertencias en la parte de arriba? Son de la CLI de Symfony, no de Composer. Lee
`config.platform.php` para decidir qué binario de PHP ejecutar, y como no tengo exactamente la 8.4.26
instalada, recurre a la última versión 8.4 que sí tengo. Todo sigue funcionando, pero para
silenciarlo, puedes configurar `config.platform.php` con tu versión exacta.

## Actualización a Symfony UX 3.0

Vale, tenemos otro error de actualización de Composer. Este dice que estamos intentando instalar `symfony/asset-mapper` 8.2, pero la
versión de `symfony/ux-turbo` en nuestro archivo lock no lo permite. Buenas noticias: ¡ya han salido las versiones 3.0
de Symfony UX! Busca `symfony/ux-turbo` en `composer.json` y
cámbialo por `^3.0`:

[[[ code('d0101ef627') ]]]

Ejecuta... y tenemos que hacer lo mismo con `symfony/stimulus-bundle`.
Búscalo y cámbialo también a `^3.0`:

[[[ code('3fcac3b356') ]]]

Una vez más:

```terminal-silent
symfony composer update
```

¡Ya está! Perfecto. Vuelve a la app, actualiza... y ya estamos en `8.2.x-dev`. ¡Éxito!

## Ignorando `config/schema.json`

Vuelve al terminal y comprueba qué ha cambiado:

```terminal
git status
```

`assets/controllers.json` los cambios de la actualización de Stimulus, además de los habituales
`composer.json` y `composer.lock`. Pero también hay un nuevo archivo `config/schema.json`.
Este se genera automáticamente y ayuda a tu editor a autocompletar y validar tus archivos de configuración YAML.
Como se genera automáticamente, prefiero ignorarlo. Abre `.gitignore` y, igual que hicimos
con `config/reference.php`, añade `config/schema.json`:

[[[ code('d045dd1341') ]]]

Ejecuta « `git status` »:

```terminal-silent
git status
```

Perfecto. Haz un commit de todo con:

```terminal
git commit -a -m "Upgrade to Symfony 8.2"
```

## Actualización de las recetas de Flex

El siguiente paso en cualquier actualización es actualizar tus recetas de Flex:

```terminal
symfony composer recipe:update
```

Esto te da una lista enorme, y es que hay muchas. No te voy a hacer ver cómo las reviso
una por una, pero revisarlas debería formar parte de tu proceso de actualización. Dicho esto,
como solo estamos pasando de la 8.0 a la 8.2, hoy por hoy no es estrictamente necesario. Las actualizaciones de las recetas
entre versiones menores suelen consistir principalmente en habilitar nuevas funciones y corregir elementos obsoletos. Donde realmente
importan es cuando das el salto a la siguiente versión mayor —como de la 8.4 a la 9.0—, porque es entonces cuando
las cosas realmente dejan de funcionar. Así que, de momento, las dejaré para más adelante. Para ver una actualización mayor completa, con recetas
y todo, échale un vistazo a nuestro curso [Actualización a Symfony 8](https://symfonycasts.com/screencast/symfony8-upgrade)
.

A continuación: ¡vamos a ver algunas mejoras en el sistema de cierre de sesión!
