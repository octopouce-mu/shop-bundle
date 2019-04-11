<?php
/**
 * Created by Kévin Hilairet <kevin@octopouce.mu>
 * Date: 2019-02-18
 */

namespace Octopouce\ShopBundle\Controller\Admin;

use Octopouce\ShopBundle\Entity\Order;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/carts")
 */
class CartController extends AbstractController
{
	/**
	 * @Route("/", name="octopouce_shop_admin_cart_index")
	 */
	public function index(): Response
	{
		$carts = $this->getDoctrine()->getRepository(Order::class)->findAllCarts();

		return $this->render('@OctopouceShop/Admin/cart/index.html.twig', [
			'entities' => $carts
		]);
	}

	/**
	 * @Route("/{id}", name="octopouce_shop_admin_cart_show")
	 */
	public function show($id): Response
	{
		$cart = $this->getDoctrine()->getRepository(Order::class)->find($id);
		if(!$cart) {
			$this->createNotFoundException();
		}

		return $this->render('@OctopouceShop/Admin/cart/show.html.twig', [
			'cart' => $cart,
		]);
	}
}

