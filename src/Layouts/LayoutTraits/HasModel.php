<?php

namespace NovaFlexibleContent\Layouts\LayoutTraits;

use Illuminate\Database\Eloquent\Model;
use NovaFlexibleContent\Layouts\Layout;

/**
 * @deprecated
 */
trait HasModel
{
    /**
     * The parent model instance
     */
    protected Model|Layout|null $model = null;

    /** @deprecated */
    public function model(): Layout|Model|null
    {
        return $this->model;
    }

    /**
     * @deprecated I have no Idea where this used, and why we should keep it?
     */
    public function setModel(Model|Layout|null $model): static
    {
        $this->model = $model;

        return $this;
    }
}
