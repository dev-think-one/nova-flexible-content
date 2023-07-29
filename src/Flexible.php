<?php

namespace NovaFlexibleContent;

use Laravel\Nova\Contracts\Downloadable;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\SupportsDependentFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use NovaFlexibleContent\Http\ScopedRequest;
use NovaFlexibleContent\Layouts\GroupsCollection;
use NovaFlexibleContent\Layouts\Layout;
use NovaFlexibleContent\Layouts\LayoutsCollection as LayoutsCollection;
use NovaFlexibleContent\Nova\Fields\TraitsForFlexible\HasGroupRemovingConfirmation;
use NovaFlexibleContent\Nova\Fields\TraitsForFlexible\HasGroupsLimits;
use NovaFlexibleContent\Nova\Fields\TraitsForFlexible\HasLayoutsMenu;
use NovaFlexibleContent\Nova\Fields\TraitsForFlexible\HasOriginalModel;
use NovaFlexibleContent\Nova\Fields\TraitsForFlexible\HasPreset;
use NovaFlexibleContent\Nova\Fields\TraitsForFlexible\HasResolver;

class Flexible extends Field implements Downloadable
{
    use SupportsDependentFields;
    use HasResolver;
    use HasLayoutsMenu;
    use HasGroupsLimits;
    use HasGroupRemovingConfirmation;
    use HasPreset;
    use HasOriginalModel;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'flexible-content';

    /**
     * @inerhitDoc
     */
    public $showOnIndex = false;

    /**
     * The available layouts as collection.
     */
    protected LayoutsCollection $layouts;

    /**
     * The currently created layout groups.
     */
    protected GroupsCollection $groups;

    /**
     * All the validated attributes
     */
    protected static array $validatedKeys = [];

    /**
     * Create a fresh flexible field instance
     *
     * @param string $name
     * @param string|null $attribute
     * @param mixed|null $resolveCallback
     * @return void
     */
    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        $this->layouts = new LayoutsCollection();
        $this->groups  = new GroupsCollection();

        parent::__construct($name, $attribute, $resolveCallback);

