<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Repository\OrderRepository;
use App\Repository\ProductVariantRepository;
use App\Entity\ProductVariant;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Stripe\Exception\ApiErrorException;

class PaymentController extends AbstractController
{
    #[Route('/api/config', name: 'app_config')]
    public function index(StripeService $stripeService): JsonResponse
    {
        return new JsonResponse([
            'stripe_public_key' => $stripeService->getPublicKey(),
        ]);
    }

    #[Route('/api/checkout', name: 'app_checkout', methods: ['POST'])]
    public function checkout(
        Request $request,
        StripeService $stripeService,
        EntityManagerInterface $em,
        ProductVariantRepository $productVariantRepository
    ): JsonResponse {
        // 1) Auth
        $user = $this->getUser();
        $email = $user->getEmail();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], Response::HTTP_UNAUTHORIZED);
        }

        // 2) Vérifier si une commande "pending" existe déjà
        $pendingOrders = $em->getRepository(Order::class)->findBy([
            'user'   => $user,
            'status' => 'PENDING'
        ]);
        
        $stripeClient = new \Stripe\StripeClient($_ENV['STRIPE_SECRET_KEY']);

        foreach ($pendingOrders as $po) {
            if ($po->getStripeSessionId()) {
                try {
                    // Expire la session Stripe existante pour empêcher un paiement "ancien"
                    $stripeClient->checkout->sessions->expire($po->getStripeSessionId());
                } catch (\Throwable $e) {
                    // Si déjà expirée/introuvable, on ignore
                }
            }
            $po->setStatus('CANCELED');
            
        }
        $em->flush();

        // 3) Payload
        $payload = json_decode($request->getContent(), true);
        $items = $payload['data'] ?? null;
        if (!\is_array($items) || $items === []) {
            return new JsonResponse(['message' => 'Panier vide ou invalide.'], Response::HTTP_BAD_REQUEST);
        }

        // 4) Préparation commande
        $order = new Order();
        $order->setUser($user);
        
        $totalPriceCents = 0;
        $lineItems = [];

        foreach ($items as $item) {
            $variantId = $item['productVariantId'] ?? null;
            $quantity  = (int)($item['quantity'] ?? 0);

            if (!$variantId || $quantity <= 0) {
                return new JsonResponse(['message' => 'Ligne de panier invalide.'], Response::HTTP_BAD_REQUEST);
            }

            $variant = $productVariantRepository->find($variantId);

            if (!$variant) {
                return new JsonResponse(['message' => "Variante $variantId introuvable."], Response::HTTP_BAD_REQUEST);
            }

            // (Optionnel) vérifier stock/disponibilité
            if ($variant->getStock() < $quantity) {
                return new JsonResponse(['message' => 'Stock insuffisant pour ' . $variant->getName()], Response::HTTP_BAD_REQUEST);
            }

            // Prix en cents (source de vérité côté serveur)
            $unitPriceCents = (int) str_replace('.', '', $variant->getProduct()->getPrice());

            // Détail de commande + snapshot du prix unitaire
            $orderDetail = new OrderDetails();
            $orderDetail->setOrder($order);
            $orderDetail->setProductVariant($variant);
            $orderDetail->setQuantity($quantity);
            $orderDetail->setUnitPrice($unitPriceCents);

            $order->getOrderDetails()->add($orderDetail);

            $lineItems[] = [
                'quantity'   => $quantity,
                'price_data' => [
                    'currency'    => 'eur',
                    'unit_amount' => $unitPriceCents,
                    'product_data'=> [
                        'name' => $variant->getProduct()->getName(),
                    ],
                ],
            ];

            $totalPriceCents += $unitPriceCents * $quantity;
        }

        $order->setAmount($totalPriceCents);

        // 5) Création + Stripe + persistance
        try {
            // On persiste d'abord pour obtenir un ID d'ordre pour metadata/URL
            $em->persist($order);
            $em->flush();

            $successUrl = $_ENV['APP_FRONT_URL'].'/payment/success';
            $cancelUrl = $_ENV['APP_FRONT_URL'].'/payment/cancel';

            $session = $stripeService->createCheckoutSession(
                lineItems: $lineItems,
                successUrl: $successUrl,
                cancelUrl: $cancelUrl,
                customerEmail: $email,
                metadata: [
                    'order_id' => (string)$order->getId(),
                    'user_id'  => (string)$user->getId(),
                ],
                payment_intent_data: [
                    'metadata' => [
                        'order_id' => (string)$order->getId(),
                    ],
                    ],
            );

            $order->setStripeSessionId($session->id);
            $em->flush();

            return new JsonResponse([
                'message'           => 'Session de paiement créée avec succès.',
                'checkout_url'      => $session->url,
                'stripe_session_id' => $session->id,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            
            // Option 2 (alternative): $em->remove($order); $em->flush();

            return new JsonResponse(
                ['message' => 'Erreur lors de la création de la session de paiement : ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/api/webhook/stripe', name: 'app_webhook_stripe', methods: ['POST'])]
    public function stripeWebhook(
        Request $request,
        StripeService $stripeService,
        OrderRepository $orderRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature');
        $endpointSecret = $this->getParameter('stripe.webhook_secret');

        try {
            $event = $stripeService->handleWebhook($payload, $sigHeader, $endpointSecret);

            if ($event->type === 'payment_intent.succeeded') {
                $pi = $event->data->object;
                $orderId = $pi->metadata->order_id ?? null;

                if ($orderId) {
                    $order = $orderRepository->find((int) $orderId);

                    if ($order && $order->getStatus() !== 'PAID') {
                        $order->setStatus('PAID');
                        $order->setPaidAt(new \DateTimeImmutable());
                        $order->setStripePaymentId($pi->id);
                        $entityManager->flush();
                    }
                }
            }

            if ($event->type === 'checkout.session.expired') {
                $session = $event->data->object;
                $orderId = $session->client_reference_id ?? ($session->metadata['order_id'] ?? null);

                if ($orderId) {
                    $order = $orderRepository->find((int) $orderId);
                    if ($order && $order->getStatus() === 'PENDING') {
                        $order->setStatus('CANCELED');
                        $order->setCancelledAt(new \DateTimeImmutable());
                        $entityManager->flush();
                    }
                }
            }

            if ($event->type === 'payment_intent.canceled') {
                $pi = $event->data->object;
                $orderId = $pi->metadata->order_id ?? null;

                if ($orderId) {
                    $order = $orderRepository->find((int) $orderId);
                    if ($order && $order->getStatus() === 'PENDING') {
                        $order->setStatus('CANCELED');
                        $order->setCancelledAt(new \DateTimeImmutable());
                        $entityManager->flush();
                    }
                }
            }

            return new JsonResponse(['message' => 'Événement ignoré (non géré).',]);

        } catch (\Throwable $e) {
            return new JsonResponse(['message' => 'Invalid webhook: '.$e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
