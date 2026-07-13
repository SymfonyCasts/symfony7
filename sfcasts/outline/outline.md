# Symfony 8 Forms Advanced Outline

## Prerequisites
- Fork TutsHero last step of https://github.com/SymfonyCasts/symfony7/tree/ep6-security
- Upgrade PHP version to `>=8.4.1` in composer.json
- Upgrade Symfony to the latest 8.1 w/ deps: w/ `symfony composer update`
- Upgrade recipes
- Apply missing TutsHero steps from ep6-security at the moment of forking 

## DTO
- Let's open `src/Entity/StarshipPart`
- We say name and price are required according to our validation rules
- But in the database, we do not allow nullable (`nullable=false`)
- Let's be honest and fix property types in the PHP code
    - Change to: `private string $name;`
    - And to: `private int $price;`
    - Not it perfectly reflects the DB setup
- Fix setters and getters:
    - Change to `public function getName(): string`
    - And to:  `public function getPrice(): int`
    - Now for clarity we can add `public function __construct(string $name, int $price)`
- But now if you go to /admin/starship-part/new - form will fail on submit
- For forms, we still need to allow invalid states
- For this, create DTO class `src/Dto/StarshipPartDto.php`
    - Make it `final`
    - Copy all the properties from the entity including asserts
    - And change them to `public`
    - Drop ORM annotations
    - And allow `null` for `name` and `price` properties
    - For this, change to: `public ?string $name = null;`
    - And to: `public ?int $price = null;`
- Back to `StarshipPartType` form
    - Set `'data_class' => StarshipPartDto::class,`
- Now back to `AdminController::newStarshipPart()`
    - This `$form->getData()` will return our DTO object now
    - We should map it to the entity before persisting
    - We can do it manually like...
    - Or we can leverage Symfony's ObjectMapper to do it for us
- First, install it with `symfony composer require symfony/object-mapper`
    - Now, inject it in `newStarshipPart()` as `ObjectMapperInterface $objectMapper`
    - Inside `if`, I will drop `$part = $form->getData();` w/ the related PHPDoc
    - Instead, add `$part = $objectMapper->map($form->getData(), StarshipPart::class);`
    - PhpStorm should already know it's a `StarshipPart` object now
    - If your IDE doesn't know about it, you can leave a PHPDoc for `$part`
- Now go refresh the page, fill in the form, and submit it
- Go search the created part - here it is! It worked as before
- But now our PHP code is more correct and reflects the database setup
- 
