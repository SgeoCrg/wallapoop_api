<?php

namespace App\Serializer;

use App\Entity\MediaObject;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Dependencyinjection\Attribute\Autowire;
use Vich\UploaderBundle\Storage\StorageInterface;

class MediaObjectNormalizer implements NormalizerInterface
{
    private const ALREADY_CALLED = 'MEDIA_OBJECT_NORMALIZER_ALREADY_CALLER';

    public function __construct(
        #[Autowire(service: 'api_platform.jsonld.normalizer.item')]
        private readonly NormalizerInterface $normalizer,
        private readonly StorageInterface $storage
    ){
    }

    public function normalize($object, ?string $format = null, array $context = []):
    array|string|int|float|bool|\ArrayObject|null
    {
        $context[self::ALREADY_CALLED] = true;
        $object->contentUrl = $this->storage->resolveUri($object, 'file');

        return $this->normalizer->normalize($object, $format,  $context);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if(isset($context[self::ALREADY_CALLED])) return false;

        return $data instanceof MediaObject;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            mediaObject::class => true,
        ];
    }
}