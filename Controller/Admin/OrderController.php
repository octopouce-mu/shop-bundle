<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-18
 */

namespace Octopouce\ShopBundle\Controller\Admin;

use Octopouce\ShopBundle\Entity\Account\User;
use Doctrine\ORM\EntityNotFoundException;
use JMS\Payment\CoreBundle\PluginController\PluginController;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Octopouce\ShopBundle\Entity\Billing;
use Octopouce\ShopBundle\Entity\Invoice;
use Octopouce\ShopBundle\Entity\Order;
use Octopouce\ShopBundle\Entity\OrderItem;
use Octopouce\ShopBundle\Entity\OrderState;
use Octopouce\ShopBundle\Entity\Shipment;
use Octopouce\ShopBundle\Factory\Invoice\InvoiceInterface;
use Octopouce\ShopBundle\Factory\Order\Event\OrderEvents;
use Octopouce\ShopBundle\Factory\Order\Factory\OrderFactory;
use Octopouce\ShopBundle\Form\Admin\OrderStateType;
use Octopouce\ShopBundle\Form\Admin\OrderType;
use Octopouce\ShopBundle\Form\Admin\OrderUserType;
use Octopouce\ShopBundle\Form\BillingType;
use Octopouce\ShopBundle\Form\ShipmentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/orders")
 */
class OrderController extends AbstractController
{
	/**
	 * @Route("/", name="octopouce_shop_admin_order_index")
	 */
	public function index(): Response
	{
		$orders = $this->getDoctrine()->getRepository(Order::class)->findByStatus();

		return $this->render('@OctopouceShop/Admin/order/index.html.twig', [
			'entities' => $orders
		]);
	}

	/**
	 * @Route("/new", name="octopouce_shop_admin_order_new")
	 */
	public function new(Request $request): Response
	{
		$order = new Order();

		$form = $this->createForm(OrderUserType::class, $order);
		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid()) {
			return $this->redirectToRoute('octopouce_shop_admin_order_new_user', ['idUser' => $order->getUser()->getId()]);
		}

