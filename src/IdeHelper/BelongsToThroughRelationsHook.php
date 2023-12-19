<?php

namespace Staudenmeir\BelongsToThrough\IdeHelper;

use Barryvdh\LaravelIdeHelper\Console\ModelsCommand;
use Barryvdh\LaravelIdeHelper\Contracts\ModelHookInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use ReflectionClass;
use ReflectionMethod;
use Throwable;
use Znck\Eloquent\Relations\BelongsToThrough as BelongsToThroughRelation;
use Znck\Eloquent\Traits\BelongsToThrough as BelongsToThroughTrait;

class BelongsToThroughRelationsHook implements ModelHookInterface
{
    public function run(ModelsCommand $command, Model $model): void
    {
        $traits = class_uses_recursive($model);

        if (!in_array(BelongsToThroughTrait::class, $traits)) {
            return;
        }

        $methods = (new ReflectionClass($model))->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if ($method->isAbstract() || $method->isStatic() || !$method->isPublic()
                || $method->getNumberOfParameters() > 0 || $method->getDeclaringClass()->getName() === Model::class) {
                continue;
            }

            try {
                $relationship = $method->invoke($model);
            } catch (Throwable) {
                continue;
            }

            if ($relationship instanceof BelongsToThroughRelation) {
                $this->addRelationship($command, $method, $relationship);
            }
        }
    }

    protected function addRelationship(ModelsCommand $command, ReflectionMethod $method, Relation $relationship): void
    {
        $type = '\\' . $relationship->getRelated()::class;

        $command->setProperty(
            $method->getName(),
            $type,
            true,
            false,
            '',
            true
        );
    }
}
