<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class TagsToStringTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): string
    {
        return implode(', ', $value);
    }

    public function reverseTransform(mixed $value): array
    {
        if (null === $value || '' === trim($value)) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $value)));
    }
}
