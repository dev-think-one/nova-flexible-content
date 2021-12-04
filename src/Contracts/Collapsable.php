<?php

namespace Whitecube\NovaFlexibleContent\Contracts;

interface Collapsable
{
    public function setCollapsed(bool $collapsed): static;

    public function isCollapsed(): bool;
}
