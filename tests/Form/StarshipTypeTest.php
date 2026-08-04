<?php

namespace App\Tests\Form;

use App\Entity\Starship;
use App\Form\EmbeddedStarshipPartType;
use App\Form\StarshipType;
use App\Form\Type\CreditsType;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

#[AllowMockObjectsWithoutExpectations]
class StarshipTypeTest extends TypeTestCase
{
    public function testSubmitValidTags(): void
    {
        $starship = new Starship();
        $form = $this->factory->create(StarshipType::class, $starship);

        $form->submit([
            'name' => 'Nostromo',
            'tags' => 'flagship, cargo',
        ]);

        // Ensure there is no transformation failure on the field
        $this->assertTrue($form->get('tags')->isSynchronized());

        $this->assertSame('Nostromo', $starship->getName());
        $this->assertSame(['flagship', 'cargo'], $starship->getTags());
    }

    public function testSubmitInvalidTags(): void
    {
        $starship = new Starship();
        $form = $this->factory->create(StarshipType::class, $starship);

        $form->submit([
            'name' => 'Nostromo',
            'tags' => 'flagship, foobar',
        ]);

        // Ensure there is a transformation failure on the field
        $this->assertFalse($form->get('tags')->isSynchronized());

        $this->assertSame('Nostromo', $starship->getName());
        $this->assertSame([], $starship->getTags());
    }
}
