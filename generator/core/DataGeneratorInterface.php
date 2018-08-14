<?php

namespace go1\util_dataset\generator\core;

interface DataGeneratorInterface
{
    public function generate(&$trait, callable $callback = null);
}
