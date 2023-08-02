<?php

namespace NovaFlexibleContent\Layouts\LayoutTraits;

use Laravel\Nova\Contracts\Deletable;
use Laravel\Nova\Contracts\Storable;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use NovaFlexibleContent\Flexible;
use NovaFlexibleContent\Layouts\Layout;

trait HasRemoveCallback
{
    /**
     * The callback to be called when this layout removed.
     */
    protected ?\Closure $removeCallbackMethod = null;

    /**
     * The method to call when this layout removed.
     */
    public function fireRemoveCallback(Flexible $flexibleField, NovaRequest $request, $model)
    {
        $removeCallbackMethod = $this->removeCallbackMethod ?? $this->defaultRemoveCallback();

        return call_user_func_array($removeCallbackMethod, [$flexibleField, $this, $request, $model]);
    }

    /**
     * The default behaviour when removed.
     *
     * TODO: confusing code - should be reworked
     */
    protected function defaultRemoveCallback(): \Closure
    {

        return function (Flexible $flexible, Layout $layout, NovaRequest $request, $model) {
            $layout->fieldsCollection()
                ->each(function (Field $field) use ($layout, $request, $model) {
                    if ($field instanceof Flexible) {
                        $field->resolve($layout);
                        $field->groups()->fireRemoveCallback($field, $request, $model);
                    } elseif ($field instanceof Storable
                        && $field instanceof Deletable
                        && $field->isPrunable()
                        && property_exists($field, 'deleteCallback')
                    ) {
                        $field->value = $layout->getAttribute($field->attribute);
                        call_user_func(
                            $field->deleteCallback,
                            $request,
                            $model,
                            $field->getStorageDisk(),
                            $field->getStoragePath()
                        );
                    }
                });
        };
    }
}
