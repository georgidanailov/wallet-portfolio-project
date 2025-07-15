<?php

namespace App\Controller;

use App\Service\OrderService;
use App\Repository\ProductRepository;
use App\DTO\CreateOrderDTO;
use App\Exception\ValidationException;
use App\Exception\InsufficientFundsException;
use App\Exception\ProductNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function index(): Response
    {
        $orders = $this->orderService->getOrderHistory();

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
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
}