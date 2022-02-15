<?php

namespace SpringPHP\Inter;
interface RenderInter
{
    public function render(string $template, ?array $data = null, ?array $options = null): ?string;

    public function onException(\Throwable $throwable, $arg): string;
}