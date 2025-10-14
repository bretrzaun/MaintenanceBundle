<?php
namespace BretRZaun\MaintenanceBundle;

use Symfony\Component\HttpFoundation\Request;

interface MaintenanceServiceInterface
{
    /**
     * is maintenance mode active
     */
    public function isMaintenance(): bool;

    /**
     * check for internal request
     *
     * @param Request|null $request
     */
    public function isInternal(?Request $request = null): bool;
}