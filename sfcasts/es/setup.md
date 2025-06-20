# Configuración

¡Hola amigos! ¡Bienvenidos de nuevo! Y bienvenidos de nuevo a mí, si se me permite el atrevimiento. Vuelvo de mis "vacaciones" de 14 meses de cáncer cerebral. Por desgracia, no estoy del todo mejor y claro, tecleo con una mano, como un pirata de Symfony. Pero, caramba, os he echado de menos a todos y he echado de menos Symfony. Y hoy es un buen día. Gracias por el apoyo, el cariño y la paciencia. Ahora, ¡a trabajar!

En el tutorial anterior, hicimos cosas impresionantes. Creamos una entidad, configuramos migraciones, creamos fixtures y realizamos consultas como nerds de SQL. Pero seamos realistas, no podemos construir nada que impresione a nuestros amigos o a nuestra abuela sin entender las relaciones de la base de datos. Por ejemplo, "esta porción de pizza me pertenece" o "tengo muchas porciones de pizza" Mmm, me gusta la pizza.

Para deformar completamente tu juego de relaciones, descarga el código del curso de esta página. Una vez que lo hayas descomprimido, encontrarás un directorio `start/` con el código que ves aquí. Echa un vistazo al práctico archivo`README.md` para ver todos los detalles de la configuración. El último paso será abrir un terminal, navegar por el proyecto y ejecutar: `symfony serve`. A veces lo ejecuto con `-d`, para que haga su trabajo en segundo plano, pero hoy lo ejecutaré en primer plano.

```terminal
symfony serve
```

## Hola, servidor y registros de Tailwind

Un efecto secundario útil de la ejecución en primer plano es que podemos ver todos los registros, aunque puedes verlos en cualquier momento ejecutando `symfony server:log`. Este proyecto utiliza Tailwind CSS y puedes ver cómo se descarga Tailwind y se construye en segundo plano. Una vez hecho esto, voy a desplazarme hacia arriba y hacer clic en el enlace para lanzar nuestra aplicación: ¡Starshop!

## Introducción a Starshop

Starshop se dedica a reparar naves, una solución integral para todos tus problemas con las naves espaciales, porque nadie quiere flotar por el espacio intergaláctico con una ducha rota. Qué asco. Todas estas naves espaciales proceden directamente de la base de datos. Si navegas hasta `src/Entity/`, encontrarás nuestra única entidad brillante: `Starship`.

[[[ code('bc4c539663') ]]]

## Pasos siguientes: Seguimiento de piezas de naves

Es hora de animar las cosas rastreando las piezas de una nave y su coste. Luego asignaremos cada pieza a una nave de la base de datos.
