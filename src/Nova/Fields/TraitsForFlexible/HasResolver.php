<?php

namespace NovaFlexibleContent\Nova\Fields\TraitsForFlexible;

use NovaFlexibleContent\Value\DefaultResolver;
use NovaFlexibleContent\Value\Resolver;

trait HasResolver
{
    /**
     * The field's value setter & getter
     */
    protected Resolver $resolver;

    /**
     * Initialise trait in Flexible constructor.
     */
    protected function initializeHasResolver(): void
    {
        $this->setResolver(DefaultResolver::class);
    }

    /**
     * Set the field's resolver.
     *
     * @param Resolver|class-string<Resolver> $resolver
     * @return static
     */
    public function setResolver(Resolver|string $resolver): static
    {
        if (is_string($resolver)
            && is_a($resolver, Resolver::class, true)) {
            $resolver = new $resolver();
        }

        if (!($resolver instanceof Resolver)) {
            throw new \InvalidArgumentException('Resolver Class does not implement ResolverInterface.');
        }

        $this->resolver = $resolver;

        return $this;
    }

    /**
     * @deprecated
     */
    public function resolver(Resolver|string $resolver): static
    {
        return $this->setResolver($resolver);
    }
}
