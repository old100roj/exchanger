<?php /** @noinspection PhpParamsInspection */

namespace App\Tests\Services;

use App\Entity\Rate;
use App\Exceptions\ExchangerException;
use App\Repository\RateRepository;
use App\Services\Exchanger;
use BenMajor\ExchangeRatesAPI\ExchangeRatesAPI;
use BenMajor\ExchangeRatesAPI\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use BenMajor\ExchangeRatesAPI\Exception;

class ExchangerTest extends TestCase
{
    private const BASE_CURRENCY = 'USD';
    private const EXCEPTION_MESSAGE = 'Something went wrong.';
    private const EXCEPTION_CODE = 500;

    /** @var bool */
    private $throwException = false;

    /** @var Rate */
    private $rate;

    /** @var mixed */
    private $gottenFromApiRates = [];

    /** @var Exchanger */
    private $exchanger;

    public function setUp(): void
    {
        $rateRepository = $this->mockRateRepository();
        $exchangeRatesAPI = $this->mockExchangeRatesAPI();
        $this->exchanger = new Exchanger($rateRepository, $exchangeRatesAPI, self::BASE_CURRENCY);
    }

    /**
     * @throws ExchangerException
     */
    public function testGetRates(): void
    {
        $this->gottenFromApiRates = ['INR' => 1, 'EUR' => 2];
        $rates = $this->exchanger->getRates();
        $this->assertEquals($this->gottenFromApiRates, $rates);
    }

    /**
     * @throws ExchangerException
     */
    public function testGetRatesIfEmptyArray(): void
    {
        $rates = $this->exchanger->getRates();
        $this->assertEquals($rates, []);
    }

    /**
     * @throws ExchangerException
     */
    public function testGetRatesIfOnlyOne(): void
    {
        $this->gottenFromApiRates = 1.111;
        $rates = $this->exchanger->getRates(['INR']);
        $this->assertEquals(['INR' => 1.111], $rates);
    }

    /**
     * @throws ExchangerException
     */
    public function testGetRatesIfExpectedMoreThenOneButOneGotten(): void
    {
        $this->gottenFromApiRates = 1.111;
        $rates = $this->exchanger->getRates(['INR', 'EUR']);
        $this->assertEquals(['INR' => 1.111], $rates);
    }

    /**
     * @throws ExchangerException
     */
    public function testGetRatesIfException(): void
    {
        $this->throwException = true;
        $this->expectException(ExchangerException::class);
        $this->expectExceptionCode(self::EXCEPTION_CODE);
        $this->expectExceptionMessage(self::EXCEPTION_MESSAGE);
        $this->exchanger->getRates();
    }

    /**
     * @throws ExchangerException
     */
    public function testUpdateRates(): void
    {
        $this->gottenFromApiRates = ['INR' => 1, 'EUR' => 2];
        $this->exchanger->updateRates();
        $this->assertTrue(true);
    }

    /**
     * @throws ExchangerException
     */
    public function testUpdateRatesIfNoRates(): void
    {
        $this->exchanger->updateRates();
        $this->assertTrue(true);
    }

    /**
     * @throws ExchangerException
     */
    public function testUpdateRatesIfException(): void
    {
        $this->throwException = true;
        $this->expectException(ExchangerException::class);
        $this->expectExceptionCode(self::EXCEPTION_CODE);
        $this->expectExceptionMessage(self::EXCEPTION_MESSAGE);
        $this->exchanger->updateRates();
    }

    private function mockRateRepository(): MockObject
    {
        $rateRepository = $this->createMock(RateRepository::class);

        $rateRepository->expects($this->any())
            ->method('plush')
            ->willReturn(null)
        ;

        $rateRepository->expects($this->any())
            ->method('findOneBy')
            ->willReturn($this->rate)
        ;

        return $rateRepository;
    }

    private function mockExchangeRatesAPI(): MockObject
    {
        $exchangeRatesAPI = $this->createMock(ExchangeRatesAPI::class);

        $exchangeRatesAPI->expects($this->any())
            ->method('fetch')
            ->willReturn($this->mockResponse());

        $exchangeRatesAPI->expects($this->any())
            ->method('getBaseCurrency')
            ->willReturn(self::BASE_CURRENCY);

        $exchangeRatesAPI->expects($this->any())
            ->method('addRate')
            ->willReturnSelf();

        $exchangeRatesAPI->expects($this->any())
            ->method('setBaseCurrency')
            ->willReturnSelf()
        ;

        return $exchangeRatesAPI;
    }

    private function mockResponse(): MockObject
    {
        $response = $this->createMock(Response::class);

        $response->expects($this->any())
            ->method('getRates')
            ->willReturnCallback(function () {
                if ($this->throwException) {
                    throw new Exception(self::EXCEPTION_MESSAGE, self::EXCEPTION_CODE);
                }

                return $this->gottenFromApiRates;
            })
        ;

        return $response;
    }
}
