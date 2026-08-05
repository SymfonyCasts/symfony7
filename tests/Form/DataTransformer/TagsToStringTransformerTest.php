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

        $this->assertSame('flagship, cargo', $transformer->transform(['flagship', 'cargo']));
        $this->assertSame('', $transformer->transform([]));
        $this->assertSame('', $transformer->transform(null));
    }

    public function testReverseTransform(): void
    {
        $transformer = new TagsToStringTransformer();

        $this->assertSame(['flagship', 'cargo'], $transformer->reverseTransform('flagship, cargo'));
        $this->assertSame(['flagship', 'stealth'], $transformer->reverseTransform('flagship ,   stealth , , '));
        $this->assertSame([], $transformer->reverseTransform(''));
        $this->assertSame([], $transformer->reverseTransform(null));

        // Unknown tags should throw a TransformationFailedException
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessageIsOrContains('"unknown" is not a known tag');
        $transformer->reverseTransform('flagship, unknown');
    }
}
