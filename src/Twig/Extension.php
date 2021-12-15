<?php
namespace BretRZaun\MaintenanceBundle\Twig;

use BretRZaun\MaintenanceBundle\MaintenanceServiceInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\TwigFunction;

class Extension extends \Twig\Extension\AbstractExtension
{
    private MaintenanceServiceInterface $maintenanceService;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(MaintenanceServiceInterface $maintenanceService, RequestStack $requestStack)
    {
        $this->maintenanceService = $maintenanceService;
        $this->requestStack = $requestStack;
    }

    public function getFunctions(): array
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
