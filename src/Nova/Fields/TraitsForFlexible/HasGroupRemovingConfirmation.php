<?php

namespace NovaFlexibleContent\Nova\Fields\TraitsForFlexible;

use Illuminate\Support\Arr;

trait HasGroupRemovingConfirmation
{

    /**
     * Display confirmation modal before removing group.
     */
    public function withGroupRemovingConfirmation(?string $modalMessage = null, ?string $yesButtonText = null, ?string $noButtonText = null): static
    {
        return $this->withMeta([
            'confirmRemove'        => true,
            'confirmRemoveMessage' => $modalMessage,
            'confirmRemoveYes'     => $yesButtonText,
            'confirmRemoveNo'      => $noButtonText,
        ]);
    }

    /**
     * Do not display confirmation modal before removing group.
     */
    public function withoutGroupRemovingConfirmation(): static
    {
        Arr::forget($this->meta, [
            'confirmRemove',
            'confirmRemoveMessage',
            'confirmRemoveYes',
            'confirmRemoveNo',
        ]);

        return $this;
    }

    /**
     * @deprecated
     */
    public function confirmRemove(?string $label = null, ?string $yes = null, ?string $no = null): static
    {
        return $this->withGroupRemovingConfirmation($label, $yes, $no);
    }

}
