<?php

namespace NovaFlexibleContent\Nova\Fields\TraitsForFlexible;

use Illuminate\Database\Eloquent\Model;

/**
 * Hmm, I can't find place where this original model really useful.
 * We can use resolver for all functions, also data keeper can be not really model, but other class.
 * So I believe in future this functionality should be removed at all.
 * If you know place where this code required please write to me yaroslav.georgitsa@gmail.com.
 *
 * @deprecated
 */
trait HasOriginalModel
{
    /**
     * @deprecated
     */
    public static Model|null $model;

    /**
     * Registers a reference to the origin model for nested & contained fields.
     *
     * @deprecated
     */
    protected function registerOriginModel($model): static
    {
        /** @psalm-suppress UndefinedClass */
        $isPageTemplate = is_a($model, "\Whitecube\NovaPage\Pages\Template");
        if (is_a($model, \Laravel\Nova\Resource::class)) {
            $model = $model->model();
        } elseif ($isPageTemplate) {
            /** @psalm-suppress UndefinedClass */
            $model = $model->getOriginal();
        }

        if (is_a($model, Model::class)) {
            static::$model = $model;
        }

        return $this;
    }

    /**
     * Return the previously registered origin model.
     *
     * @deprecated
     */
    public static function getOriginModel(): ?Model
    {
        return static::$model;
    }
}