		return $this->render('@OctopouceAdmin/Crud/edit.html.twig', [
			'form' => $form->createView()
		]);
	}

	/**
	 * @Route("/new/{idUser}", name="octopouce_shop_admin_order_new_user")
	 */
	public function newUser($idUser, Request $request, OrderFactory $orderFactory, InvoiceInterface $invoiceFactory): Response
	{
		$em = $this->getDoctrine()->getManager();

		$user = $em->getRepository(User::class)->find($idUser);
		if(!$user) {
			throw new EntityNotFoundException(User::class, $idUser);
		}


		$order = new Order();
		$order->setUser($user);

		$orderFactory->setOrder($order);
		$orderFactory->setUser($user);

		$orderFactory->setDataBillingByUser($user, $order);
		$orderFactory->setDataShipmentByUser($user, $order);

		$now = new \DateTime();

		$lastOrder = $this->getDoctrine()->getRepository(Order::class)->lastByNumber();
		$pos = strpos($lastOrder->getNumber(), $now->format('y'), 0);
		if($pos !== false && $pos === 0) {
			$number = preg_replace('/^'.$now->format('y').'/', '', $lastOrder->getNumber());
			$number = (int) $number + 1;
			if($number < 10) {
				$number = '0'.$number;
			}else {
				$number = (string) $number;
			}
		} else {
			$number = '01';
		}

		$order->setNumber($now->format('y').$number);

		$form = $this->createForm(OrderType::class, $order);
		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid()) {

			if(!$order->getShipment()->getAddress()) {
				$order->setShipment(null);
			}

			if(!$form->get('cart')->getData() || !$form->get('order')->getData()) {
				$this->getDoctrine()->getManager()->persist($order);


				$state = $form->get('state')->getData();
				$orderState = new OrderState();
				$orderState->setState($state);
				$orderState->setOrder($order);
				$orderState->setUser($this->getUser());
				$em->persist($orderState);

				$order->addState($orderState);



				$cloneCart = null;
				if($form->get('cart')->getData()) {
					$cloneCart = $form->get('cart')->getData();
				} elseif($form->get('order')->getData()) {
					$cloneCart = $form->get('order')->getData();
				}

				if($cloneCart) {
					$items = $cloneCart->getItems();
					if($items) {
						foreach ($items as $item) {
							$orderFactory->addItem($orderFactory->getArticle($item), $item->getName(), $item->getQuantity(), $item->getDigital());
						}

						if($state->isPaid()) {
							$invoiceFactory->create($order);
							$orderFactory->updateAccess();
						}
					}
				}

				$this->getDoctrine()->getManager()->flush();

				$this->addFlash('success', 'Vous pouvez ajouter les produits à cette commande !');

				return $this->redirectToRoute('octopouce_shop_admin_order_show', ['id' => $order->getId()]);
			} else {
				$this->addFlash('danger', 'Vous ne pouvez pas sélectionner un panier et une commande sur la même commande !');
			}



		}

		return $this->render('@OctopouceShop/Admin/order/new.html.twig', [
			'form' => $form->createView(),
			'order' => $order
		]);
	}

	/**
	 * @Route("/{id}", name="octopouce_shop_admin_order_show")
	 */
	public function show($id, Request $request, OrderFactory $orderFactory, InvoiceInterface $invoiceFactory, PluginController $ppc): Response
	{
		$order = $this->getDoctrine()->getRepository(Order::class)->find($id);
		if(!$order) {
			$this->createNotFoundException();
		}

		$oldPaid = $order->getStates()[0]->getState()->isPaid();

		$newState = new OrderState();
		$formState = $this->createForm(OrderStateType::class, $newState, [
			'last_state' => $order->getStates()[count($order->getStates()) - 1]->getState()->getName()
		]);

		$formState->handleRequest($request);
		if($formState->isSubmitted() && $formState->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$newState->setOrder($order);
			$newState->setUser($this->getUser());
			$em->persist($newState);

			$now = new \DateTime();

			// if the state is paid and order haven't pay it
			if($newState->getState()->isPaid() && !$order->getPayIt()){
				$order->setPayIt($now);
			}

			// if new state is paid, we update access
			if(!$oldPaid && $newState->getState()->isPaid()) {

				$paymentSystemName = $order->getPaymentInstruction()->getPaymentSystemName();
				if($paymentSystemName === 'check' || $paymentSystemName === 'bank_wire') {
					// change state payments
					$lastPayment = $order->getPaymentInstruction()->getPayments()[ count( $order->getPaymentInstruction()->getPayments() ) - 1 ];
					$ppc->approve( $lastPayment->getId(), $lastPayment->getTargetAmount() );
				}

				$orderFactory->setOrder( $order );
				$orderFactory->updateAccess();
			}

			// if new state isn't paid, init pay it
			if(!$newState->getState()->isPaid()) {
				$order->setPayIt(null);
			}

			$em->flush();

			// check if order is pay it, we update pay it of last invoice if she exist
			if($order->getPayIt() && count($order->getInvoices()) > 0 && !$order->getInvoices()[0]->getPayIt()) {
				$order->getInvoices()[0]->setPayIt($now);
			} elseif ($order->getPayIt() && !$order->getInvoices()) {
				$invoiceFactory->createAndSend($order);
			}

			$em->flush();


			return $this->redirectToRoute('octopouce_shop_admin_order_show', ['id' => $id]);

		}

		return $this->render('@OctopouceShop/Admin/order/show.html.twig', [
			'order' => $order,
			'formState' => $formState->createView()
		]);
	}

	/**
	 * @Route("/{id}/invoices/new", name="octopouce_shop_admin_order_show_invoice_new")
	 */
	public function invoiceNew($id, InvoiceInterface $invoiceFactory): Response
	{
		$order = $this->getDoctrine()->getRepository(Order::class)->find($id);
		if(!$order) {
			$this->createNotFoundException();
		}

		$invoiceFactory->create($order);
		$invoiceFactory->save();

		$this->addFlash('success', 'Nouvelle facture créée !');

		return $this->redirectToRoute('octopouce_shop_admin_order_show', ['id' => $id]);
	}

	/**
	 * @Route("/{idOrder}/invoices/{id}/generate", name="octopouce_shop_admin_order_show_invoice_generate")
	 */
	public function invoiceGenerate($idOrder, $id, InvoiceInterface $invoiceFactory): PdfResponse
	{
		$order = $this->getDoctrine()->getRepository(Order::class)->find($idOrder);
		if(!$order) {
			$this->createNotFoundException();
		}

		/** @var Invoice $invoice */
		$invoice = $this->getDoctrine()->getRepository(Invoice::class)->find($id);
		if(!$invoice) {
			$this->createNotFoundException();
		}

		$invoiceFactory->setInvoice($invoice);

		return new PdfResponse(
			$invoiceFactory->generate(),
			$invoiceFactory->getFilename()
		);
	}

	/**
	 * @Route("/{idOrder}/invoices/{id}/send", name="octopouce_shop_admin_order_show_invoice_send")
	 */
	public function invoiceSend($idOrder, $id, InvoiceInterface $invoiceFactory): Response
	{
		$order = $this->getDoctrine()->getRepository(Order::class)->find($idOrder);
		if(!$order) {
			$this->createNotFoundException();
		}

		/** @var Invoice $invoice */
		$invoice = $this->getDoctrine()->getRepository(Invoice::class)->find($id);
		if(!$invoice) {
			$this->createNotFoundException();
		}

		$invoiceFactory->setInvoice($invoice);

		$invoiceFactory->send();

		$this->addFlash('success', 'La dernière facture générée a été envoyée !');

		return $this->redirectToRoute('octopouce_shop_admin_order_show', ['id' => $idOrder]);
	}

	/**
	 * @Route("/{idOrder}/shipment/{id}", name="octopouce_shop_admin_order_shipment_update")
	 */
	public function shipmentUpdate($idOrder, $id, Request $request): Response
	{
		$shipment = $this->getDoctrine()->getRepository(Shipment::class)->find($id);
		if(!$shipment) {
			$this->createNotFoundException();
		}

		$form = $this->createForm(ShipmentType::class, $shipment);

		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid()) {
			$this->getDoctrine()->getManager()->flush();

			$this->addFlash('success', 'Adresse de livraison mis à jour');

			return $this->redirectToRoute('octopouce_shop_admin_order_show', ['id' => $idOrder]);
		}


		return $this->render('@OctopouceAdmin/Crud/edit.html.twig', [
			'entity' => $shipment,
			'form' => $form->createView()
		]);
	}

	/**
	 * @Route("/{idOrder}/billing/{id}", name="octopouce_shop_admin_order_billing_update")
	 */
	public function billingUpdate($idOrder, $id, Request $request): Response
	{
		$billing = $this->getDoctrine()->getRepository(Billing::class)->find($id);
		if(!$billing) {
			$this->createNotFoundException();
		}

		$form = $this->createForm(BillingType::class, $billing);

		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid()) {

			$this->getDoctrine()->getManager()->flush();

			$this->addFlash('success', 'Adresse de facturation mis à jour');

			return $this->redirectToRoute('octopouce_shop_admin_order_show', ['id' => $idOrder]);
		}


		return $this->render('@OctopouceAdmin/Crud/edit.html.twig', [
			'entity' => $billing,
			'form' => $form->createView()
		]);
	}

