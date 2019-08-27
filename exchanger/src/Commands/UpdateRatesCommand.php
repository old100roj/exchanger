<?php

namespace App\Commands;

use App\Exceptions\ExchangerException;
use App\Services\Exchanger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateRatesCommand extends Command
{
    protected static $defaultName = 'app:update-rates';

    /** @var Exchanger */
    private $exchanger;

    public function __construct(Exchanger $exchanger, string $name = null)
    {
        parent::__construct($name);
        $this->exchanger = $exchanger;
    }

    protected function configure()
    {
        $this->setDescription('This command updates rates table.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $updated = true;

        try {
            $this->exchanger->updateRates();
        } catch (ExchangerException $e) {
            $updated = false;
            $output->writeln('The ExchangerException was caught.');
            $output->writeln('Message: '. $e->getMessage());
            $output->writeln('Code: '. $e->getCode());
        }

        if ($updated) {
            $output->writeln('Everything is ok. Rates table was updated.');
        }
    }
}
