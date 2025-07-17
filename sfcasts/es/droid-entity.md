# Entidad Droide para la Relación Muchos a Muchos

Ya hemos probado bien los tipos de relación. Hemos visto`ManyToOne` y `OneToMany`, que en realidad son el mismo tipo de relación, visto desde lados diferentes. Así que, en realidad, hasta ahora sólo hemos explorado un tipo de relación: `ManyToOne`.

¿Qué hay de esa relación `OneToOne` de la que tal vez hayas oído hablar? Pues... ¡sorpresa! No es más que un `ManyToOne` disfrazado: la base de datos se parece a un `ManyToOne`, salvo que tiene una restricción única en la clave externa para asegurarse de que cada lado de la relación sólo puede referirse a un elemento.

La cuestión es que `ManyToOne` `OneToMany` , y `OneToOne` son todos, efectivamente, del mismo tipo de relación.

## Entra en los droides

Bien, hablemos de reparación espacial. Para nosotros, los humanos, es un trabajo peligroso: el vacío del espacio, el frío, la falta de oxígeno, las lluvias ocasionales de asteroides y el vacío infinito. Eso sin mencionar cuando Bob se olvidó de asegurar su arnés y se fue flotando hacia su propia frontera final. Tardaron horas en encontrarle. Nunca volvió a ser el mismo después de aquello.

Entonces, ¿quién mejor para abordar esto que nuestros fieles droides? 

Diriges un ejército de droides, cada uno está asignado a varias naves estelares y cada nave estelar tiene varios droides. Aquí es donde entra en juego el segundo y último tipo de relación: `ManyToMany`.

Para preparar el escenario, necesitamos una entidad `Droid`. A estas alturas, ya sabes lo que hay que hacer:

```terminal
symfony console make:entity Droid
```

Y así de fácil, ya estamos en marcha. Esto sólo necesita unas pocas propiedades:`name` y `primaryFunction`. Con las predeterminadas bastará. Eso es todo, muy fácil. 

Pero el trabajo de un desarrollador nunca termina. Como somos desarrolladores eficientes, no vagos, copia el comando para generar la migración y ejecútalo:

```terminal-silent
symfony console make:migration
```

Echa un vistazo. Aquí no hay sorpresas. Entonces... ejecútalo:

```terminal
symfony console doctrine:migrations:migrate
```

Y... tenemos una nueva y reluciente tabla `droid` en la base de datos. Todavía no está relacionada con `ship`, pero bueno, toda relación tiene que empezar por algún sitio.

## Poblar el universo con droides

Antes de poner esto en marcha, ¡vamos a fabricar algunos droides! Ejecuta:

```terminal
symfony console make:factory Droid
```

Abre `src/Factory/DroidFactory.php`. Ya está listo, pero les falta personalidad. Sustituiré la matriz por datos más interesantes:

[[[ code('7d04c9335b') ]]]

***NOTE
Para que esto funcione, actualiza también `AppFixtures` para incluir```php
DroidFactory::createMany(100)
```
***

Lo haremos un poco más tarde. Recarga los accesorios con:

```terminal
symfony console doctrine:fixtures:load
```

¡Y ahí lo tienes! Una tabla `droid` llena hasta los topes de droides dispuestos a ayudar y no a morir en el solitario vacío del espacio. Pero todavía no se puede asignar un droide a una nave. Cambiemos eso con nuestro último tipo de relación: `ManyToMany`.
