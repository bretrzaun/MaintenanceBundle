<?php
namespace BretRZaun\MaintenanceBundle\Twig;

use BretRZaun\MaintenanceBundle\MaintenanceService;

class Extension extends \Twig\Extension\AbstractExtension
{
    /**
     * @var MaintenanceService
     */
    private $maintenanceService;

    public function __construct(MaintenanceService $maintenanceService)
    {

        $this->maintenanceService = $maintenanceService;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('is_maintenance', [$this, 'isMaintenance'])
        ];
    }

    public function isMaintenance(): bool
    {
        return $this->maintenanceService->isMaintenance();
    }
}
