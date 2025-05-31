<?php
namespace Bitrix\telegram\contracts;

interface Middlewareinterface
{
    public function handle(array $message, \Closure $next): void;
}