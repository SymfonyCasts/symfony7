# Listado de piezas

Nueva misión: necesitamos una página que enumere todas las piezas disponibles. Nuestro equipo de ventas ferengi la utilizará para el clásico upselling. Ya sabes, lo de siempre:

> Oye, acabas de comprar una nave estelar, ¿qué tal unos nuevos y relucientes organizadores de cristal de dilitio o
> estabilizadores de portavasos?

Utilicemos MakerBundle para adelantarnos. Busca tu terminal y ejecuta:

```terminal
symfony console make:controller
```

Llámalo... espera... `PartController`. Brillante! Para mantener las cosas centradas, di no a las pruebas.

¡Listo! Una clase y una plantilla. Hasta aquí, todo perfecto. Echa un vistazo al nuevo `PartController`:

[[[ code('65362ae804') ]]]

No hay mucho que ver: renderiza una plantilla. Vaya.

Cambia la URL a `/parts`, y renómbrala a `app_part_index`:

[[[ code('0fe202463d') ]]]

Copia el nombre de la ruta para que podamos enlazar con ella... y abre `base.html.twig`.

## Enlace a la página de piezas

¿Recuerdas el enlace "Acerca de" que está ahí sin hacer nada? Cámbialo y conviértelo en un enlace "Piezas". Establece `href` en `{{ path('app_part_index') }}`:

[[[ code('e47492e405') ]]]

Dirígete a la página de inicio, haz clic en nuestro recién estrenado enlace y... bueno, no es lo más bonito, ¡pero funciona!

Antes de celebrarlo, deberíamos cambiar el título de la poco inspiradora `Hello PartController`. Abre `templates/part/index.html.twig`. Ya estamos modificando el bloque `title`, así que hagámoslo algo interesante como `Parts`:

[[[ code('690f2d9c2b') ]]]

## Añadir algo de sustancia: Recorrer las partes en bucle

Para hacer un bucle sobre las piezas, en `PartController`, necesitamos consultar todas las piezas.

Añade un argumento `StarshipPartRepository` para autoinstalarlo. Llámalo como quieras, como `$leeroyJenkins` o... `$repository`. Para obtener todas las piezas, es sencillo: `$parts = repository->findAll()`:

[[[ code('f037f81b93') ]]]

## Imprimir piezas en la plantilla

Ahora que tenemos esta variable `parts` en nuestra plantilla, podemos hacer un bucle sobre ella:

[[[ code('6bb20859c2') ]]]

Para animar las cosas, pegaré esta plantilla:

[[[ code('98b405ca44') ]]]

Es sólo un montón de cosas para que quede bonito. Puedes obtener este código del bloque de código de esta página.

Actualiza y... ¡mucho mejor! 

## Un pequeño truco: Utilizar la función Ciclo

Una cosa interesante que estoy utilizando aquí es la función `cycle()`:

[[[ code('397637c5f5') ]]]

Quería dar a cada engranaje un color aleatorio para que tuviera un aspecto más atractivo. La función `cycle()`nos permite pasar un montón de cadenas, y luego `loop.index 0` las recorre en ciclos. Es un pequeño toque, pero añade ese toque que tanto gusta a los ferengis.

Por último, sustituye `assigned to SHIP NAME` por `{{ part.ship }}` - esta vez, no estoy utilizando `ship.part`, sino el otro lado de la relación,`part.ship.name`. Uy, me equivoqué, debería ser `part.starship.name`:

[[[ code('535e603425') ]]]

Y... ¡ya está! 

A continuación, hablaremos de las uniones. ¡Únete! Lo siento, no pude resistirme.