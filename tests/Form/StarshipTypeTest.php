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

    public function testIsEditMode(): void
    {
        $starship = new Starship();
        $form = $this->factory->create(StarshipType::class, $starship);

        // The status field should not be present for a new starship
        $this->assertFalse($form->has('status'));
        $this->assertFalse($form->get('slug')->isDisabled());

        // Now simulate editing an existing starship
        $starship->setId(1); // Simulate that the starship has an ID (i.e., it's being edited)
        $form = $this->factory->create(StarshipType::class, $starship);

        // The status field should now be present
        $this->assertTrue($form->has('status'));
        $this->assertTrue($form->get('slug')->isDisabled());

        $form = $this->factory->create(StarshipType::class, $starship, [
            'is_admin' => true,
        ]);
        $this->assertFalse($form->get('slug')->isDisabled());
    }
}
