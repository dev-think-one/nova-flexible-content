<?php

namespace NovaFlexibleContent\Layouts\LayoutTraits;

use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;

trait HasFieldsCollection
{
    /**
     * The layout's registered fields.
     */
    protected ?FieldCollection $fields = null;

    /**
     * Provided by developer fields.
     */
    protected function fields(): array
    {
        return $this->fields ? $this->fields->all() : [];
    }

    /**
     * Retrieve the layout's fields as a collection.
     */
    public function fieldsCollection(): FieldCollection
    {
        $fields = $this->fields;
        if(!$fields || $fields->isEmpty()) {
            $fields = $this->fields();
        }

        return FieldCollection::make($fields);
    }

    /**
     * Filter the layout's fields for detail view.
     */
    public function filterForDetail(NovaRequest $request, mixed $resource): static
    {
        $this->fields = $this->fieldsCollection()->filterForDetail($request, $resource);

        return $this;
    }
}
