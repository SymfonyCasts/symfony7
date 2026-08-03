<?php

namespace App\Tests\Form\DataTransformer;

use App\Form\DataTransformer\TagsToStringTransformer;
use PHPUnit\Framework\TestCase;

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
    }
}
