<?php declare(strict_types=1);

namespace Autonomo\AiSpeaker;

trait ModelTrait
{
    protected string $model;

    public function changeModel(string $model): void
    {
        $this->model = $model;
    }

    public function getModel(): string
    {
        return $this->model;
    }
}
