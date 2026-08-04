<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class TagsToStringTransformer implements DataTransformerInterface
{
    public const array KNOWN_TAGS = [
        'flagship',
        'medical',
        'stealth',
        'cargo',
        'science',
    ];

    public function transform(mixed $value): string
    {
        if (null === $value) {
            return '';
        }

        return implode(', ', $value);
    }

    public function reverseTransform(mixed $value): array
    {
        if (null === $value || '' === trim($value)) {
            return [];
        }

        $tags = [];
        foreach (array_filter(array_map('trim', explode(',', $value))) as $input) {
            $canonical = strtolower($input); // transformation, this justifies the use of TransformationFailedException
            if (!in_array($canonical, self::KNOWN_TAGS, true)) {
                throw new TransformationFailedException(sprintf('"%s" is not a known tag. Known tags: %s.', $input, implode(', ', self::KNOWN_TAGS)));
            }
            $tags[] = $canonical;
        }

        return array_values(array_unique($tags));
    }
}
