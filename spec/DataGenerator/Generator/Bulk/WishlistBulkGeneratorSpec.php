<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
 */

declare(strict_types=1);

namespace spec\BitBag\SyliusVueStorefront2Plugin\DataGenerator\Generator\Bulk;

use BitBag\SyliusVueStorefront2Plugin\DataGenerator\ContextModel\DataGeneratorCommandContextInterface;
use BitBag\SyliusVueStorefront2Plugin\DataGenerator\ContextModel\Generator\GeneratorContextInterface;
use BitBag\SyliusVueStorefront2Plugin\DataGenerator\Exception\InvalidContextException;
use BitBag\SyliusVueStorefront2Plugin\DataGenerator\Generator\Bulk\WishlistBulkGenerator;
use BitBag\SyliusVueStorefront2Plugin\DataGenerator\Generator\Entity\GeneratorInterface;
use BitBag\SyliusWishlistPlugin\Entity\Wishlist;
use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;

final class WishlistBulkGeneratorSpec extends ObjectBehavior
{
    public function let(
        EntityManagerInterface $entityManager,
        GeneratorInterface $generator,
    ): void {
        $this->beConstructedWith($entityManager, $generator);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(WishlistBulkGenerator::class);
    }

    public function it_generates_wishlists(
        GeneratorContextInterface $context,
        GeneratorInterface $generator,
        WishlistInterface $wishlist,
        EntityManagerInterface $entityManager,
    ): void {
        $entityName = Wishlist::class;
        $quantity = 100;
        $flushAfter = 10;

        $context->getIO()->shouldBeCalled();
        $context->entityName()->willReturn($entityName);
        $context->getQuantity()->willReturn($quantity);

        for ($i = 1; $i <= $quantity; $i++) {
            $generator->generate($context)->willReturn($wishlist->getWrappedObject());

            $entityManager->persist($wishlist->getWrappedObject())->shouldBeCalled();

            if ($i % $flushAfter === 0) {
                $entityManager->flush()->shouldBeCalled();
            }
        }

        $entityManager->flush()->shouldBeCalled();

        $this->generate($context);
    }

    public function it_throws_exception_on_invalid_context(DataGeneratorCommandContextInterface $context): void
    {
        $this->shouldThrow(InvalidContextException::class)
            ->during('generate', [$context]);
    }
}
