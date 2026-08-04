<?php

namespace App\Tests\Form\DataTransformer;

use App\Form\DataTransformer\TagsToStringTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class TagsToStringTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $transformer = new TagsToStringTransformer();

        $this->assertEquals($transformer->transform(['flagship', 'cargo']), 'flagship, cargo');
        $this->assertEquals($transformer->transform([]), '');
        $this->assertEquals($transformer->transform(null), '');
    }

    public function testReverseTransform(): void
    {
        $transformer = new TagsToStringTransformer();

        $this->assertEquals($transformer->reverseTransform('flagship, cargo'), ['flagship', 'cargo']);
        $this->assertEquals($transformer->reverseTransform('flagship ,   stealth , , '), ['flagship', 'stealth']);
        $this->assertEquals($transformer->reverseTransform(''), []);
        $this->assertEquals($transformer->reverseTransform(null), []);

        // Unknown tags should throw a TransformationFailedException
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessageIsOrContains('"unknown" is not a known tag');
        $transformer->reverseTransform('flagship, unknown');
    }
}
