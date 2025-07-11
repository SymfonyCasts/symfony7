# Acceder a los datos de un ManyToMany

Objetivo realmente sencillo: imprimir todos los droides asignados a un `Starship`. Si te has sentido cómodo con la relación `OneToMany` de `Starship` a sus partes, ¡esto te va a encantar!

Abre la plantilla de la página de muestra `Starship`:`templates/starship/show.html.twig`. Robaré la etiqueta `h4` y `p` para`arrived at`, las pegaré a continuación, y cambiaré la `h4` por `Droids`. Borra `arrived at`... y rompe esa línea:

[[[ code('5cf6e65737') ]]]

Tenemos una variable `ship`, que es un objeto `Starship`. Y recuerda que tiene una propiedad `droids` y un método `getDroids()`. Así que, para`droid in ship.droids`. Esto llama al método `getDroids()`, y eso devuelve una colección de objetos `Droid`. Así que podemos decir `{{ droid.name }}`.

## El `loop.last`

Quiero comas, pero no una coma de más al final. Di : 
`{% if not loop.last %}, {% endif %}`. Hay formas más elegantes de hacerlo, pero de momento hazlo sencillo.

Si no hay ningún droide, utiliza una etiqueta `else` y di`No droids on board (clean up your own mess)`. ¡Grosero!

[[[ code('c379a4fd9f') ]]]

## Droides en la página de inicio

En la página de inicio, también queremos mostrar los droides. Abre la plantilla: `templates/main/homepage.html.twig`. Justo después de`parts`, añade otro div con `Droids: {{ ship.droidNames ?: 'none' }}`:

[[[ code('237c7386af') ]]]

## El método inteligente

Podríamos volver a utilizar nuestra coma de `loop.last `, pero necesitamos los nombres de los droides en dos sitios, así que vamos a añadir un método inteligente para ello en la clase `Starship`. Podría ir en cualquier sitio, pero lo pondré al final con los otros métodos de droides. Crea un `public function getDroidNames(): string`. Para devolver una cadena de nombres de droides separados por comas, mira esto: return`implode(', ', $this->droids->map(fn(Droid $droid) => $droid->getName())->toArray())`:

[[[ code('3a5eaaa16e') ]]]

Vaya, ¡qué bocado! Vamos a desglosarlo:

Primero, `$this->droids` es nuestra colección de objetos `Droid`. Segundo, `map()` aplica una función a cada `Droid` de la colección. Tercero, `fn(Droid $droid) => $droid->getName()`es una forma hipster de decir:

> Dame el nombre de cada droide

Cuarto, `toArray()` convierte la colección en una matriz para poder utilizarla con`implode()`. Por último, `implode(', ', ...)` toma esa matriz de nombres y la convierte en una cadena separada por comas.

Ahora que tenemos un método `getDroidNames()`, podemos decir`{{ ship.droidNames ?: 'none' }}`.

¡Ya está! Actualiza... y disfruta de los nombres de los droides en la página de inicio.

A continuación: utilicemos Foundry para establecer la relación ManyToMany en los accesorios. ¡Otro lugar en el que brilla Foundry!
