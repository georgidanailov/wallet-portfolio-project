<?php

namespace App\Controller;

use App\Service\OrderService;
use App\Repository\ProductRepository;
use App\DTO\CreateOrderDTO;
use App\Exception\ValidationException;
use App\Exception\InsufficientFundsException;
use App\Exception\ProductNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class OrderController extends AbstractController
{
    public function __construct(
        private OrderService $orderService,
        private ProductRepository $productRepository,
        private LoggerInterface $logger
    ) {}

    #[Route('/orders', name: 'app_order')]
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $paginatedOrders = $this->orderService->getPaginatedOrders($page, 5);

        return $this->render('order/index.html.twig', [
            'pagination' => $paginatedOrders,
        ]);
    }

    #[Route('/buy/{id}', name: 'buy_product')]
    public function buyProduct(int $id, Request $request): Response
    {
        try {
            $product = $this->productRepository->find($id);
            if (!$product) {
                throw new ProductNotFoundException();
            }

            $quantity = (int) $request->request->get('quantity', 1);
            $createOrderDTO = new CreateOrderDTO($id, $quantity);

            $errors = $createOrderDTO->validate();
            if (!empty($errors)) {
                throw new ValidationException($errors);
            }

            $order = $this->orderService->createOrder($product, $quantity);
            $this->addFlash('success', 'Product purchased successfully!');

            return $this->redirectToRoute('app_wallet');

        } catch (ProductNotFoundException $e) {
            $this->addFlash('error', 'Product not found');
            return $this->redirectToRoute('app_product');
        } catch (InsufficientFundsException $e) {
            $this->addFlash('error', 'Insufficient funds!');
            return $this->redirectToRoute('product_show', ['id' => $id]);
        } catch (ValidationException $e) {
            foreach ($e->getErrors() as $error) {
                $this->addFlash('error', $error);
            }
            return $this->redirectToRoute('product_show', ['id' => $id]);
        } catch (\Exception $e) {
            $this->logger->error('Error creating order', ['error' => $e->getMessage()]);
            $this->addFlash('error', 'An error occurred while processing your order');
            return $this->redirectToRoute('product_show', ['id' => $id]);
        }
    }

    #[Route('/api/orders', name: 'api_orders', methods: ['GET'])]
    public function getOrdersApi(Request $request): JsonResponse
    {
        try {
            $page = max(1, (int) $request->query->get('page', 1));
            $itemsPerPage = min(50, max(1, (int) $request->query->get('limit', 5)));

            $paginatedOrders = $this->orderService->getPaginatedOrders($page, $itemsPerPage);

            return $this->json([
                'success' => true,
                'data' => $paginatedOrders->toArray()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error fetching orders API', ['error' => $e->getMessage()]);
            return $this->json([
                'success' => false,
                'error' => 'Unable to fetch orders'
            ], 500);
        }
    }
}