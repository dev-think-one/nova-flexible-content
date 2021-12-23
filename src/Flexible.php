<?php

namespace Whitecube\NovaFlexibleContent;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\Downloadable;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Whitecube\NovaFlexibleContent\Contracts\LayoutInterface;
use Whitecube\NovaFlexibleContent\Http\ScopedRequest;
use Whitecube\NovaFlexibleContent\Layouts\GroupsCollection;
use Whitecube\NovaFlexibleContent\Layouts\Layout;
use Whitecube\NovaFlexibleContent\Layouts\LayoutsCollection as LayoutsCollection;
use Whitecube\NovaFlexibleContent\Layouts\Preset;
use Whitecube\NovaFlexibleContent\Value\Resolver;
use Whitecube\NovaFlexibleContent\Value\ResolverInterface;

class Flexible extends Field implements Downloadable
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-flexible-content';

    /**
     * The available layouts collection
     *
     * @var \Whitecube\NovaFlexibleContent\Layouts\LayoutsCollection
     */
    protected $layouts;

    /**
     * The currently defined layout groups
     *
     * @var \Illuminate\Support\Collection
     */
    protected $groups;

    /**
     * The field's value setter & getter
     *
     * @var \Whitecube\NovaFlexibleContent\Value\ResolverInterface
     */
    protected $resolver;

    /**
     * All the validated attributes
     *
     * @var array
     */
    protected static $validatedKeys = [];

    /**
     * All the validated attributes
     *
     * @var Model
     */
    public static $model;

    /**
     * Create a fresh flexible field instance
     *
     * @param  string  $name
     * @param  string|null  $attribute
     * @param  mixed|null  $resolveCallback
     * @return void
     */
    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->button(__('Add layout'));

        // The original menu as default
        $this->menu('flexible-drop-menu');

        $this->hideFromIndex();
    }

    /**
     * Get the field layouts
     *
     * @return \Whitecube\NovaFlexibleContent\Layouts\LayoutsCollection
     */
    public function layouts()
    {
        return $this->layouts;
    }

    /**
     * Get the field groups
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function groups()
    {
        return $this->groups;
    }

    /**
     * Set custom dropdown menu component.
     */
    public function menu(string $component, array $data = []): static
    {
        return $this->withMeta(['menu' => compact('component', 'data')]);
    }

    /**
     * Set the button's label.
     */
    public function button(string $label): static
    {
        return $this->withMeta(['button' => $label]);
    }

    /**
     * Make the flexible content take up the full width
     * of the form. Labels will sit above.
     */
    public function fullWidth(): static
    {
        return $this->withMeta(['fullWidth' => true]);
    }

    /**
     *  Set max limit of groups in field.
     */
    public function limit(int $limit = 1): static
    {
        return $this->withMeta(['limit' => $limit]);
    }

    /**
     * Confirm remove
     *
     * @return $this
     */
    public function confirmRemove($label = '', $yes = 'Delete', $no = 'Cancel')
    {
        return $this->withMeta([
            'confirmRemove'        => true,
            'confirmRemoveMessage' => $label,
            'confirmRemoveYes'     => $yes,
            'confirmRemoveNo'      => $no,
        ]);
    }

    /**
     * Set the field's resolver
     *
     * @param  mixed  $resolver
     * @return $this
     */
    public function resolver($resolver)
    {
        if (is_string($resolver) && is_a($resolver, ResolverInterface::class, true)) {
            $resolver = new $resolver();
        }

        if (!($resolver instanceof ResolverInterface)) {
            throw new \Exception('Resolver Class "'.get_class($resolver).'" does not implement ResolverInterface.');
        }

        $this->resolver = $resolver;

        return $this;
    }

    /**
     * Register a new layout
     *
     * @param  array  $arguments
     * @return $this
     */
    public function addLayout(...$arguments)
    {
        $count = count($arguments);

        if ($count > 1) {
            $this->registerLayout(new Layout(...$arguments));

            return $this;
        }

        $layout = $arguments[0];

        if (is_string($layout) && is_a($layout, LayoutInterface::class, true)) {
            $layout = new $layout();
        }

        if (!($layout instanceof LayoutInterface)) {
            throw new \Exception('Layout Class "'.get_class($layout).'" does not implement LayoutInterface.');
        }

        $this->registerLayout($layout);

        return $this;
    }

    /**
     * Apply a field configuration preset
     */
    public function preset(Preset|string|array $preset): static
    {
        if (is_string($preset)) {
            $preset = new $preset;
        } elseif (is_array($preset)) {
            $preset = Preset::withLayouts($preset);
        }

        $preset->handle($this);

        return $this;
    }

    /**
     * Push a layout instance into the layouts collection
     *
     * @param  \Whitecube\NovaFlexibleContent\Contracts\LayoutInterface  $layout
     * @return void
     */
    protected function registerLayout(LayoutInterface $layout)
    {
        if (!$this->layouts) {
            $this->layouts = new LayoutsCollection();
            $this->withMeta(['layouts' => $this->layouts]);
        }

        $this->layouts->push($layout);
    }

    /**
     * Resolve the field's value.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
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
     * @param  mixed  $resource
     * @param  string|null  $attribute
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
     * @param  NovaRequest  $request
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
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
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

    public function reFillValue($model, ?string $attribute = null)
    {
        $attribute   = $attribute ?? $this->attribute;
        $this->value = $this->resolver->set($model, $attribute, $this->groups);
    }

    /**
     * Process an incoming POST Request
     */
    protected function syncAndFillGroups(NovaRequest $request, string $requestAttribute, $model): array
    {
        if (!($raw = $this->extractValue($request, $requestAttribute))) {
            $this->fireRemoveCallbacks(GroupsCollection::make(), $request, $model);
            $this->groups = GroupsCollection::make();

            return [];
        }

        $callbacks = [];
        $newGroups = GroupsCollection::make($raw)->map(function ($item, $key) use ($request, &$callbacks) {
            $layout = $item['layout'];
            $key = $item['key'];
            $attributes = $item['attributes'];

            $group = $this->findGroup($key) ?? $this->newGroup($layout, $key);

            $group->setCollapsed((bool) ($item['collapsed'] ?? false));
            $scope = ScopedRequest::scopeFrom($request, $attributes, $key);
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
        })->each(function (LayoutInterface $group) use ($request, $model) {
            if (method_exists($group, 'fireRemoveCallback')) {
                $group->fireRemoveCallback($this, $request, $model);
            }
        });

        return $this;
    }

    /**
     * Find the flexible's value in given request.
     */
    protected function extractValue(NovaRequest $request, string $attribute): ?array
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
     * Resolve all contained groups and their fields
     *
     * @param  \Illuminate\Support\Collection  $groups
     * @return \Illuminate\Support\Collection
     */
    protected function resolveGroups($groups)
    {
        return $groups->map(function ($group) {
            return $group->getResolved();
        });
    }

    /**
     * Resolve all contained groups and their fields for display on index and
     * detail views.
     *
     * @param  \Illuminate\Support\Collection  $groups
     * @return \Illuminate\Support\Collection
     */
    protected function resolveGroupsForDisplay($groups)
    {
        return $groups->map(function ($group) {
            return $group->getResolvedForDisplay();
        });
    }

    /**
     * Define the field's actual layout groups (as "base models") based
     * on the field's current model & attribute
     *
     * @param  mixed  $resource
     * @param  string  $attribute
     * @return \Illuminate\Support\Collection
     */
    protected function buildGroups($resource, $attribute)
    {
        if (!$this->resolver) {
            $this->resolver(Resolver::class);
        }

        return $this->groups = $this->resolver->get($resource, $attribute, $this->layouts);
    }

    public function findGroup(string $groupKey): ?LayoutInterface
    {
        return $this->groups->first(fn (LayoutInterface $group) => $group->isUseKey($groupKey));
    }

    public function findGroupRecursive(string $groupKey): ?LayoutInterface
    {
        /** @var LayoutInterface $group */
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
            ->each(function (LayoutInterface $group) use ($groupKey, $fieldKey, $newValue, &$isUpdated) {
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
    protected function newGroup(string $layout, string $key): ?LayoutInterface
    {
        return $this->layouts->find($layout)?->duplicate($key);
    }

    /**
     * Get the validation rules for this field & its contained fields.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function getRules(NovaRequest $request)
    {
        return parent::getRules($request);
    }

    /**
     * Get the creation rules for this field & its contained fields.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array|string
     */
    public function getCreationRules(NovaRequest $request)
    {
        return array_merge_recursive(
            parent::getCreationRules($request),
            $this->getFlexibleRules($request, 'creation')
        );
    }

    /**
     * Get the update rules for this field & its contained fields.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function getUpdateRules(NovaRequest $request)
    {
        return array_merge_recursive(
            parent::getUpdateRules($request),
            $this->getFlexibleRules($request, 'update')
        );
    }

    /**
     * Retrieve contained fields rules and assign them to nested array attributes
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $specificty
     * @return array
     */
    protected function getFlexibleRules(NovaRequest $request, $specificty)
    {
        if (!($value = $this->extractValue($request, $this->attribute))) {
            return [];
        }

        $rules = $this->generateRules($request, $value, $specificty);

        if (!is_a($request, ScopedRequest::class)) {
            // We're not in a nested flexible, meaning we're
            // assuming the field is located at the root of
            // the model's attributes. Therefore, we should now
            // register all the collected validation rules for later
            // reference (see Http\TransformsFlexibleErrors).
            static::registerValidationKeys($rules);

            // Then, transform the rules into an array that's actually
            // usable by Laravel's Validator.
            $rules = $this->getCleanedRules($rules);
        }

        return $rules;
    }

    /**
     * Format all contained fields rules and return them.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  array  $value
     * @param  string  $specificty
     * @return array
     */
    protected function generateRules(NovaRequest $request, $value, $specificty)
    {
        return GroupsCollection::make($value)->map(function ($item, $key) use ($request, $specificty) {
            $group = $this->newGroup($item['layout'], $item['key']);

            if (!$group) {
                return [];
            }

            $scope = ScopedRequest::scopeFrom($request, $item['attributes'], $item['key']);

            return $group->generateRules($scope, $specificty, $this->attribute.'.'.$key);
        })
                               ->collapse()
                               ->all();
    }

    /**
     * Transform Flexible rules array into an actual validator rules array
     *
     * @param  array  $rules
     * @return array
     */
    protected function getCleanedRules(array $rules)
    {
        return array_map(function ($field) {
            return $field['rules'];
        }, $rules);
    }

    /**
     * Add validation keys to the valdiatedKeys register, which will be
     * used for transforming validation errors later in the request cycle.
     *
     * @param  array  $rules
     * @return void
     */
    protected static function registerValidationKeys(array $rules)
    {
        $validatedKeys = array_map(function ($field) {
            return $field['attribute'];
        }, $rules);

        static::$validatedKeys = array_merge(
            static::$validatedKeys,
            $validatedKeys
        );
    }

    /**
     * Return a previously registered validation key
     *
     * @param  string  $key
     * @return null|\Whitecube\NovaFlexibleContent\Http\FlexibleAttribute
     */
    public static function getValidationKey($key)
    {
        return static::$validatedKeys[$key] ?? null;
    }

    /**
     * Registers a reference to the origin model for nested & contained fields.
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
     */
    public static function getOriginModel(): ?Model
    {
        return static::$model;
    }
}
