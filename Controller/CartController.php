<?php
namespace Octopouce\ShopBundle\Controller;

use Octopouce\ShopBundle\Entity\Discount;
use Octopouce\ShopBundle\Entity\Order;
use Octopouce\ShopBundle\Entity\OrderItem;
use Octopouce\ShopBundle\Factory\Invoice\InvoiceInterface;
use Octopouce\ShopBundle\Factory\Order\Factory\OrderFactory;
use Octopouce\ShopBundle\Factory\Order\Storage\OrderStorageInterface;
use Octopouce\ShopBundle\Form\CheckoutType;
use Octopouce\ShopBundle\Form\ClearCartType;
use Octopouce\ShopBundle\Form\QuantityItemType;
use Octopouce\ShopBundle\Form\RemoveItemType;
use Octopouce\ShopBundle\Form\SetDiscountType;
use JMS\Payment\CoreBundle\Form\ChoosePaymentMethodType;
use JMS\Payment\CoreBundle\Plugin\Exception\Action\VisitUrl;
use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use JMS\Payment\CoreBundle\Plugin\Exception\InternalErrorException;
use JMS\Payment\CoreBundle\PluginController\PluginController;
use JMS\Payment\CoreBundle\PluginController\Result;
use Octopouce\AdminBundle\Utils\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


class CartController extends AbstractController
{
	/**
	 * @var TranslatorInterface
	 */
	private $translator;

	/**
	 * @var OrderFactory
	 */
	private $orderFactory;

	public function __construct(TranslatorInterface  $translator, OrderFactory $orderFactory)
	{
		$this->translator = $translator;
		$this->orderFactory = $orderFactory;
	}

	/**
	 * @Route("/cart", name="cart_index")
	 */
	public function index()
	{
		$clearForm = $this->createForm(ClearCartType::class, $this->orderFactory->getCurrent());

		// TODO: remove item if user has already buy item
		// ...

		return $this->render('@OctopouceShop/cart/index.html.twig', [
			'order' => $this->orderFactory,
			'clearForm' => $clearForm->createView(),
			'itemsInCart' => $this->orderFactory->getCurrent()->getItemsTotal()
		]);
	}

	/**
	 * @Route("/cart/checkout", name="cart_checkout")
	 */
	public function cartCheckout(Request $request)
	{
		$order = $this->orderFactory->getCurrent();
		if(!$order || !$order->getItems()) {
			return $this->redirectToRoute('cart_index');
		}

		$user = $this->getUser();

		if(!$order->getBilling()) {
			$this->orderFactory->setDataBillingByUser();
		}

		$digital = true;
		foreach ($order->getItems() as $item) {
			if(!$item->getDigital()){
				$digital = false;
				break;
			}
		}

		if(!$order->getShipment() && !$digital) {
			$this->orderFactory->setDataShipmentByUser();
		}


		$form = $this->createForm(CheckoutType::class, $order, [
			'user' => $user
		]);
		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->flush();

			return $this->redirectToRoute('cart_checkout_payment');
		}