//	/**
//	 * @Route("/{idOrder}/item/new", name="admin_order_item_new")
//	 */
//	public function itemNew($idOrder, Request $request, EventDispatcherInterface $eventDispatcher, UserJournalInterface $userJournalFactory, UserSubscriptionInterface $userSubscriptionFactory): Response
//	{
//		$order = $this->getDoctrine()->getRepository(Order::class)->find($idOrder);
//		if(!$order) {
//			throw $this->createNotFoundException();
//		}
//
//		$item = new OrderItem();
//		$item->setOrder($order);
//		$form = $this->createForm(OrderItemType::class, $item);
//
//		$form->handleRequest($request);
//		if($form->isSubmitted() && $form->isValid()) {
//			$em = $this->getDoctrine()->getManager();
//
//			if($item->getJournal() && $item->getSubscription()) {
//				$this->addFlash('danger', 'L\'article ne peut pas contenir une revue et un abonnement.');
//			} elseif(!$item->getJournal() && !$item->getSubscription()) {
//				$this->addFlash('danger', 'L\'article ne peut pas contenir une revue et un abonnement.');
//			} else {
//
//				$em->persist($item);
//				$order->addItem($item);
//
//				if($order->getStates()[0]->getState()->isPaid()) {
//					if($item->getJournal()) {
//						$userJournalFactory->addJournal($item->getJournal(), $order);
//					} elseif($item->getSubscription()) {
//						$userSubscriptionFactory->addSubscription($item->getSubscription(), $order);
//					}
//				}
//
//
//				$event = new GenericEvent( $item->getOrder() );
//				$eventDispatcher->dispatch( OrderEvents::ORDER_UPDATED, $event );
//
//				$em->flush();
//
//				$this->addFlash('success', 'L\'article a été ajouté à la commande.');
//
//				return $this->redirectToRoute('admin_order_show', ['id' => $idOrder]);
//			}
//		}
//
//		return $this->render('admin/order/show/form-order-item.html.twig', [
//			'entity' => $item,
//			'form' => $form->createView()
//		]);
//	}
//
//	/**
//	 * @Route("/{idOrder}/item/{id}", name="admin_order_item_update")
//	 */
//	public function itemUpdate($idOrder, $id, Request $request, EventDispatcherInterface $eventDispatcher, UserJournalInterface $userJournalFactory, UserSubscriptionInterface $userSubscriptionFactory): Response
//	{
//		$item = $this->getDoctrine()->getRepository(OrderItem::class)->find($id);
//		if(!$item) {
//			throw $this->createNotFoundException();
//		}
//
//		$oldItem = clone $item;
//		$oldSubscription = clone $oldItem->getSubscription();
//
//		$form = $this->createForm(OrderItemType::class, $item);
//
//		$form->handleRequest($request);
//		if($form->isSubmitted() && $form->isValid()) {
//			$em = $this->getDoctrine()->getManager();
//
//			if($item->getJournal() && $item->getSubscription()) {
//				$this->addFlash('danger', 'L\'article ne peut pas contenir une revue et un abonnement.');
//			} else {
//				$em->persist($item);
//
//				if($item->getOrder()->getStates()[0]->getState()->isPaid()) {
//					if ( $oldItem->getJournal() ) {
//
//						// remove userJournal if journal is changed in order
//						if ( $oldItem->getJournal()->getId() !== $item->getJournal()->getId() ) {
//							$userJournalFactory->removeJournalByJournalAndOrderAndUser( $oldItem->getJournal(), $item->getOrder(), $item->getOrder()->getUser() );
//						}
//					} elseif ( $oldSubscription && ! $item->getSubscription() ) {
//
//						// remove userSubscription if subscription is dele ted in order
//						$userSubscriptionFactory->removeSubscriptionBySubscription( $oldSubscription );
//
//					} elseif ( $oldSubscription === $item->getSubscription() ) {
//						if ( $oldSubscription->getStartedAt() !== $item->getSubscription()->getStartedAt() ||
//						     $oldSubscription->getName() !== $item->getSubscription()->getName() ||
//						     $oldSubscription->getType() !== $item->getSubscription()->getType() ||
//						     $oldSubscription->getPeriod() !== $item->getSubscription()->getPeriod()) {
//
//							// update userSubscription if subscription is updated in order
//							$userSubscriptionFactory->updateSubscriptionBySubscription( $oldSubscription );
//						}
//					}
//				}
//
//
//				$event = new GenericEvent( $item->getOrder() );
//				$eventDispatcher->dispatch( OrderEvents::ORDER_UPDATED, $event );
//
//				$em->flush();
//
//
//				$this->addFlash('success', 'L\'article a été mis à jour.');
//
//				return $this->redirectToRoute('admin_order_show', ['id' => $idOrder]);
//			}
//		}
//
//		return $this->render('admin/order/show/form-order-item.html.twig', [
//			'entity' => $item,
//			'form' => $form->createView()
//		]);
//	}

	/**
	 * @Route("/{idOrder}/item/{id}/delete", name="octopouce_shop_admin_order_item_delete")
	 */
	public function itemDelete($idOrder, $id, EventDispatcherInterface $eventDispatcher, OrderFactory $orderFactory): Response
	{
		$item = $this->getDoctrine()->getRepository(OrderItem::class)->find($id);
		if(!$item) {
			throw $this->createNotFoundException();
		}

		$orderFactory->removeItem($item);
		$event = new GenericEvent( $item->getOrder() );
		$eventDispatcher->dispatch( OrderEvents::ORDER_UPDATED, $event );

		$this->getDoctrine()->getManager()->flush();

		$this->addFlash('success', 'L\'article a été enlevé de la commande.');

		return $this->redirectToRoute('octopouce_shop_admin_order_show', ['id' => $idOrder]);
	}



}

