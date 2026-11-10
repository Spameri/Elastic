<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;

/**
 * @template-covariant T of \Spameri\Elastic\Entity\ValueInterface
 * @template-extends \IteratorAggregate<T>
 */
interface ValueCollectionInterface extends \IteratorAggregate
{

}
