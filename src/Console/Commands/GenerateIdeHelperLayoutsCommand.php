<?php

namespace NovaFlexibleContent\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use NovaFlexibleContent\Flexible;
use NovaFlexibleContent\Layouts\Collections\LayoutsCollection;
use NovaFlexibleContent\Layouts\Layout;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionType;
use Symfony\Component\Finder\Finder;

class GenerateIdeHelperLayoutsCommand extends Command
{
    protected $signature = 'nova-flexible-content:ide-helper:layouts
     {--filename= : File name}
     ';

    protected $description = '';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $filename = $this->option('filename') ?? '_ide_helper_flexible_layouts.php';


        $content = $this->generateDocs();

        $written = $this->files->put($filename, $content);
        if ($written !== false) {
            $this->info("Layout(s) information was written to $filename");
        } else {
            $this->error("Failed to write model information to $filename");
        }

        return 0;
    }

    protected function generateDocs(): string
    {
        $formatterPrefix = '@';
        $output = "<?php
// {$formatterPrefix}formatter:off
/**
 * A helper file for your Flexible Layouts
 *
 * @author Think Dev Team <dev@think.studio>
 */
\n\n";

        $namespace = app()->getNamespace();

        $layouts = [];

        foreach ((new Finder())->in(app_path('Nova'))->files() as $resource) {
            $resource = $namespace . str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after($resource->getPathname(), app_path() . DIRECTORY_SEPARATOR)
                );

            if (
                is_subclass_of($resource, Layout::class) &&
                !(new ReflectionClass($resource))->isAbstract()
            ) {
                $layouts[] = $resource;
            }
        }

        foreach ($layouts as $layout) {
            $output .= $this->createLayoutPhpDocs($layout);
        }

        return $output;
    }

    /**
     * @param class-string<Layout> $layout
     * @return string
     */
    protected function createLayoutPhpDocs(string $layout): string
    {
        $output = '';
        $parentClassName = Layout::class;
        $layoutsCollectionClassName = LayoutsCollection::class;
        $className = class_basename($layout);
        $namespace = trim(Str::beforeLast($layout, $className), '\\');

        $output .= "namespace {$namespace} {\n\n";

        $output .= "/**\n";
        $output .= "* {$layout}\n";
        $output .= "*\n";
        /** @var Field $field */
        foreach ($layout::make()->fieldsCollection() as $field) {
            $output .= "* @property-read mixed \${$field->attribute}\n";
            if ($field instanceof Flexible) {
                $fieldName = 'flexible' . Str::ucfirst(Str::camel($field->attribute));

                $output .= "* @property-read \\{$layoutsCollectionClassName} \${$fieldName}\n";
            }
        }
        $output .= $this->getPropertiesFromMethods($layout);
        $output .= "*/\n";
        $output .= "class {$className} extends {$parentClassName} {}\n\n";

        $output .= "}\n\n";

        return $output;
    }

    public function getPropertiesFromMethods(string $layout): string
    {

        $properties = [];

        $methods = get_class_methods($layout);
        if ($methods) {
            sort($methods);
            foreach ($methods as $method) {
                $reflection = new \ReflectionMethod($layout, $method);
                $type = $this->getReturnTypeFromReflection($reflection);
                $isAttribute = is_a($type, Attribute::class, true);
                if (
                    Str::startsWith($method, 'get') && Str::endsWith(
                        $method,
                        'Attribute'
                    ) && $method !== 'getAttribute'
                ) {
                    //Magic get<name>Attribute
                    $name = Str::snake(substr($method, 3, -9));
                    if (!empty($name)) {
                        $type = $this->getReturnTypeFromReflection($reflection);
                        $properties[$name] = $type;
                    }
                } elseif ($isAttribute) {
                    $properties[Str::snake($method)] = 'mixed';
                }
            }
        }

        $output = '';
        foreach ($properties as $name => $type) {
            $output .= "* @property-read $type \${$name}\n";
        }

        return $output;
    }

    protected function getReturnTypeFromReflection(\ReflectionMethod $reflection): ?string
    {
        $returnType = $reflection->getReturnType();
        if (!$returnType) {
            return null;
        }

        $types = $this->extractReflectionTypes($returnType);

        $type = implode('|', $types);

        if ($returnType->allowsNull()) {
            $type .= '|null';
        }

        return $type;
    }

    protected function extractReflectionTypes(ReflectionType $reflection_type): array
    {
        if ($reflection_type instanceof ReflectionNamedType) {
            $types[] = $this->getReflectionNamedType($reflection_type);
        } else {
            $types = [];
            foreach ($reflection_type->getTypes() as $named_type) {
                if ($named_type->getName() === 'null') {
                    continue;
                }

                $types[] = $this->getReflectionNamedType($named_type);
            }
        }

        return $types;
    }

    protected function getReflectionNamedType(ReflectionNamedType $paramType): string
    {
        $parameterName = $paramType->getName();
        if (!$paramType->isBuiltin()) {
            $parameterName = '\\' . $parameterName;
        }

        return $parameterName;
    }
}
