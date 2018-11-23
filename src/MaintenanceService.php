<?php
namespace BretRZaun\MaintenanceBundle;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

class MaintenanceService
{
    /**
     * @var \DateTime
     */
    private $currentDate;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->setCurrentDate(new \DateTime('now'));
        $this->parameterBag = $parameterBag;
    }

    public function setCurrentDate(\DateTime $date): void
    {
        $this->currentDate = $date;
    }

    public function isMaintenance(): bool
    {
        $maintenance = $this->parameterBag->get('maintenance');

        // Manueller Schalter
        if (!isset($maintenance['enabled']) || $maintenance['enabled'] === false) {
            // Datumsprüfung
            if (isset($maintenance['from'])) {
                $from = \DateTime::createFromFormat('d.m.Y H:i:s', $maintenance['from']);
                if (!$from) {
                    throw new \RuntimeException('Maintenance: invalid date format "from" (expects d.m.Y H:i:s)');
                }
                if ($from !== false && $from > $this->currentDate) {
                    return false;
                }
            } elseif (isset($maintenance['until'])) {
                $until = \DateTime::createFromFormat('d.m.Y H:i:s', $maintenance['until']);
                if (!$until) {
                    throw new \RuntimeException('Maintenance: invalid date format "until" (expects d.m.Y H:i:s)');
                }
                if ($until === false || $this->currentDate > $until) {
                    return false;
                }
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * IP-Prüfung durchführen
     *
     * @param Request|null $request
     * @return bool
     */
    public function isInternal(Request $request = null): bool
    {
        $maintenance = $this->parameterBag->get('maintenance');
        return $request &&
            isset($maintenance['allowed_ip']) &&
            $this->matchIp($maintenance['allowed_ip'], $request->getClientIp());
    }

    /**
     * check an IP-mask (including wildcards) with an ip address
     *
     * @param string|array $ipmask
     * @param string|null $remoteIp
     * @return bool
     */
    protected function matchIp($ipmask, $remoteIp): bool
    {
        if ($remoteIp === null) {
            return false;
        }
        if (\is_string($ipmask)) {
            $ipmask = [$ipmask];
        }
        foreach ($ipmask as $entry) {
            $pattern = '/^' . str_replace(['*', '.'], ['[0-9]{1,3}', '\.'], $entry) . '$/';
            preg_match($pattern, $remoteIp, $matches);
            if (\count($matches) > 0) {
                return true;
            }
        }
        return false;
    }
}
