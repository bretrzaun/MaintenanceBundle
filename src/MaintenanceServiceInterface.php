<?php
namespace BretRZaun\MaintenanceBundle;

use Symfony\Component\HttpFoundation\Request;

interface MaintenanceServiceInterface
{
    /**
     * is maintenance mode active
     *
     * @return bool
     */
    public function isMaintenance(): bool;

    /**
     * check for internal request
     *
     * @param Request|null $request
     * @return bool
     */
    public function isInternal(Request $request = null): bool;
}