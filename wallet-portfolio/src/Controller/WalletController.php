<?php

namespace App\Controller;

use App\Service\WalletService;
use App\Repository\TransactionLogRepository;
use App\DTO\AddFundsDTO;
use App\Exception\ValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class WalletController extends AbstractController
{
    public function __construct(
        private WalletService $walletService,
        private TransactionLogRepository $transactionLogRepository,
        private LoggerInterface $logger
    ) {}

    #[Route('/wallet', name: 'app_wallet')]
    public function index(): Response
    {
        $wallet = $this->walletService->getWallet();
        $transactions = $this->transactionLogRepository->findRecentTransactions($wallet, 10);

        return $this->render('wallet/index.html.twig', [
            'wallet' => $wallet,
            'transactions' => $transactions,
        ]);
    }

    #[Route('/wallet/add-funds', name: 'wallet_add_funds', methods: ['POST'])]
    public function addFunds(Request $request): Response
    {
        try {
            $amount = (float) $request->request->get('amount');
            $addFundsDTO = new AddFundsDTO($amount);

            $errors = $addFundsDTO->validate();
            if (!empty($errors)) {
                throw new ValidationException($errors);
            }

            $this->walletService->addFunds($addFundsDTO->getAmount(), $addFundsDTO->getDescription());
            $this->addFlash('success', 'Funds added successfully!');

        } catch (ValidationException $e) {
            foreach ($e->getErrors() as $error) {
                $this->addFlash('error', $error);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error adding funds', ['error' => $e->getMessage()]);
            $this->addFlash('error', 'An error occurred while adding funds');
        }

        return $this->redirectToRoute('app_wallet');
    }

    #[Route('/api/wallet-balance', name: 'api_wallet_balance', methods: ['GET'])]
    public function getWalletBalance(): JsonResponse
    {
        try {
            $wallet = $this->walletService->getWallet();
            return $this->json(['balance' => $wallet->getBalance()]);
        } catch (\Exception $e) {
            $this->logger->error('Error getting wallet balance', ['error' => $e->getMessage()]);
            return $this->json(['error' => 'Unable to retrieve balance'], 500);
        }
    }
}