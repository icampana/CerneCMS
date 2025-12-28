<?php

namespace app\commands;

interface Command
{
    public function execute(array $args): void;
    public function getDescription(): string;
    public function getUsage(): string;
}
