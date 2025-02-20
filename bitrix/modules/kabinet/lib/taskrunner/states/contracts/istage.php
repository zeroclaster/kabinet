<?
namespace Bitrix\Kabinet\taskrunner\states\contracts;

interface Istage
{
    public function execute();
    public function getStatus();
    public function getId();
    public function getTitle();
    public function getName();
    public function setTitle(string $title);
}
