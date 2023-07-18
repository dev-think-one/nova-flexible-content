<?php

namespace NovaFlexibleContent\Nova\Fields\TraitsForFlexible;

use NovaFlexibleContent\Contracts\ResolverInterface;
use NovaFlexibleContent\Value\Resolver;

trait HasResolver
{
    /**
     * The field's value setter & getter
     */
    protected ResolverInterface $resolver;

    /**
     * Initialise trait in Flexible constructor.
     */
    protected function initializeHasResolver(): void
    {
        $this->setResolver(Resolver::class);
    }

    /**
     * Set the field's resolver.
     *
     * @param ResolverInterface|class-string<ResolverInterface> $resolver
     * @return static
     */
    public function setResolver(ResolverInterface|string $resolver): static
    {
        if (is_string($resolver)
            && is_a($resolver, ResolverInterface::class, true)) {
            $resolver = new $resolver();
        }

        if (!($resolver instanceof ResolverInterface)) {
            throw new \InvalidArgumentException('Resolver Class does not implement ResolverInterface.');
        }

        $this->resolver = $resolver;

        return $this;
    }

    /**
     * @deprecated
     */
    public function resolver(ResolverInterface|string $resolver): static
    {
        return $this->setResolver($resolver);
    }
}
