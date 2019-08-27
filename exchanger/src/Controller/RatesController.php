<?php

namespace App\Controller;

use App\Entity\Rate;
use App\Exceptions\ExchangerException;
use App\Forms\RateType;
use App\Repository\RateRepository;
use App\Services\Exchanger;
use App\Services\PaginationHelper;
use App\Structures\PaginatedData;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RatesController extends AbstractController
{
    private const BASE_RATES_URI = '/rates/page/';

    /** @var Exchanger */
    private $exchanger;

    /** @var RateRepository */
    private $rateRepository;

    public function __construct(Exchanger $exchanger, RateRepository $rateRepository)
    {
        $this->exchanger = $exchanger;
        $this->rateRepository = $rateRepository;
    }

    /**
     * @Route("/", methods={"GET", "HEAD"})
     * @return RedirectResponse
     */
    public function indexAction(): RedirectResponse
    {
        $paginatedDate = new PaginatedData();

        return new RedirectResponse(self::BASE_RATES_URI . $paginatedDate->firstPage);
    }

    /**
     * @Route("/rates/page/{page}", methods={"GET"})
     * @param string $page
     * @return Response
     */
    public function ratesAction(string $page): Response
    {
        $rates = $this->rateRepository->getRates((int)$page);
        $pageItems = PaginationHelper::generatePaginationBlock($rates, self::BASE_RATES_URI);
        $form = $this->createForm(RateType::class, new Rate(), ['action' => '/rates/add']);

        return $this->render('rates.html.twig', [
            'rates' => $rates,
            'pageItems' => $pageItems,
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/rates/add", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function addAction(Request $request): Response
    {
        $rate = new Rate();
        $form = $this->createForm(RateType::class, $rate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rate->setCustom(true);
            $date = new DateTime();
            $rate->setCreated($date);
            $rate->setUpdated($date);
            $this->rateRepository->plush($rate);
        }

        return new RedirectResponse(self::BASE_RATES_URI .
            PaginationHelper::getLastPage(
                $this->rateRepository->getRecordsNumber()
            )
        );
    }

    /**
     * @Route("/rates/edit/{id}", methods={"GET", "POST"})
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function editAction(Request $request, int $id)
    {
        $rate = $this->rateRepository->find($id);

        if (is_null($rate)) {
            return $this->render('error.html.twig', [
                'message' => 'There is no rate with ' . $id . ' id.'
            ]);
        }

        $form = $this->createForm(RateType::class, $rate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$rate->getDeleted()) {
            $rate->setCustom(true);
            $rate->setUpdated(new DateTime());
            $this->rateRepository->plush($rate);
        }

        return $this->render('edit-rate.html.twig', [
            'form' => $form->createView(),
            'rate' => $rate
        ]);
    }

    /**
     * @Route("/rates/delete/{id}", methods={"GET", "DELETE"})
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function deleteAction(Request $request, int $id): Response
    {
        $rate = $this->rateRepository->find($id);

        if (is_null($rate)) {
            return $this->render('error.html.twig', [
                'message' => 'There is no rate with ' . $id . ' id.'
            ]);
        }

        $recordsNumber = (int)$request->get('recordsNumber');
        $rate->setDeleted(true);
        $this->rateRepository->plush($rate);
        $recordsNumber--;

        return new RedirectResponse(self::BASE_RATES_URI .
            PaginationHelper::getPageToRedirect('' . $request->get('page'), $recordsNumber)
        );
    }

    /**
     * @Route("/rates/refresh/{id}", methods={"GET"})
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function refreshAction(Request $request, int $id): Response
    {
        $rate = $this->rateRepository->find($id);

        if (is_null($rate)) {
            return $this->render('error.html.twig', [
                'message' => 'There is no rate with ' . $id . ' id.'
            ]);
        }

        try {
            $this->exchanger->updateRates([$rate->getCurrency()]);
        } catch (ExchangerException $e) {
            return $this->render('error.html.twig', [
                'message' => 'Remote api exception was thrown. Message: ' .
                    $e->getMessage() . '. Code: ' . $e->getCode()
            ]);
        }

        return new RedirectResponse(self::BASE_RATES_URI .
            PaginationHelper::getPageToRedirect('' . $request->get('page'), (int)$request->get('recordsNumber'))
        );
    }
}
