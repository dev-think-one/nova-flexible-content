<?php

namespace NovaFlexibleContent\FileAdder;

use Illuminate\Database\Eloquent\Model;
use NovaFlexibleContent\FileAdder\FileAdder as NewFileAdder;
use Spatie\MediaLibrary\MediaCollections\FileAdderFactory as OriginalFileAdderFactory;

/**
 * @psalm-suppress UndefinedClass
 */
class FileAdderFactory extends OriginalFileAdderFactory
{
    /**
     * @param \Illuminate\Database\Eloquent\Model                        $subject
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string|null                                                $suffix
     *
     * @return \Spatie\MediaLibrary\MediaCollections\FileAdder
     */
    public static function create(Model $subject, $file, string $suffix = null): \Spatie\MediaLibrary\MediaCollections\FileAdder
    {
        return app(NewFileAdder::class)
            ->setSubject($subject)
            ->setFile($file)
            ->setMediaCollectionSuffix($suffix);
    }
}