        foreach (class_uses_recursive($this) as $trait) {

            if (method_exists($this, $method = 'initialize' . class_basename($trait))) {
                $this->{$method}();
            }
        }
    }

    /**
     * Get the field layouts.
     */
    public function layouts(): LayoutsCollection
    {
        return $this->layouts;
    }

    /**
     * Get the field groups.
     */
    public function groups(): GroupsCollection
    {
        return $this->groups;
    }

    /**
     * Register a new layout.
     *
     * @deprecated Please use alternative useLayout
     */
    public function addLayout(...$arguments): static
    {
        $count = count($arguments);

        if ($count > 1) {
            $layout = new Layout(...$arguments);
        } else {
            $layout = $arguments[0];
        }

        return $this->useLayout($layout);
    }

    /**
     * Register a new layout.
     */
    public function useLayout(Layout|string $layout): static
    {
        if (!is_a($layout, Layout::class, true)) {
            return $this;
        }

        if (is_string($layout)) {
            $layout = new $layout();
        }

        $this->registerLayout($layout);

        return $this;
    }

    /**
     * Push a layout instance into the layouts collection.
     */
    protected function registerLayout(Layout $layout)
    {
        $this->layouts->push($layout);

        return $this->withMeta(['layouts' => $this->layouts]);
    }

    /**
     * Resolve the field's value.
     *
     * @param mixed $resource
     * @param string|null $attribute
     * @return void
     */
    public function resolve($resource, $attribute = null)
    {
        $attribute = $attribute ?? $this->attribute;

        $this->registerOriginModel($resource);

        $this->buildGroups($resource, $attribute);

        $this->value = $this->resolveGroups($this->groups);
    }

    /**
     * Resolve the field's value for display on index and detail views.
     *
     * @param mixed $resource
     * @param string|null $attribute
     * @return void
     */
    public function resolveForDisplay($resource, $attribute = null)
    {
        $attribute = $attribute ?? $this->attribute;

        $this->registerOriginModel($resource);

        $this->buildGroups($resource, $attribute);

        $this->value = $this->resolveGroupsForDisplay($this->groups);
    }

    /**
     * Check showing on detail.
     *
     * @param NovaRequest $request
     * @param             $resource
     * @return bool
     */
    public function isShownOnDetail(NovaRequest $request, $resource): bool
    {
        $this->layouts = $this->layouts->each(function ($layout) use ($request, $resource) {
            $layout->filterForDetail($request, $resource);
        });

        return parent::isShownOnDetail($request, $resource);
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @param string $requestAttribute
     * @param object $model
     * @param string $attribute
     * @return null|\Closure
     */
    protected function fillAttribute(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        if (!$request->exists($requestAttribute)) {
            return;
        }

        $attribute = $attribute ?? $this->attribute;

        $this->registerOriginModel($model);

        $this->buildGroups($model, $attribute);

        $callbacks = GroupsCollection::make($this->syncAndFillGroups($request, $requestAttribute, $model));

        $this->reFillValue($model, $attribute);

        if ($callbacks->isEmpty()) {
            return;
        }

        return function () use ($callbacks) {
            $callbacks->each->__invoke();
        };
    }

    public function reFillValue($model, ?string $attribute = null): static
    {
        $attribute   = $attribute ?? $this->attribute;
        $this->value = $this->resolver->set($model, $attribute, $this->groups);

        return $this;
    }

    /**
     * Process an incoming POST Request
     */
    protected function syncAndFillGroups(NovaRequest $request, string $requestAttribute, $model): array
    {
        if (!($raw = $this->extractValueFromRequest($request, $requestAttribute))) {
            $this->fireRemoveCallbacks(GroupsCollection::make(), $request, $model);
            $this->groups = GroupsCollection::make();

            return [];
        }

        $callbacks = [];
        $newGroups = GroupsCollection::make($raw)->map(function ($item, $key) use ($request, &$callbacks) {
            $layout     = $item['layout'];
            $key        = $item['key'];
            $attributes = $item['attributes'];

            $group = $this->findGroup($key) ?? $this->newGroup($layout, $key);

            $group->setCollapsed((bool)($item['collapsed'] ?? false));
            $scope     = ScopedRequest::scopeFrom($request, $attributes, $key);
            $callbacks = array_merge($callbacks, $group->fill($scope));

            return $group;
        })->filter();

        $this->fireRemoveCallbacks($newGroups, $request, $model);

        $this->groups = $newGroups;

        return $callbacks;
    }

    /**
     * Fire's the remove callbacks on the layouts
     *
     * @param $newGroups GroupsCollection This should be (all) the new groups to bne compared against to find the
     *                   removed groups
     */
    protected function fireRemoveCallbacks(GroupsCollection $newGroups, NovaRequest $request, $model): static
    {
        $newGroupKeys = $newGroups->map(function ($item) {
            return $item->inUseKey();
        });

        $this->groups->filter(function ($item) use ($newGroupKeys) {
            // Return only removed groups.
            return !$newGroupKeys->contains($item->inUseKey());
        })->each(function (Layout $group) use ($request, $model) {
            if (method_exists($group, 'fireRemoveCallback')) {
                $group->fireRemoveCallback($this, $request, $model);
            }
        });

        return $this;
    }

    /**
     * Find the flexible's value in given request.
     */
    protected function extractValueFromRequest(NovaRequest $request, string $attribute): ?array
    {
        $value = $request->input($attribute);

        if (!$value) {
            return null;
        }

        if (!is_array($value)) {
            throw new \Exception('Unable to parse incoming Flexible content, data should be an array.');
        }

        return $value;
    }

    /**
     * Resolve all contained groups and their fields.
     */
    protected function resolveGroups(GroupsCollection $groups): GroupsCollection
    {
        return $groups->map(function ($group) {
            return $group->getResolved();
        });
    }

    /**
     * Resolve all contained groups and their fields for display on index and
     * detail views.
     */
    protected function resolveGroupsForDisplay(GroupsCollection $groups): GroupsCollection
    {
        return $groups->map(function ($group) {
            return $group->getResolvedForDisplay();
        });
    }

    /**
     * Define the field's actual layout groups (as "base models") based
     * on the field's current model & attribute
     *
     * @param mixed $resource
     * @param string $attribute
     * @return GroupsCollection
     */
    protected function buildGroups($resource, string $attribute): GroupsCollection
    {
        return $this->groups = $this->resolver->get($resource, $attribute, $this->layouts);
    }

    public function findGroup(string $groupKey): ?Layout
    {
        return $this->groups->first(fn (Layout $group) => $group->isUseKey($groupKey));
    }

    public function findGroupRecursive(string $groupKey): ?Layout
    {
        /** @var Layout $group */
        foreach ($this->groups as $group) {
            if ($group->isUseKey($groupKey)) {
                return $group;
            }
            if ($foundSubsequenceGroup = $group->findFlexibleGroupRecursive($groupKey)) {
                return $foundSubsequenceGroup;
            }
        }

        return null;
    }

    /**
     * @return bool - true if group found and false if not found.
     */
    public function setAttributeRecursive(string $groupKey, string $fieldKey, mixed $newValue = null): bool
    {
        $isUpdated = false;

        $this->groups
            ->each(function (Layout $group) use ($groupKey, $fieldKey, $newValue, &$isUpdated) {
                if ($group->isUseKey($groupKey)) {
                    $group->setAttribute($fieldKey, $newValue);
                    $isUpdated = true;

                    // Break loop
                    return false;
                }

                if ($group->findGroupRecursiveAndSetAttribute($groupKey, $fieldKey, $newValue)) {
                    $isUpdated = true;

                    // Break loop
                    return false;
                }
            });

        return $isUpdated;
    }

    /**
     * Create a new group based on its key and layout.
     */
    protected function newGroup(string $layout, string $key): ?Layout
    {
        return $this->layouts->find($layout)?->duplicate($key);
    }

    /**
     * Get the creation rules for this field & its contained fields.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function getCreationRules(NovaRequest $request): array
    {
        return array_merge_recursive(
            parent::getCreationRules($request),
            $this->getFlexibleRules($request, 'creation')
        );
    }

    /**
     * Get the update rules for this field & its contained fields.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     * @throws \Exception
     */
    public function getUpdateRules(NovaRequest $request): array
    {
        return array_merge_recursive(
            parent::getUpdateRules($request),
            $this->getFlexibleRules($request, 'update')
        );
    }

    /**
     * Retrieve contained fields rules and assign them to nested array attributes.
     *
     * @param NovaRequest $request
     * @param string|null $type
     * @return array
     * @throws \Exception
     */
    protected function getFlexibleRules(NovaRequest $request, ?string $type = null): array
    {

        if (!($value = $this->extractValueFromRequest($request, $this->attribute))) {
            return [];
        }

        $rules = $this->generateRules($request, $value, $type);

        if (!is_a($request, ScopedRequest::class)) {
            // We're not in a nested flexible, meaning we're
            // assuming the field is located at the root of
            // the model's attributes. Therefore, we should now
            // register all the collected validation rules for later
            // reference (see Http\TransformsFlexibleErrors).
            static::registerValidationKeys($rules);

            // Then, transform the rules into an array that's actually
            // usable by Laravel's Validator.
            $rules = array_map(fn ($field) => $field['rules'], $rules);
        }

        return $rules;
    }

    /**
     * Format all contained fields rules and return them.
     *
     * @param NovaRequest $request
     * @param array $value
     * @param string|null $type
     * @return array
     */
    protected function generateRules(NovaRequest $request, $value, ?string $type = null): array
    {
        return GroupsCollection::make($value)->map(function ($item, $key) use ($request, $type) {
            $group = $this->newGroup($item['layout'], $item['key']);

            if (!$group) {
                return [];
            }

            $scope = ScopedRequest::scopeFrom($request, $item['attributes'], $item['key']);

            return $group->generateRules($scope, "{$this->attribute}.{$key}", $type);
        })
            ->collapse()
            ->all();
    }

    /**
     * Add validation keys to the valdiatedKeys register, which will be
     * used for transforming validation errors later in the request cycle.
     *
     * @param array $rules
     * @return void
     */
    protected static function registerValidationKeys(array $rules): void
    {
        $validatedKeys = array_map(fn ($field) => $field['attribute'], $rules);

        static::$validatedKeys = array_merge(
            static::$validatedKeys,
            $validatedKeys
        );
    }

    /**
     * Return a previously registered validation key
     *
     * @param string $key
     * @return null|\NovaFlexibleContent\Http\FlexibleAttribute
     */
    public static function getValidationKey(string $key): ?Http\FlexibleAttribute
    {
        return static::$validatedKeys[$key] ?? null;
    }

}
