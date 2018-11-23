<?php
namespace BretRZaun\MaintenanceBundle;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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

        /*// IP-Prüfung durchführen
        if (isset($maintenance['allowed_ip']) &&
            $this->matchIp($maintenance['allowed_ip'], $event->getRequest()->getClientIp())
        ) {
            return false;
        }*/

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
}
