# Volver a añadir addDroid(): ¡Oculta esa Entidad Unida!

Puede que te estés preguntando

> Espera, ¿realmente necesito crear un objeto `StarshipDroid` cada
> vez que quiera simplemente asignar un droide a una nave estelar?

Creo que es demasiado trabajo. ¿Podríamos volver a los viejos tiempos en los que podíamos simplemente llamar a `$ship->addDroid($droid)`?

¡Sí! Esto no funcionará todavía, ¡pero eso nunca nos ha detenido antes! Carga los accesorios:

```terminal-silent
symfony console doctrine:fixtures:load
```

¡Ay!

> Propiedad indefinida: `App\Entity\Starship::$droids`

No es exactamente una sorpresa, ya que estamos llamando a la propiedad `droids`, muerta hace tiempo, en `addDroid()`.

## Rehacer addDroid()

Lo primero en nuestra lista es comprobar si el `Starship` ya tiene el droide en cuestión. Para ello, cambia la propiedad para que utilice el método `getDroids()`. Pero espera, `$this->getDroids()->add()` no va a ser suficiente. En su lugar, vamos a arremangarnos y crear la entidad de unión aquí mismo: `$starshipDroid = new StarshipDroid()`
`$starshipDroid->setDroid($droid)` y `$starshipDroid->setStarship($this)`:

[[[ code('a0634b7760') ]]]

Hemos establecido el lado propietario de la relación, pero vamos a sincronizar también el otro lado. Podemos hacerlo llamando a `$droid->starshipDroids->add($starshipDroid)`. 
Dale otra vuelta a las fijaciones:

```terminal-silent
symfony console doctrine:fixtures:load
```

## Persistir en cascada

Ajá, un nuevo error. Éste es bastante común en Doctrine, aunque no siempre fácil de entender:

> Se ha encontrado una nueva entidad a través de la relación `Starship#starshipDroids`
> que no estaba configurada para persistir en cascada para la entidad `StarshipDroid`.

Es una forma muy elegante de decir que hemos creado un nuevo objeto `StarshipDroid`y le hemos dicho a Doctrine que persista su relación `Starship`. Pero nunca le hemos dicho a Doctrine que persista el propio objeto `StarshipDroid`.

Aquí está el problema: no tenemos acceso al gestor de entidades. Así que no podemos decir simplemente `$entityManager->persist($starshipDroid)`. En su lugar, vamos a apoyarnos en algo llamado `cascade=['persist']`, en lo que me sumergiré ahora mismo.
