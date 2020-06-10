<?php
namespace BretRZaun\MaintenanceBundle\Twig;

use BretRZaun\MaintenanceBundle\MaintenanceServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\TwigFunction;

class Extension extends \Twig\Extension\AbstractExtension
{
    /**
     * @var MaintenanceServiceInterface
     */
    private $maintenanceService;

    /**
     * @var Request
     */
    private $requestStack;

    public function __construct(MaintenanceServiceInterface $maintenanceService, RequestStack $requestStack)
    {
        $this->maintenanceService = $maintenanceService;
        $this->requestStack = $requestStack;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('maintenance_mode', [$this, 'maintenanceMode']),
            new TwigFunction('maintenance_mode_allowed', [$this, 'maintenanceModeAllowed'])
        ];
    }

    public function maintenanceMode(): bool
    {
        return $this->maintenanceService->isMaintenance() &&
            !$this->maintenanceService->isInternal($this->requestStack->getCurrentRequest());
    }

    public function maintenanceModeAllowed()
    {
        return $this->maintenanceService->isMaintenance() &&
            $this->maintenanceService->isInternal($this->requestStack->getCurrentRequest());
    }
}