		return $this->render('@OctopouceShop/cart//checkout.html.twig', [
			'order' => $this->orderFactory,
			'itemsInCart' => $this->orderFactory->getCurrent()->getItemsTotal(),
			'form' => $form->createView()
		]);
	}

	/**
	 * @Route("/cart/checkout/payment", name="cart_checkout_payment")
	 */
	public function cartCheckoutPayment()
	{
		$order = $this->orderFactory->getCurrent();
		if(!$order || !$order->getItems()) {
			return $this->redirectToRoute('cart_index');
		}

		$this->orderFactory->setNumber();
		$this->getDoctrine()->getManager()->flush();

		$form = $this->paymentForm($order);

		return $this->render('@OctopouceShop/cart//payment.html.twig', [
			'order' => $this->orderFactory,
			'itemsInCart' => $order->getItemsTotal(),
			'form' => $form->createView(),
		]);
	}

	private function paymentForm($order)
	{
		$config = [
			'paypal_express_checkout' => [
				'return_url' => $this->generateUrl('cart_checkout_payment_create', [], UrlGeneratorInterface::ABSOLUTE_URL),
				'cancel_url' => $this->generateUrl('cart_checkout_payment', [], UrlGeneratorInterface::ABSOLUTE_URL),
				'useraction' => 'commit'
			],
			'paybox' => [
				'order_number' => $order->getNumber()
			]
		];

		$form = $this->createForm(ChoosePaymentMethodType::class, null, [
			'action' => $this->generateUrl('cart_checkout_payment_create'),
			'method' => 'POST',
			'amount'   => $order->getPriceTotal(),
			'currency' => 'EUR',
			'predefined_data' => $config,
			'method_options' => [
				'paypal_express_checkout' => [
					'label' => false,
				],
				'bank_wire'  => [
					'label' => false,
				],
				'check' => [
					'label' => false,
				]
			],
			'allowed_methods' => ['paybox', 'bank_wire', 'check']
		]);

		return $form;
	}

	/**
	 * @Route("cart/checkout/payment/create", name="cart_checkout_payment_create", methods={"POST"})
	 */
	public function paymentCreateAction(Request $request, PluginController $ppc, OrderStorageInterface $orderStorage, InvoiceInterface $invoiceFactory, MailerService $mailerService)
	{
		$order = $this->orderFactory->getCurrent();
		if(!$order || !$order->getItems()) {
			return $this->redirectToRoute('cart_index');
		}

		$number = $order->getNumber();

		$form = $this->paymentForm($order);
		$form->handleRequest($request);
		if (!$form->isSubmitted() || !$form->isValid()) {
			return $this->redirectToRoute('cart_checkout_payment');
		}

		$ppc->createPaymentInstruction($instruction = $form->getData());

		$order->setPaymentInstruction($instruction);

		$em = $this->getDoctrine()->getManager();
		$em->persist($order);
		$em->flush();


		$payment = $this->createPayment($order, $ppc);
		if(!$payment) {
			throw new InternalErrorException('Error payment system !');
		}

		$em = $this->getDoctrine()->getManager();

		$paymentSystemName = $payment->getPaymentInstruction()->getPaymentSystemName();

		$result = $ppc->approveAndDeposit($payment->getId(), $payment->getTargetAmount());


		if ($result->getStatus() === Result::STATUS_SUCCESS) {

			// create access journal for user and subscription by order
			$this->orderFactory->updateAccess();

			$em->flush();

			$invoiceFactory->createAndSend($order);
			$em->flush();

			$this->orderFactory->addState('payment_accepted');

			$mail = $this->renderView('cart/mails/order.html.twig', array(
				'order'  => $order,
				'user' => $order->getUser()
			));

			$mailerService->send(
				$order->getUser()->getEmail(),
				'Confirmation de votre commande n°'.$order->getNumber(),
				$mail
			);

			$orderStorage->remove();

			return $this->redirectToRoute('cart_payment_confirm', [
				'order' => $number,
			]);

		} elseif ($result->getStatus() === Result::STATUS_PENDING) {

			if($paymentSystemName === 'check' || $paymentSystemName === 'bank_wire') {

				$this->orderFactory->addState('awaiting_payment.'.$paymentSystemName);

				$em->flush();

				$mail = $this->renderView('cart/mails/order.html.twig', array(
					'order'  => $order,
					'user' => $order->getUser()
				));

				$mailerService->send(
					$order->getUser()->getEmail(),
					'Confirmation de votre commande n°'.$order->getNumber(),
					$mail
				);

				$orderStorage->remove();

				return $this->redirectToRoute('cart_payment_confirm', [
					'order' => $number,
				]);
			}

			$ex = $result->getPluginException();

			$this->orderFactory->addState('payment_pending');

			if ($ex instanceof ActionRequiredException) {
				$action = $ex->getAction();

				if ($action instanceof VisitUrl) {
					return $this->redirect($action->getUrl());
				}
			}

			$this->orderFactory->addState('payment_error');

		} else {
			$this->orderFactory->addState('payment_error');
		}

		$this->addFlash('danger', $this->translator->trans('state.payment_error') );

		return $this->redirectToRoute('cart_checkout_payment');

		throw $result->getPluginException();
	}

	/**
	 * @Route("/cart/{order}/payment/confirm", name="cart_payment_confirm")
	 */
	public function cartCheckoutPaymentConfirm($order)
	{
		$order = $this->getDoctrine()->getRepository(Order::class)->findOneByNumber($order);
		if(!$order) {
			throw $this->createNotFoundException();
		}

		return $this->render('@OctopouceShop/cart/confirm.html.twig', [
			'order' => $order
		]);
	}

	public function header(): Response
	{
		$response = $this->render('cart/header.html.twig', [
			'order' => $this->orderFactory,
			'itemsInCart' => $this->orderFactory->getCurrent()->getItemsTotal()
		]);
		$response->setSharedMaxAge(60);

		return $response;
	}


	public function removeItemForm(OrderItem $item): Response
	{
		$form = $this->createForm(RemoveItemType::class, $item);

		return $this->render('cart/remove_item_form.html.twig', [
			'form' => $form->createView()
		]);
	}

	public function quantityItemForm(OrderItem $item): Response
	{
		$form = $this->createForm(QuantityItemType::class, $item);

		return $this->render('cart/quantity_item_form.html.twig', [
			'item' => $item,
			'form' => $form->createView()
		]);
	}

	/**
	 * @Route("/cart/removeItem/{id}", name="cart_remove_item", methods={"POST"})
	 * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
	 */
	public function removeItem(Request $request, OrderItem $item): Response
	{
		$form = $this->createForm(RemoveItemType::class, $item);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$this->orderFactory->removeItem($item);

			$this->addFlash('success', $this->translator->trans('cart.remove_item.success'));
		}
		return $this->redirectToRoute('cart_index');
	}

	/**
	 * @Route("/cart/quantityItem/{id}", name="cart_quantity_item", methods={"POST"})
	 * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
	 */
	public function quantityItem(Request $request, OrderItem $item): Response
	{
		$form = $this->createForm(QuantityItemType::class, $item);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$form->get('less')->isClicked()
				? $this->orderFactory->removeItemQuantity($item)
				: $this->orderFactory->addItemQuantity($item);


			$this->addFlash('success', $this->translator->trans('cart.quantity.success'));
		}
		return $this->redirectToRoute('cart_index');
	}

	/**
	 * @Route("/cart/clear", name="cart_clear", methods={"POST"})
	 * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
	 */
	public function clear(Request $request): Response
	{
		$form = $this->createForm(ClearCartType::class, $this->orderFactory->getCurrent());
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$this->orderFactory->clear();

			$this->addFlash('success', $this->translator->trans('cart.clear.success'));
		}

		return $this->redirectToRoute('homepage');
	}

	/**
	 * @Route("/cart/discount", name="cart_set_discount", methods={"POST"})
	 */
	public function setDiscount(Request $request): Response
	{
		$form = $this->createForm(SetDiscountType::class, $this->orderFactory->getCurrent());
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$discount = $this->getDoctrine()->getRepository(Discount::class)->findOneBy([
				'code' => $form->get('discountCode')->getData()
			]);
			if(!$discount) {
				throw $this->createNotFoundException();
			}

			$this->orderFactory->setDiscount($discount);
			$this->addFlash('success', $this->translator->trans('app.cart.setDiscount.message.success'));

		}
		return $this->redirectToRoute('cart_index');
	}

	private function createPayment(Order $order, PluginController $ppc)
	{
		$instruction = $order->getPaymentInstruction();
		$pendingTransaction = $instruction->getPendingTransaction();

		if ($pendingTransaction !== null) {
			return $pendingTransaction->getPayment();
		}

		$amount = $instruction->getAmount() - $instruction->getDepositedAmount();

		return $ppc->createPayment($instruction->getId(), $amount);
	}
}
