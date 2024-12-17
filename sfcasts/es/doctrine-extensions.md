# Auto Slug y Timestamps con las extensiones Doctrine

Hemos añadido tres nuevos campos a `Starship`: `slug`,`updatedAt`, y `createdAt`. ¡Pero ahora nuestros accesorios no se cargan! La razón es sencilla: los 3 campos son obligatorios en la base de datos, pero `StarshipFactory` no los establece. Podríamos añadirlos, pero no deberíamos tener que hacerlo. En un mundo perfecto `slug` se generaría automáticamente a partir de `name`, `updatedAt`: se establecería a la hora actual cuando la entidad cambiara y `createdAt`: se establecería a la hora actual cuando la entidad se creara.

¡Y existe un paquete que puede hacer esto `DoctrineExtensions`! En tu terminal, ejecuta:

```terminal
composer require stof/doctrine-extensions-bundle
```

Este bundle tiene una receta... pero no se considera oficial. Es una receta de terceros, o contrib. Normalmente están bien, pero ten en cuenta que las recetas contrib son añadidas por la comunidad y no controladas por el equipo central de Symfony.

Desplázate hacia arriba para ver lo que tenemos. Los paquetes más importantes son `gedmo/doctrine-extensions`, que contiene la lógica real, y `stof/doctrine-extensions-bundle`, que la integra con Symfony. No tienes que preocuparte de lo demás.

Ejecuta

```terminal
git status
```

para ver qué ha añadido la receta. Ha configurado el bundle y ha añadido un nuevo archivo de configuración ¡Genial! Para este bundle, necesitamos editar esta configuración para habilitar las extensiones, donde cada extensión es como un superpoder para tus entidades.

Abre `config/packages/stof_doctrine_extensions.yaml`. Debajo de `default_local`añade una nueva clave: `orm:`, luego `default:` y dentro de ella, activa 2 superpoderes, quiero decir extensiones:`timestampable: true` y `sluggable: true`.

Ahora están activados en general, pero necesitamos un poco más de configuración para que cobren vida para la entidad `Starship`. Vuelve a abrirla.

Sobre la propiedad `$slug`, añade un nuevo atributo: `#[Slug]`, importando la clase de`Gedmo\Mapping\Annotation`. Dentro, añade `fields:` y establécelo en una matriz que contenga`name`. Esto indica a la extensión que genere el slug a partir de la propiedad `$name`cuando se persista la entidad por primera vez.

Encima de `$updatedAt`, añade `#[Timestampable]` con `on: 'update'` para que sepa que debe establecer este campo con la hora actual al actualizar la entidad. Lo mismo para`$createdAt`, pero con `on: 'create'`.

¡Vamos a probarlo! En tu terminal, ejecuta:

```terminal
symfony console doctrine:fixtures:load
```

Y... ¡funcionó! Ejecuta nuestra consulta SQL para ver los valores:

```terminal-silent
symfony console doctrine:query:sql 'SELECT name, slug, updated_at, created_at FROM starship'
```

¡Sí! Nuestro `slug` se genera a partir del `name`, y `updatedAt` y `createdAt` se establecen con la marca de tiempo de cuando se creó la entidad. Doctrine considera el guardado inicial también como una actualización: por eso `updatedAt` y `createdAt` tienen el mismo valor.

Desplázate un poco hacia abajo. Fíjate en que estas babosas tienen como sufijo `-1`? Esto se debe a que nuestro campo `slug` es único, pero nuestro `name` no lo es. Tenemos algunos campos estelares, como `Lunar Marauder` aquí, que tienen el mismo nombre. La extensión slug es lo suficientemente inteligente como para detectar esto, y añadir automáticamente un sufijo numérico (`-1`, `-2`, etc.) para mantenerlos únicos. ¡Inteligente!

Ahora que tenemos un slug único y legible por humanos para nuestras naves estelares, vamos a utilizarlo en lugar de este feo `id` en nuestras URL. ¡También utilizaremos algo llamado Resolvedores de valores de controlador para hacer que nuestros controladores sean de alta tecnología! ¡Eso a continuación! 
