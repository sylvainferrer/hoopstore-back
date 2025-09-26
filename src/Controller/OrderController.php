<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Entity\User;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class OrderController extends AbstractController
{
    #[Route('/admin/orders', name: 'api_show_orders_admin', methods: ['GET'])]
    public function showOrdersAdmin(OrderRepository $orderRepository): JsonResponse
    {
        $orders = $orderRepository->findAll();

        if (!$orders) {
            return new JsonResponse(['message' => 'Commandes non trouvées.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = array_map(function (Order $order) {

            return [
                'id'   => $order->getId(),
                'firstname' => $order->getUser()->getFirstname(),
                'lastname' => $order->getUser()->getLastname(),
                'email' => $order->getUser()->getEmail(),
                'amount' => number_format($order->getAmount() / 100, 2, ',', ' '),
                'status' => $order->getStatus(),
                'stripeSessionId' => $order->getStripeSessionId(),
                'stripePaymentId' => $order->getStripePaymentId(),
                'createdAt' => $order->getCreatedAt()?->format('d-m-Y H:i'),
                'paidAt' => $order->getPaidAt()?->format('d-m-Y H:i'),
            ];
        }, $orders);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[Route('/admin/orders/{id}', name: 'api_show_order_details_admin', methods: ['GET'])]
    public function showOrderDetailsAdmin(int $id, OrderRepository $orderRepository): JsonResponse
    {
        $order = $orderRepository->findOneWithOrderDetails($id);

        if (!$order) {
            return new JsonResponse(['message' => 'Aucun détail trouvé pour cette commande.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $orderDetails = $order->getOrderDetails()->toArray();

        $data = array_map(fn(OrderDetails $o) => [
            'id'       => $o->getId(),
            'name'     => $o->getProductVariant()->getProduct()->getName(),
            'size'     => $o->getProductVariant()->getSize()->value,
            'price'    => number_format($o->getUnitPrice() / 100, 2, ',', ' '),
            'quantity' => $o->getQuantity(),
            'total'    => number_format(($o->getUnitPrice() * $o->getQuantity()) / 100, 2, ',', ' ')
        ], $orderDetails);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[Route('/orders/me', name: 'api_show_orders_me', methods: ['GET'])]
    public function showOrdersMe(OrderRepository $orderRepository): JsonResponse
    {
        $user = $this->getUser();

        if (!$user || !$user instanceof User) {
            return new JsonResponse(['message' => 'Utilisateur introuvable ou invalide.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $orders = $orderRepository->findNonCanceledOrdersByUser($user);

        $data = array_map(function (array $order) {
            return [
                'id'        => $order['id'],
                'firstname' => $order['firstname'],
                'lastname'  => $order['lastname'],
                'email'     => $order['email'],
                'amount'    => number_format($order['amount'] / 100, 2, ',', ' '),
                'status'    => $order['status'],
                'paidAt'    => $order['paidAt']?->format('d-m-Y H:i'),
            ];
        }, $orders);

        return new JsonResponse($data, JsonResponse::HTTP_OK);

    }

    /* ------------------------------------------------------------------------------------------------------------ */

    #[Route('/orders/me/{id}', name: 'api_show_order_details_me', methods: ['GET'])]
    public function showOrderDetailsMe(int $id, OrderRepository $orderRepository): JsonResponse
    {
        $user = $this->getUser();

        if (!$user || !$user instanceof User) {
            return new JsonResponse(['message' => 'Utilisateur introuvable ou invalide.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $order = $orderRepository->findNonCanceledOrderDetailsByUser($id, $user);

        if (!$order) {
            return new JsonResponse(['message' => 'Commande introuvable.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $orderDetails = $order->getOrderDetails()->toArray();

        $data = array_map(fn(OrderDetails $o) => [
            'id'       => $o->getId(),
            'name'     => $o->getProductVariant()->getProduct()->getName(),
            'size'     => $o->getProductVariant()->getSize()->value,
            'price'    => number_format($o->getUnitPrice() / 100, 2, ',', ' '),
            'quantity' => $o->getQuantity(),
            'total'    => number_format(($o->getUnitPrice() * $o->getQuantity()) / 100, 2, ',', ' ')
        ], $orderDetails);

        return new JsonResponse($data, JsonResponse::HTTP_OK);

    }

}
