<?php

namespace App\Services;

use App\Entity\Rate;
use App\Exceptions\ExchangerException;
use App\Repository\RateRepository;
use BenMajor\ExchangeRatesAPI\Exception;
use BenMajor\ExchangeRatesAPI\ExchangeRatesAPI;
use DateTime;

class Exchanger
{
    /** @var ExchangeRatesAPI */
    private $exchanger;

    /** @var RateRepository */
    private $rateRepository;

    public function __construct(
        RateRepository $rateRepository,
        ExchangeRatesAPI $exchanger,
        string $baseCurrency
    ) {
        $this->rateRepository = $rateRepository;
        $this->exchanger = $exchanger->setBaseCurrency($baseCurrency);
    }

    /**
     * @param array $rateNames
     * @return array
     * @throws ExchangerException
     */
    public function getRates(array $rateNames = []): array
    {
        foreach ($rateNames as $rate) {
            try {
                $this->exchanger->addRate($rate);
            } catch (\Exception $e) {
                throw new ExchangerException($e->getMessage(), $e->getCode());
            }
        }

        try {
            $result = $this->exchanger->fetch()->getRates();
        } catch (Exception $e) {
            throw new ExchangerException($e->getMessage(), $e->getCode());
        }

        if (is_array($result)) {
            return $result;
        }

        if (count($rateNames) >= 1) {
            return [array_shift($rateNames) => $result];
        }

        return [];
    }

    /**
     * @param array $rateNames
     * @throws ExchangerException
     */
    public function updateRates(array $rateNames = []): void
    {
        $dateTime = new DateTime();
        $rates = $this->getRates($rateNames);

        foreach ($rates as $currency => $rateValue) {
            if ($currency === $this->exchanger->getBaseCurrency()) {
                continue;
            }

            $rate = $this->rateRepository->findOneBy([
                'currency' => $currency
            ]);

            if (is_null($rate)) {
                $rate = new Rate();
            }

            if ($rate->getCustom()) {
                continue;
            }

            if (is_null($rate->getCreated())) {
                $rate->setCreated($dateTime);
            }

            $rate->setRate($rateValue);
            $rate->setCurrency($currency);
            $rate->setUpdated($dateTime);
            $this->rateRepository->plush($rate);
        }
    }
}
