<?php

namespace spec\BitBag\SyliusVueStorefront2Plugin\CommandHandler\Wishlist;

use ApiPlatform\Core\Api\IriConverterInterface;
use BitBag\SyliusVueStorefront2Plugin\Command\Wishlist\AddItemToWishlist;
use BitBag\SyliusVueStorefront2Plugin\Command\Wishlist\RemoveItemFromWishlist;
use BitBag\SyliusVueStorefront2Plugin\CommandHandler\Wishlist\AddItemToWishlistHandler;
use BitBag\SyliusVueStorefront2Plugin\CommandHandler\Wishlist\RemoveItemFromWishlistHandler;
use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use BitBag\SyliusWishlistPlugin\Entity\WishlistProductInterface;
use BitBag\SyliusWishlistPlugin\Factory\WishlistProductFactoryInterface;
use BitBag\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\InvalidArgumentException;

class RemoveItemFromWishlistHandlerSpec extends ObjectBehavior
{
    public function let(
        ObjectManager $wishlistManager,
        IriConverterInterface $iriConverter,
        EventDispatcherInterface $eventDispatcher,
    ): void {
        $this->beConstructedWith(
            $wishlistManager,
            $iriConverter,
            $eventDispatcher
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(RemoveItemFromWishlistHandler::class);
    }

    public function it_is_invokable_for_existing_product_variant_in_wishlist(
        IriConverterInterface $iriConverter,
        WishlistInterface $wishlist,
        ProductVariantInterface $productVariant,
        WishlistProductFactoryInterface $wishlistProductFactory,
        WishlistProductInterface $wishlistProduct,
        ObjectManager $wishlistManager,
        EventDispatcherInterface $eventDispatcher,
    ): void {
        $removeItemFromWishlist = new RemoveItemFromWishlist('wishlistIri', 'productVariantIri');

        $iriConverter->getItemFromIri($removeItemFromWishlist->getId())->willReturn($wishlist);
        $iriConverter->getItemFromIri($removeItemFromWishlist->getProductVariant())->willReturn($productVariant);

        $wishlistProductFactory->createForWishlistAndVariant($wishlist, $productVariant)->willReturn($wishlistProduct);

        $wishlist->hasProductVariant($productVariant)->willReturn(true);
        $wishlist->removeProductVariant($productVariant)->shouldBeCalled();

        $wishlistManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::any(), RemoveItemFromWishlistHandler::EVENT_NAME)->shouldBeCalled();

        $this->__invoke($removeItemFromWishlist)->shouldReturn($wishlist);
    }

    public function it_is_invokable_for_not_existing_product_variant_in_wishlist(
        IriConverterInterface $iriConverter,
        WishlistInterface $wishlist,
        ProductVariantInterface $productVariant,
        WishlistProductFactoryInterface $wishlistProductFactory,
        WishlistProductInterface $wishlistProduct,
    ): void {
        $removeItemFromWishlist = new RemoveItemFromWishlist('wishlistIri', 'productVariantIri');

        $iriConverter->getItemFromIri($removeItemFromWishlist->getId())->willReturn($wishlist);
        $iriConverter->getItemFromIri($removeItemFromWishlist->getProductVariant())->willReturn($productVariant);

        $wishlistProductFactory->createForWishlistAndVariant($wishlist, $productVariant)->willReturn($wishlistProduct);

        $wishlist->hasProductVariant($productVariant)->willReturn(false);
        $wishlist->removeProductVariant($productVariant)->shouldNotBeCalled();

        $this->__invoke($removeItemFromWishlist)->shouldReturn($wishlist);
    }

    public function it_throws_an_exception_when_cannot_find_wishlist(
        IriConverterInterface $iriConverter,
    ): void {
        $removeItemFromWishlist = new RemoveItemFromWishlist('wishlistIri', 'productVariantIri');

        $iriConverter->getItemFromIri($removeItemFromWishlist->getId())->willReturn(null);

        $this->shouldThrow(InvalidArgumentException::class)
            ->during('__invoke', [$removeItemFromWishlist]);
    }
    public function it_throws_an_exception_when_cannot_find_product_variant(
        IriConverterInterface $iriConverter,
        WishlistInterface $wishlist,
    ): void {
        $removeItemFromWishlist = new RemoveItemFromWishlist('wishlistIri', 'productVariantIri');

        $iriConverter->getItemFromIri($removeItemFromWishlist->getId())->willReturn($wishlist);
        $iriConverter->getItemFromIri($removeItemFromWishlist->getProductVariant())->willReturn(null);

        $this->shouldThrow(InvalidArgumentException::class)
            ->during('__invoke', [$removeItemFromWishlist]);
    }
}
