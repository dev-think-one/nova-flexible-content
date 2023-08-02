<?php

namespace NovaFlexibleContent\Layouts\LayoutTraits;

use NovaFlexibleContent\Flexible;
use NovaFlexibleContent\Layouts\Layout;

trait HasFlexibleFieldInLayout
{
    /**
     * If layout contains sub flexible groups, find group by key recursive.
     *
     * @param string $groupKey
     * @return Layout|null
     */
    public function findFlexibleGroupRecursive(string $groupKey): ?Layout
    {
        if ($this->isUseKey($groupKey)) {
            return $this;
        }

        foreach ($this->fieldsCollection() as $field) {
            if ($field instanceof Flexible) {
                if ($group = $field->findGroupRecursive($groupKey)) {
                    return $group;
                }
            }
        }

        return null;
    }

    /**
     * TODO: rebuild
     */
    public function findGroupRecursiveAndSetAttribute($groupKey, $fieldKey, $newValue): bool
    {
        $data = $this->getAttributes();

        if ($this->isUseKey($groupKey)) {
            if (array_key_exists($fieldKey, $data)) {
                $this->setAttribute($fieldKey, $newValue);

                return true;
            }

            return false;
        }

        $result = $this->setAttributeValue($data, $groupKey, $fieldKey, $newValue);

        $this->attributes = $data;

        return $result;
    }

    public function setAttributeValue(array &$array, string $groupKey, string $fieldKey, mixed $newValue): bool
    {
        foreach ($array as $key => $value) {
            if (is_object($value)
                && property_exists($value, 'key')
                && property_exists($value, 'attributes')
                && $value->key === $groupKey
                && is_object($value->attributes)) {
                foreach ($value->attributes as $attribute => $attrValue) {
                    if ($attribute === $fieldKey) {
                        $value->attributes->$attribute = $newValue;

                        return true;
                    }
                }
            }
            if (is_array($value)) {
                if ($this->setAttributeValue($array[$key], $groupKey, $fieldKey, $newValue)) {
                    return true;
                }
            }
        }

        return false;
    }
}
