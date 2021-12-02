<?php

namespace berthott\SX\Services;

use HaydenPierce\ClassFinder\ClassFinder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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
        $this->sxables = collect($sxables);
    }

    /**
     * Get the target model.
     */
    public function getTarget(): string
    {
        if (!request()->segments() || !$this->sxables) {
            return '';
        }
        $model = Str::studly(Str::singular(request()->segment(count(explode('/', config('sx.prefix'))) + 1)));

        return $this->sxables->first(function ($sxable) use ($model) {
            return Str::contains($sxable, $model);
        });
    }
}
