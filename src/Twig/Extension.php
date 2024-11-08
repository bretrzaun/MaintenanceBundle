<?php
namespace BretRZaun\MaintenanceBundle\Twig;

use Twig\Extension\AbstractExtension;
use BretRZaun\MaintenanceBundle\MaintenanceServiceInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\TwigFunction;

class Extension extends AbstractExtension
{
    public function __construct(private readonly MaintenanceServiceInterface $maintenanceService, private readonly RequestStack $requestStack)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('maintenance_mode', $this->maintenanceMode(...)),
            new TwigFunction('maintenance_mode_allowed', $this->maintenanceModeAllowed(...))
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
