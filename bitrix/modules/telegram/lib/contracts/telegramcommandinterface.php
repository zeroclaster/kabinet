<?php
namespace Bitrix\telegram\contracts;


interface Telegramcommandinterface
{
    public function execute(array $message): void;
}
