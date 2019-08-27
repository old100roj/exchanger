<?php
/** @noinspection PhpUndefinedMethodInspection */

namespace Ds3Static\Tests\Controller;

use App\Entity\Rate;
use App\Repository\RateRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RatesControllerTest extends WebTestCase
{
    private const TEST_CURRENCY = 'EUR';

    /** @var KernelBrowser */
    private $client;

    /** @var RateRepository */
    private $rateRepository;

    /** @var Rate */
    private $testRate;

    /** @var bool */
    private $originDeleted;

    /** @var float */
    private $originRate;

    /** @var DateTime */
    private $originUpdated;

    /** @var bool */
    private $isNewRate = false;

    public function setUp(): void
    {
        $this->client = static::createClient();
        self::bootKernel();
        $this->rateRepository = self::$container->get('doctrine')->getRepository(Rate::class);
        $rate = $this->rateRepository->findOneBy([
            'currency' => self::TEST_CURRENCY
        ]);
        $date = new DateTime();

        if (is_null($rate)) {
            $rate = new Rate();
            $rate->setCreated($date);
            $rate->setUpdated($date);
            $rate->setRate(777);
            $rate->setCurrency(self::TEST_CURRENCY);
            $this->isNewRate = true;
        }


        $this->originDeleted = $rate->getDeleted();
        $this->originRate = $rate->getRate();
        $this->originUpdated = $rate->getUpdated();
        $rate->setDeleted(false);
        $this->rateRepository->plush($rate);
        $this->testRate = $rate;
        $this->originDeleted = $this->testRate->getDeleted();
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/');
        $this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testRates(): void
    {
        $this->client->request('GET', '/rates/page/1');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('#rates-tab', 'Rates');
        $this->assertSelectorTextContains('#add-new-tab', 'Add new one');
        $rates = $this->rateRepository->getRates();

        /** @var Rate $record */
        foreach ($rates->records as $record) {
            $this->assertSelectorTextContains('table', $record->getCurrency());
            $this->assertSelectorTextContains('table', $record->getRate());
            $this->assertSelectorTextContains('table', $record->getUpdated()->format('Y-m-d H:i'));
            $this->assertSelectorTextContains('table', $record->getCreated()->format('Y-m-d H:i'));
        }
    }

    public function testAdd(): void
    {
        $this->client->request('POST', '/rates/add');
        $this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testEdit(): void
    {
        $this->client->request('GET', '/rates/edit/' . $this->testRate->getId());
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('form', 'Currency');
        $this->assertSelectorTextContains('form', 'Rate');
        $this->assertSelectorTextContains('ul', $this->testRate->getCurrency());
    }

    public function testEditIfWrongID(): void
    {
        $this->client->request('GET', '/rates/edit/0');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('#error', 'Something went wrong.');
        $this->assertSelectorTextContains('#error', 'There is no rate with 0 id.');
    }

    public function testDeleteIfWrongID(): void
    {
        $this->client->request('GET', '/rates/delete/0');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('#error', 'Something went wrong.');
        $this->assertSelectorTextContains('#error', 'There is no rate with 0 id.');
    }

    public function testDelete(): void
    {
        $this->client->request('GET', '/rates/delete/' . $this->testRate->getId());
        $this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testRefreshIfWrongId(): void
    {
        $this->client->request('GET', '/rates/refresh/0');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('#error', 'Something went wrong.');
        $this->assertSelectorTextContains('#error', 'There is no rate with 0 id.');
    }

    public function testRefreshIfException(): void
    {
        $this->testRate->setCurrency('777');
        $this->rateRepository->plush($this->testRate);
        $this->client->request('GET', '/rates/refresh/' . $this->testRate->getId());
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('#error', 'Something went wrong.');
        $this->assertSelectorTextContains('#error', 'Remote api exception was thrown.');
    }

    public function testRefresh(): void
    {
        $this->client->request('GET', '/rates/refresh/' . $this->testRate->getId());
        $this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function tearDown(): void
    {
        if ($this->isNewRate) {
            $manager = $this->rateRepository->getObjectManager();
            $manager->remove($this->testRate);
            $manager->flush();
            return;
        }

        $this->testRate->setCurrency(self::TEST_CURRENCY);
        $this->testRate->setUpdated($this->originUpdated);
        $this->testRate->setRate($this->originRate);
        $this->testRate->setDeleted($this->originDeleted);
        $this->rateRepository->plush($this->testRate);
    }
}
