<?php

namespace Infira\Collection;

/**
 * @mixin \Illuminate\Support\Collection
 */
class CollectionMacros
{
    public static function inject(): \Closure
    {
        return function ($callback, $method = 'map') {
            return $this->$method($this->_makeInjectable($callback));
        };
    }


    public static function toInjectable(): \Closure
    {
        return function () {
            return $this->pipeInto(\Infira\Collection\InjectableCollection::class);
        };
    }


    public static function mapCollect(): \Closure
    {
        return function ($callback = null) {
            if (!$callback) {
                return $this->mapInto(\Illuminate\Support\Collection::class);
            }

            return $this->map(fn($value, $key) => $callback(new \Illuminate\Support\Collection($value, $key)));
        };
    }


    public static function mapOnly(): \Closure
    {
        return function ($keys) {
            return $this->map(fn($item) => collect($item)->only($keys)->all());
        };
    }


    public static function mapSelf(): \Closure
    {
        return function ($callback = null) {
            return $this->map(fn($value, $key) => $callback(new static($value, $key)));
        };
    }


    public static function mapWith(): \Closure
    {
        return function ($class, $callback = null) {
            if (!$callback) {
                return $this->mapInto($class);
            }

            return $this->map(fn($value, $key) => $callback(new $class($value, $key)));
        };
    }


    public static function mergeOnly(): \Closure
    {
        return function ($items, $keys = null) {
            $items = $this->getArrayableItems($items);
            if ($keys === null) {
                $keys = array_keys($items);
            }
            else {
                if ($keys instanceof \Illuminate\Support\Enumerable) {
                    $keys = $keys->all();
                }
                $keys = is_array($keys) ? $keys : array_slice(func_get_args(), 1);
            }

            return new static(array_merge(array_flip($keys), \Illuminate\Support\Arr::only($items, $keys)));
        };
    }


    public static function rename(): \Closure
    {
        return function ($from, $to = null) {
            $from = (is_string($from) and is_string($to)) ? [$from, $to] : $this->getArrayableItems($from);

            $this->items = array_combine(
                array_map(static fn($key) => $from[$key] ?? $key, array_keys($this->items)),
                $this->items);

            return $this;
        };
    }


    public static function zipSelf(): \Closure
    {
        return function () {
            return $this->keys()->zip($this->values());
        };
    }
}
