<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir;

use Cline\Shamir\Conductors\CombineConductor;
use Cline\Shamir\Conductors\SplitConductor;

/**
 * Static facade for Shamir's Secret Sharing operations.
 *
 * Provides convenient static access to ShamirManager functionality.
 * For dependency injection or advanced usage, resolve ShamirManager
 * from the container directly.
 *
 * @method static bool             areCompatible(Share ...$shares)                    Verify shares are compatible
 * @method static string           combine(iterable<Share|string> $shares)            Reconstruct a secret from shares
 * @method static SplitConductor   for(string $secret)                                Begin fluent split operation
 * @method static CombineConductor from(iterable<Share|string> $shares)               Begin fluent combine operation
 * @method static Config           getConfig()                                        Get current configuration
 * @method static ShareCollection  split(string $secret, int $threshold, int $shares) Split a secret into shares
 * @method static ShamirManager    withConfig(Config $config)                         Create manager with different config
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see ShamirManager
 */
final class Shamir
{
    /**
     * The underlying manager instance.
     */
    private static ?ShamirManager $manager = null;

    /**
     * Forward static calls to the manager.
     *
     * Implements the magic method pattern to proxy all static method calls
     * to the underlying ShamirManager instance. This enables convenient
     * static API usage while maintaining proper dependency injection support.
     *
     * @param string            $method    Method name to call on the manager
     * @param array<int, mixed> $arguments Method arguments to forward
     *
     * @return mixed Result from the manager method call
     */
    public static function __callStatic(string $method, array $arguments): mixed
    {
        return self::getManager()->{$method}(...$arguments);
    }

    /**
     * Set the manager instance.
     *
     * Useful for testing or when custom configuration is needed.
     *
     * @param ShamirManager $manager Manager instance to use
     */
    public static function setManager(ShamirManager $manager): void
    {
        self::$manager = $manager;
    }

    /**
     * Get the manager instance.
     *
     * Creates a default manager if none exists. In Laravel applications,
     * this would typically be resolved from the service container.
     *
     * @return ShamirManager Active manager instance
     */
    private static function getManager(): ShamirManager
    {
        if (!self::$manager instanceof ShamirManager) {
            self::$manager = new ShamirManager();
        }

        return self::$manager;
    }
}
