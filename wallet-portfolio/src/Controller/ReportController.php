<?php

namespace App\Controller;

use App\Service\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class ReportController extends AbstractController
{
    public function __construct(
        private ReportService $reportService,
        private LoggerInterface $logger
    ) {}

    #[Route('/report', name: 'app_report')]
    public function index(): Response
    {
        try {
            $report = $this->reportService->generateSpendingReport();

            return $this->render('report/index.html.twig', [
                'categorySpending' => $report->getCategorySpending(),
                'totalSpent' => $report->getTotalSpent(),
                'totalOrders' => $report->getTotalOrders(),
                'totalItems' => $report->getTotalItems(),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error generating report', ['error' => $e->getMessage()]);
            $this->addFlash('error', 'An error occurred while generating the report');
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/api/report', name: 'api_report', methods: ['GET'])]
    public function getReportData(): JsonResponse
    {
        try {
            $report = $this->reportService->generateSpendingReport();
            return $this->json($report->toArray());
        } catch (\Exception $e) {
            $this->logger->error('Error generating API report', ['error' => $e->getMessage()]);
            return $this->json(['error' => 'Unable to generate report'], 500);
        }
    }
}