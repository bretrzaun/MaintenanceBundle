<?php
namespace BretRZaun\MaintenanceBundle\Twig;

use BretRZaun\MaintenanceBundle\MaintenanceService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class Extension extends \Twig\Extension\AbstractExtension
{
    /**
     * @var MaintenanceService
     */
    private $maintenanceService;

    /**
     * @var Request
     */
    private $requestStack;

    public function __construct(MaintenanceService $maintenanceService, RequestStack $requestStack)
    {
        $this->maintenanceService = $maintenanceService;
        $this->requestStack = $requestStack;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('is_maintenance', [$this, 'isMaintenance'])
        ];
    }

    public function isMaintenance($useRequest = true): bool
    {
        if ($useRequest) {
            $request = $this->requestStack->getCurrentRequest();
        } else {
            $request = null;
        }
        return $this->maintenanceService->isMaintenance($request);
    }
}
