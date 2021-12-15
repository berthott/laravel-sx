<?php

namespace berthott\SX\Services;

use HaydenPierce\ClassFinder\ClassFinder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

const CACHE_KEY = 'SxableService-Cache-Key';

class SxableService
{
    /**
     * Collection with all sxable classes.
     */
    private Collection $sxables;

    /**
     * The Constructor.
     */
    public function __construct()
    {
        $this->initSxableClasses();
    }

    /**
     * Get the sxable classes collection.
     */
    public function getSxableClasses(): Collection
    {
        return $this->sxables;
    }

    /**
     * Initialize the sxable classes collection.
     */
    private function initSxableClasses(): void
    {
        $this->sxables = Cache::sear(CACHE_KEY, function () {
            $sxables = [];
            $namespaces = config('sx.namespace');
            foreach (is_array($namespaces) ? $namespaces : [$namespaces] as $namespace) {
                foreach (ClassFinder::getClassesInNamespace($namespace, ClassFinder::RECURSIVE_MODE) as $class) {
                    foreach (class_uses_recursive($class) as $trait) {
                        if ('berthott\SX\Models\Traits\Sxable' == $trait) {
                            array_push($sxables, $class);
                        }
                    }
                }
            }
            return collect($sxables);
        });
    }

    /**
     * Get the target model.
     */
    public function getTarget(): string
    {
        if (!request()->segments() || $this->sxables->isEmpty()) {
            return '';
        }
        $model = Str::studly(Str::singular(request()->segment(count(explode('/', config('permissions.prefix'))) + 1)));

        return $this->sxables->first(function ($class) use ($model) {
            return Arr::last(explode('\\', $class)) === $model;
        }) ?: '';
    }
}
