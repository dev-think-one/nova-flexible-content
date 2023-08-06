<?php

namespace NovaFlexibleContent\Http;

use Illuminate\Support\Collection;
use Laravel\Nova\Http\Requests\NovaRequest;

class ScopedRequest extends NovaRequest
{
    /**
     * The group's key.
     *
     * @var string|null
     */
    public ?string $group = null;

    /**
     * The original file input attributes.
     */
    protected ?Collection $fileAttributes = null;

    /**
     * Create a copy of the given request, only containing the group's input
     *
     * @param NovaRequest $from
     * @param  array  $attributes
     * @param  string  $group
     * @return static
     */
    public static function scopeFrom(NovaRequest $from, array $attributes, string $group): static
    {
        return parent::createFrom($from)->scopeInto($group, $attributes);
    }

    /**
     * Alter the request's input for given group key & attributes
     *
     * @param  string  $group
     * @param  array  $attributes
     * @return static
     */
    public function scopeInto(string $group, array $attributes): static
    {
        [$input, $files] = $this->getScopeState($group, $attributes);

        $input['_method']       = $this->input('_method');
        $input['_retrieved_at'] = $this->input('_retrieved_at');

        $this->handleScopeFiles($files, $input, $group);

        $this->replace($input);
        $this->files->replace($files);

        return $this;
    }

    /**
     * Get the target scope configuration array
     *
     * @param  string  $group
     * @param  array  $attributes
     * @return array
     */
    protected function getScopeState(string $group, array $attributes = []): array
    {
        $input = [];
        $files = [];

        foreach ($attributes as $attribute => $value) {
            $attribute = FlexibleAttribute::make($attribute, $group, is_array($value));

            // Sub-objects could contain files that need to be kept
            if ($attribute->isAggregate()) {
                $files                   = array_merge($files, $this->getNestedFiles($value, $attribute->group));
                $input[$attribute->name] = $value;

                continue;
            }

            // Register Files
            if ($attribute->isFlexibleFile($value)) {
                $files[] = $attribute->getFlexibleFileAttribute($value);

                continue;
            }

            // Register regular attributes
            $input[$attribute->name] = $value;
        }

        return [$input, array_filter($files)];
    }

    /**
     * Get nested file attributes from given array
     *
     * @param  array  $iterable
     * @param  null|string  $group
     * @return array
     */
    protected function getNestedFiles(array $iterable, ?string $group = null): array
    {
        $files = [];
        $key   = $this->isFlexibleStructure($iterable) ? $iterable['key'] : $group;

        foreach ($iterable as $original => $fieldKeyName) {
            if (is_array($fieldKeyName)) {
                $files = array_merge($files, $this->getNestedFiles($fieldKeyName, $key));

                continue;
            }

            $attribute = FlexibleAttribute::make($original, $group);

            if (!$attribute->isFlexibleFile($fieldKeyName)) {
                continue;
            }

            $files[] = $attribute->getFlexibleFileAttribute($fieldKeyName);
        }

        return array_filter($files);
    }

    /**
     * Get all useful files from current files list
     *
     * @param  array  $files
     * @param  array  $input
     * @param  string  $group
     * @return void
     */
    protected function handleScopeFiles(&$files, &$input, $group): void
    {
        $attributes = collect($files)->keyBy('original');

        $this->fileAttributes = $attributes->mapWithKeys(function ($attribute, $key) {
            return [$attribute->name => $key];
        });

        $scope = [];

        foreach ($this->getFlattenedFiles() as $attribute => $file) {
            if (!($target = $attributes->get($attribute))) {
                continue;
            }

            if (!$target->group || $target->group !== $group) {
                $scope[$target->original] = $file;

                continue;
            }

            $target->setDataIn($scope, $file);
            $target->unsetDataIn($input);
        }

        $files = $scope;
    }

    /**
     * Get the request's files as a "flat" (1 dimension) array
     *
     * @param null $iterable
     * @param FlexibleAttribute|null $original
     * @return array<FlexibleAttribute>
     */
    protected function getFlattenedFiles($iterable = null, FlexibleAttribute $original = null): array
    {
        $files = [];

        foreach ($iterable ?? $this->files->all() as $key => $value) {
            $attribute = $original ? $original->nest($key) : FlexibleAttribute::make($key);

            if (!is_array($value)) {
                $files[$attribute->original] = $value;

                continue;
            }

            $files = array_merge($files, $this->getFlattenedFiles($value, $attribute));
        }

        return array_filter($files);
    }

    /**
     * Check if the given array represents a flexible group
     *
     * @param  array  $iterable
     * @return bool
     */
    protected function isFlexibleStructure(array $iterable): bool
    {
        $keys = array_keys($iterable);

        return  in_array('layout', $keys, true)
            && in_array('key', $keys, true)
            && in_array('attributes', $keys, true);
    }

    public function isFileAttribute($name): bool
    {
        return (bool) $this->fileAttributes?->has($name);
    }

    public function getFileAttribute($name): mixed
    {
        return $this->fileAttributes?->get($name);
    }
}
