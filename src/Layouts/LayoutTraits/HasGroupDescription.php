<?php

namespace NovaFlexibleContent\Layouts\LayoutTraits;

trait HasGroupDescription
{
    /**
     * @deprecated Please use "fieldUsedForDescription"
     */
    protected ?string $tagInfoFrom = null;

    /**
     * Display description for specific group in array of groups with same layout.
     *
     * @var string|null
     */
    protected ?string $fieldUsedForDescription = null;

    /**
     * @param string|null $fieldUsedForDescription
     *
     * @return static
     */
    public function useFieldForDescription(?string $fieldUsedForDescription = null): static
    {
        $this->fieldUsedForDescription = $fieldUsedForDescription;

        return $this;
    }


    /**
     * Returns field name what used for describe group.
     *
     * @return string|null
     */
    public function fieldUsedForDescription(): ?string
    {
        return $this->fieldUsedForDescription ?? $this->tagInfoFrom;
    }
}
