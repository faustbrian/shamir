## Table of Contents

1. [Overview](#doc-docs-readme)
2. [Advanced Usage](#doc-docs-advanced-usage)
3. [Api Reference](#doc-docs-api-reference)
4. [Security](#doc-docs-security)
5. [Use Cases](#doc-docs-use-cases)
<a id="doc-docs-readme"></a>

A pure PHP implementation of Shamir's Secret Sharing scheme, allowing secrets to be split into N shares where any M shares can reconstruct the original secret.

## Overview

Shamir's Secret Sharing is a cryptographic algorithm that divides a secret into multiple parts (shares), where a minimum threshold of shares is required to reconstruct the original secret. Fewer than the threshold shares reveal zero information about the secret.

**Key Features:**
- Zero external dependencies (pure PHP + GMP extension)
- Split secrets into N shares, requiring K to reconstruct
- Information-theoretic security (shares below threshold reveal nothing)
- Automatic chunking for large secrets
- Built-in checksum validation
- Multiple encoding options (Base64, Hex)

## Requirements

Shamir v1.0 requires PHP 8.4+ and the GMP extension.

## Installation

Install Shamir with composer:

```bash
composer require cline/shamir
```

## Quick Start

### Basic Usage (Static API)

Split a secret into 5 shares where any 3 can reconstruct it:

```php
use Cline\Shamir\Shamir;

$secret = 'my-super-secret-encryption-key';
$shares = Shamir::split($secret, threshold: 3, shares: 5);

// Distribute shares to 5 different key holders
foreach ($shares as $share) {
    echo $share->toString() . "\n";
}
```

### Fluent API

Use the fluent conductor API for more expressive configuration:

```php
use Cline\Shamir\Shamir;

$secret = 'my-super-secret-encryption-key';

// Chain configuration methods
$shares = Shamir::for($secret)
    ->threshold(3)
    ->shares(5)
    ->split();

// Distribute shares
foreach ($shares as $share) {
    echo $share->toString() . "\n";
}
```

### Reconstructing Secrets

Collect any 3 shares and reconstruct the original secret:

```php
use Cline\Shamir\Shamir;
use Cline\Shamir\Share;

// Load shares from storage
$share1 = Share::fromString('1:3:checksum:encoded_value');
$share2 = Share::fromString('2:3:checksum:encoded_value');
$share3 = Share::fromString('3:3:checksum:encoded_value');

// Static API
$secret = Shamir::combine([$share1, $share2, $share3]);

// Or fluent API
$secret = Shamir::from([$share1, $share2, $share3])->combine();
```

### Binary Secrets

Works seamlessly with binary data like encryption keys:

```php
// Generate a 256-bit encryption key
$encryptionKey = random_bytes(32);

// Split into shares
$shares = Shamir::split($encryptionKey, threshold: 3, shares: 5);

// Reconstruct
$reconstructed = Shamir::combine($shares->take(3));

assert($reconstructed === $encryptionKey);
```

## Share Operations

### Share Serialization

Shares can be serialized for storage or transmission:

```php
// String format (compact, portable)
$shareString = $share->toString();  // "1:3:checksum:value"
$share = Share::fromString($shareString);

// JSON format
$json = json_encode($share);
$share = Share::fromArray(json_decode($json, true));
```

### Share Collections

The `ShareCollection` provides utilities for working with multiple shares:

```php
$shares = Shamir::split($secret, 3, 5);

// Take first N shares
$subset = $shares->take(3);

// Get random shares
$random = $shares->random(3);

// Access by index
$share = $shares->get(2);

// Iterate
foreach ($shares as $share) {
    // Process share
}
```

## Configuration

### Using ShamirManager

For advanced usage or dependency injection, use the manager directly:

```php
use Cline\Shamir\Config;
use Cline\Shamir\ShamirManager;

$config = new Config(
    prime: Config::PRIME_256,    // Field size (128, 256, 512)
    encoding: 'base64',          // Encoding (base64, hex)
);

$manager = new ShamirManager($config);

// Direct API
$shares = $manager->split($secret, 3, 5);

// Fluent API
$shares = $manager->for($secret)
    ->threshold(3)
    ->shares(5)
    ->split();
```

### Creating Manager with Different Config

```php
use Cline\Shamir\Config;
use Cline\Shamir\Shamir;

$hexConfig = new Config(
    prime: Config::PRIME_512,
    encoding: 'hex',
);

$manager = Shamir::withConfig($hexConfig);
$shares = $manager->split($secret, 3, 5);
```

## Common Use Cases

### 1. Master Key Protection

Split a master encryption key across multiple custodians:

```php
$masterKey = random_bytes(32);

// Static API
$shares = Shamir::split($masterKey, threshold: 3, shares: 5);

// Fluent API (more expressive)
$shares = Shamir::for($masterKey)
    ->threshold(3)
    ->shares(5)
    ->split();

// Distribute to 5 different security officers
// Require 3 to unlock critical systems
```

### 2. Backup Encryption

Require multiple parties to decrypt backups:

```php
$backupKey = generateBackupKey();

$shares = Shamir::for($backupKey)
    ->threshold(2)
    ->shares(3)
    ->split();

// Store shares in different locations
// Require 2 locations to restore backups
```

### 3. Disaster Recovery

Distribute recovery keys to trusted parties:

```php
$recoveryKey = generateRecoveryKey();

$shares = Shamir::for($recoveryKey)
    ->threshold(3)
    ->shares(7)
    ->split();

// Distribute to board members
// Require majority (3 of 7) to recover
```

## Next Steps

- [API Reference](#doc-docs-api-reference) - Complete API documentation
- [Security Considerations](#doc-docs-security) - Security best practices
- [Advanced Usage](#doc-docs-advanced-usage) - Advanced patterns and techniques
- [Use Cases](#doc-docs-use-cases) - Detailed use case examples

<a id="doc-docs-advanced-usage"></a>

Advanced usage patterns, integration strategies, and optimization techniques.

## Progressive Secret Reconstruction

Build a system that progressively collects shares until threshold is reached:

```php
use Cline\Shamir\Shamir;
use Cline\Shamir\Share;

class ProgressiveReconstruction
{
    private array $collectedShares = [];
    private int $threshold;

    public function addShare(string $shareString): array
    {
        $share = Share::fromString($shareString);

        // Store threshold from first share
        if (empty($this->collectedShares)) {
            $this->threshold = $share->getThreshold();
        }

        // Validate compatibility
        if ($share->getThreshold() !== $this->threshold) {
            throw new \RuntimeException('Incompatible share threshold');
        }

        // Check for duplicates
        foreach ($this->collectedShares as $existing) {
            if ($existing->getIndex() === $share->getIndex()) {
                throw new \RuntimeException('Duplicate share');
            }
        }

        $this->collectedShares[] = $share;

        return [
            'progress' => count($this->collectedShares),
            'threshold' => $this->threshold,
            'complete' => count($this->collectedShares) >= $this->threshold,
        ];
    }

    public function canReconstruct(): bool
    {
        return count($this->collectedShares) >= $this->threshold;
    }

    public function reconstruct(): ?string
    {
        if (!$this->canReconstruct()) {
            return null;
        }

        // Static API
        return Shamir::combine($this->collectedShares);

        // Or fluent API
        return Shamir::from($this->collectedShares)->combine();
    }

    public function reset(): void
    {
        $this->collectedShares = [];
        $this->threshold = 0;
    }
}

// Usage
$reconstructor = new ProgressiveReconstruction();

// Operators submit shares one by one
$status = $reconstructor->addShare($operatorShare1);
// ['progress' => 1, 'threshold' => 3, 'complete' => false]

$status = $reconstructor->addShare($operatorShare2);
// ['progress' => 2, 'threshold' => 3, 'complete' => false]

$status = $reconstructor->addShare($operatorShare3);
// ['progress' => 3, 'threshold' => 3, 'complete' => true]

if ($reconstructor->canReconstruct()) {
    $secret = $reconstructor->reconstruct();
}
```

## Time-Based Share Activation

Implement shares that become active only after a certain time:

```php
use Cline\Shamir\Shamir;

class TimeLockedShares
{
    public function createTimeLockedShares(
        string $secret,
        int $threshold,
        int $shares,
        \DateTimeInterface $activationTime
    ): array {
        // Create shares - static API
        $secretShares = Shamir::split($secret, $threshold, $shares);

        // Or fluent API
        $secretShares = Shamir::for($secret)
            ->threshold($threshold)
            ->shares($shares)
            ->split();

        $timeLockedShares = [];
        foreach ($secretShares as $share) {
            $timeLockedShares[] = [
                'share' => $share->toString(),
                'activation_time' => $activationTime->format('c'),
                'activated' => false,
            ];
        }

        return $timeLockedShares;
    }

    public function attemptReconstruction(array $timeLockedShares): string
    {
        $now = new \DateTimeImmutable();
        $activeShares = [];

        foreach ($timeLockedShares as $locked) {
            $activationTime = new \DateTimeImmutable($locked['activation_time']);

            if ($now < $activationTime) {
                throw new \RuntimeException(
                    "Share not yet active. Available at {$locked['activation_time']}"
                );
            }

            $activeShares[] = Share::fromString($locked['share']);
        }

        // Static API
        return Shamir::combine($activeShares);

        // Or fluent API
        return Shamir::from($activeShares)->combine();
    }
}

// Create shares that activate in 24 hours
$timeLocked = new TimeLockedShares();
$shares = $timeLocked->createTimeLockedShares(
    secret: $masterKey,
    threshold: 3,
    shares: 5,
    activationTime: new \DateTimeImmutable('+24 hours')
);

// Will fail if attempted before activation time
try {
    $secret = $timeLocked->attemptReconstruction($shares);
} catch (\RuntimeException $e) {
    echo "Shares not yet active\n";
}
```

## Hierarchical Secret Sharing

Create a hierarchy where different groups have different access levels:

```php
use Cline\Shamir\Shamir;

class HierarchicalSharing
{
    public function createHierarchy(string $secret): array
    {
        // Level 1: Executives (2 of 3 needed) - static API
        $execShares = Shamir::split($secret, threshold: 2, shares: 3);

        // Or fluent API for more expressive configuration
        $execShares = Shamir::for($secret)->threshold(2)->shares(3)->split();

        // Level 2: Management (3 of 5 needed)
        $mgmtShares = Shamir::for($secret)->threshold(3)->shares(5)->split();

        // Level 3: Board (5 of 7 needed)
        $boardShares = Shamir::for($secret)->threshold(5)->shares(7)->split();

        return [
            'executives' => $execShares,
            'management' => $mgmtShares,
            'board' => $boardShares,
        ];
    }
}

// Any group can reconstruct independently
$hierarchy = new HierarchicalSharing();
$levels = $hierarchy->createHierarchy($masterKey);

// Executives can unlock quickly (only need 2) - static API
$secret = Shamir::combine($levels['executives']->take(2));

// Or fluent API
$secret = Shamir::from($levels['executives']->take(2))->combine();

// Management can unlock (need 3)
$secret = Shamir::from($levels['management']->take(3))->combine();

// Board can unlock (need 5)
$secret = Shamir::from($levels['board']->take(5))->combine();
```

## Share Refresh Without Reconstructing

Update shares without reconstructing the secret (advanced):

```php
use Cline\Shamir\Shamir;

class ShareRefreshService
{
    /**
     * Refresh shares by adding a zero-polynomial.
     * This changes all share values while keeping the secret the same.
     */
    public function refreshShares(array $oldShares): array
    {
        // This requires reconstructing the secret
        // For true proactive secret sharing without reconstruction,
        // you'd need a more complex protocol

        // For this implementation, reconstruct and re-split - static API
        $secret = Shamir::combine($oldShares);
        $newShares = Shamir::split(
            $secret,
            threshold: $oldShares[0]->getThreshold(),
            shares: count($oldShares)
        );

        // Or using fluent API
        $secret = Shamir::from($oldShares)->combine();
        $newShares = Shamir::for($secret)
            ->threshold($oldShares[0]->getThreshold())
            ->shares(count($oldShares))
            ->split();

        // Clear secret from memory
        sodium_memzero($secret);

        return $newShares;
    }
}
```

## Integration with Laravel Queue

Process share distribution asynchronously:

```php
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Cline\Shamir\Shamir;

class DistributeSharesJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $secret,
        private array $recipients,
        private int $threshold,
    ) {}

    public function handle(): void
    {
        // Static API
        $shares = Shamir::split(
            $this->secret,
            $this->threshold,
            count($this->recipients)
        );

        // Or fluent API
        $shares = Shamir::for($this->secret)
            ->threshold($this->threshold)
            ->shares(count($this->recipients))
            ->split();

        foreach ($shares as $i => $share) {
            $recipient = $this->recipients[$i];

            // Send via secure channel
            $this->sendSecurely($recipient, $share);

            // Log distribution
            Log::info('Share distributed', [
                'recipient' => $recipient['id'],
                'share_index' => $share->getIndex(),
            ]);
        }
    }

    private function sendSecurely($recipient, $share): void
    {
        // Encrypt with recipient's public key
        $encrypted = $this->encryptForRecipient($share, $recipient);

        // Send via secure channel
        Mail::to($recipient['email'])->send(
            new ShareDistributionMail($encrypted)
        );
    }
}

// Dispatch job
DistributeSharesJob::dispatch($masterKey, $recipients, 3);
```

## Database Storage Pattern

Store encrypted shares in database:

```php
use Illuminate\Database\Eloquent\Model;
use Cline\Shamir\Share;

class SecretShare extends Model
{
    protected $fillable = ['user_id', 'share_index', 'encrypted_share', 'threshold'];

    protected $casts = [
        'share_index' => 'integer',
        'threshold' => 'integer',
    ];

    public function setShareAttribute(Share $share): void
    {
        $this->attributes['share_index'] = $share->getIndex();
        $this->attributes['threshold'] = $share->getThreshold();
        $this->attributes['encrypted_share'] = encrypt($share->toString());
    }

    public function getShareAttribute(): Share
    {
        $decrypted = decrypt($this->attributes['encrypted_share']);
        return Share::fromString($decrypted);
    }
}

// Store shares - static API
$shares = Shamir::split($secret, 3, 5);

// Or fluent API
$shares = Shamir::for($secret)->threshold(3)->shares(5)->split();

foreach ($shares as $share) {
    SecretShare::create([
        'user_id' => $userId,
        'share' => $share,
    ]);
}

// Reconstruct - static API
$shares = SecretShare::where('user_id', $userId)->get();
$secret = Shamir::combine($shares->pluck('share'));

// Or fluent API
$secret = Shamir::from($shares->pluck('share'))->combine();
```

## Custom Encoding

Use custom encoding for specialized requirements:

```php
use Cline\Shamir\Config;
use Cline\Shamir\Shamir;

// Use hex encoding for compatibility with systems that don't support base64
$config = new Config(
    prime: Config::PRIME_256,
    encoding: 'hex'
);

// Using manager with custom config
$manager = Shamir::withConfig($config);
$shares = $manager->split($secret, 3, 5);

// Or fluent API with custom manager
$shares = $manager->for($secret)->threshold(3)->shares(5)->split();

// Shares will use hex encoding instead of base64
```

## Testing Reconstruction Before Distribution

Verify shares can reconstruct before distributing:

```php
use Cline\Shamir\Shamir;

class ShareVerificationService
{
    public function createAndVerifyShares(string $secret, int $threshold, int $shares): array
    {
        // Create shares - fluent API
        $shareCollection = Shamir::for($secret)
            ->threshold($threshold)
            ->shares($shares)
            ->split();

        // Test reconstruction with exactly threshold shares
        $testReconstructed = Shamir::from($shareCollection->take($threshold))->combine();

        if ($testReconstructed !== $secret) {
            throw new \RuntimeException('Share reconstruction test failed');
        }

        // Test all possible combinations (for small share counts)
        if ($shares <= 7) {
            $this->verifyAllCombinations($secret, $shareCollection, $threshold);
        }

        return $shareCollection->toArray();
    }

    private function verifyAllCombinations($secret, $shares, $threshold): void
    {
        $allShares = $shares->toArray();
        $combinations = $this->generateCombinations($allShares, $threshold);

        foreach ($combinations as $combo) {
            $reconstructed = Shamir::combine($combo);
            if ($reconstructed !== $secret) {
                throw new \RuntimeException('Combination failed verification');
            }
        }
    }
}
```

## Audit Trail

Implement comprehensive audit logging:

```php
class AuditedShamirService
{
    public function split(string $secret, int $threshold, int $shares): ShareCollection
    {
        $this->log('share_split_initiated', [
            'threshold' => $threshold,
            'total_shares' => $shares,
        ]);

        // Using fluent API for better readability
        $shareCollection = Shamir::for($secret)
            ->threshold($threshold)
            ->shares($shares)
            ->split();

        $this->log('share_split_completed', [
            'share_count' => $shareCollection->count(),
        ]);

        return $shareCollection;
    }

    public function combine(array $shares): string
    {
        $this->log('reconstruction_initiated', [
            'share_count' => count($shares),
            'share_indices' => array_map(fn($s) => $s->getIndex(), $shares),
        ]);

        try {
            // Using fluent API
            $secret = Shamir::from($shares)->combine();

            $this->log('reconstruction_successful', [
                'share_count' => count($shares),
            ]);

            return $secret;
        } catch (\Exception $e) {
            $this->log('reconstruction_failed', [
                'error' => $e->getMessage(),
                'share_count' => count($shares),
            ]);

            throw $e;
        }
    }

    private function log(string $event, array $context): void
    {
        Log::channel('security')->info($event, array_merge($context, [
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'timestamp' => now(),
        ]));
    }
}
```

## Performance Optimization

For large-scale operations:

```php
class OptimizedShamirService
{
    private array $shareCache = [];

    public function splitWithCaching(string $secretId, string $secret): ShareCollection
    {
        // Check cache first
        if (isset($this->shareCache[$secretId])) {
            return $this->shareCache[$secretId];
        }

        // Generate shares - fluent API
        $shares = Shamir::for($secret)->threshold(3)->shares(5)->split();

        // Cache result
        $this->shareCache[$secretId] = $shares;

        return $shares;
    }

    public function batchSplit(array $secrets): array
    {
        $results = [];

        foreach ($secrets as $id => $secret) {
            // Fluent API for expressive batch operations
            $results[$id] = Shamir::for($secret)->threshold(3)->shares(5)->split();
        }

        return $results;
    }
}
```

## Best Practices Summary

1. **Always verify shares** before distribution
2. **Log all operations** for audit trail
3. **Clear secrets** from memory after use
4. **Test reconstruction** in disaster recovery drills
5. **Version shares** for rotation tracking
6. **Encrypt shares** at rest and in transit
7. **Rate limit** reconstruction attempts
8. **Monitor** for unusual access patterns

<a id="doc-docs-api-reference"></a>

Complete API documentation for the Shamir package.

## Shamir Facade

Static facade providing convenient access to ShamirManager functionality.

### `split()`

Split a secret into shares (static API).

```php
public static function split(
    string $secret,
    int $threshold,
    int $shares,
): ShareCollection
```

**Parameters:**
- `$secret` - The secret to split (any binary string)
- `$threshold` - Minimum shares needed to reconstruct (k)
- `$shares` - Total number of shares to generate (n)

**Returns:** `ShareCollection` - Collection of generated shares

**Throws:**
- `InvalidArgumentException` - If threshold > shares or threshold < 2

**Example:**
```php
$shares = Shamir::split('my-secret', threshold: 3, shares: 5);
```

### `for()`

Begin fluent split operation.

```php
public static function for(string $secret): SplitConductor
```

**Parameters:**
- `$secret` - The secret to split

**Returns:** `SplitConductor` - Fluent conductor for configuration

**Example:**
```php
$shares = Shamir::for('my-secret')
    ->threshold(3)
    ->shares(5)
    ->split();
```

### `combine()`

Reconstruct a secret from shares (static API).

```php
public static function combine(iterable $shares): string
```

**Parameters:**
- `$shares` - Iterable of Share objects or share strings

**Returns:** `string` - The reconstructed secret

**Throws:**
- `InsufficientSharesException` - If fewer shares than threshold
- `InvalidShareException` - If share format is invalid or checksum fails
- `IncompatibleSharesException` - If shares are from different splits

**Example:**
```php
$secret = Shamir::combine([$share1, $share2, $share3]);
```

### `from()`

Begin fluent combine operation.

```php
public static function from(iterable $shares): CombineConductor
```

**Parameters:**
- `$shares` - Iterable of Share objects or share strings

**Returns:** `CombineConductor` - Fluent conductor for combination

**Example:**
```php
$secret = Shamir::from([$share1, $share2, $share3])->combine();
```

### `withConfig()`

Create manager with different configuration.

```php
public static function withConfig(Config $config): ShamirManager
```

**Parameters:**
- `$config` - Configuration instance

**Returns:** `ShamirManager` - New manager with config

**Example:**
```php
$config = new Config(prime: Config::PRIME_512, encoding: 'hex');
$manager = Shamir::withConfig($config);
$shares = $manager->split('secret', 3, 5);
```

### `areCompatible()`

Check if shares are from the same split operation.

```php
public static function areCompatible(Share ...$shares): bool
```

**Parameters:**
- `...$shares` - Variable number of Share objects

**Returns:** `bool` - True if shares have the same threshold

**Example:**
```php
if (Shamir::areCompatible($share1, $share2, $share3)) {
    // Safe to combine
}
```

## ShamirManager

Central manager for orchestrating secret sharing operations.

### Constructor

```php
public function __construct(private Config $config = new Config())
```

### `split()`

Split a secret into shares.

```php
public function split(
    string $secret,
    int $threshold,
    int $shares,
): ShareCollection
```

### `combine()`

Reconstruct a secret from shares.

```php
public function combine(iterable $shares): string
```

### `for()`

Begin fluent split operation.

```php
public function for(string $secret): SplitConductor
```

### `from()`

Begin fluent combine operation.

```php
public function from(iterable $shares): CombineConductor
```

### `withConfig()`

Create new manager with different config.

```php
public function withConfig(Config $config): self
```

### `areCompatible()`

Verify shares are compatible.

```php
public function areCompatible(Share ...$shares): bool
```

### `getConfig()`

Get current configuration.

```php
public function getConfig(): Config
```

## Conductors

### SplitConductor

Fluent API for configuring split operations.

#### `threshold()`

Set minimum shares needed.

```php
public function threshold(int $threshold): self
```

#### `shares()`

Set total shares to generate.

```php
public function shares(int $shares): self
```

#### `split()`

Execute the split operation.

```php
public function split(): ShareCollection
```

**Example:**
```php
$shares = $manager->for($secret)
    ->threshold(3)
    ->shares(5)
    ->split();
```

### CombineConductor

Fluent API for combine operations.

#### `combine()`

Execute the combine operation.

```php
public function combine(): string
```

**Example:**
```php
$secret = $manager->from($shares)->combine();
```

## Share Class

Pure value object representing a single share in the secret sharing scheme.

### Constructor

```php
public function __construct(
    private int $index,
    private string $value,
    private int $threshold,
    private string $checksum,
)
```

### Methods

#### `getIndex()`

```php
public function getIndex(): int
```

Get the share's index (1-based).

#### `getValue()`

```php
public function getValue(): string
```

Get the encoded share value.

#### `getThreshold()`

```php
public function getThreshold(): int
```

Get the threshold required to reconstruct the secret.

#### `getChecksum()`

```php
public function getChecksum(): string
```

Get the share's checksum for validation.

#### `toString()`

```php
public function toString(): string
```

Serialize to portable string format: `"index:threshold:checksum:value"`

Delegates to ShareSerializer internally.

**Example:**
```php
$shareString = $share->toString();
// "1:3:abc123...:encoded_value"
```

#### `fromString()`

```php
public static function fromString(string $encoded): self
```

Parse a share from string format.

Delegates to ShareSerializer internally.

**Throws:** `InvalidShareException` - If format is invalid

**Example:**
```php
$share = Share::fromString('1:3:checksum:value');
```

#### `fromArray()`

```php
public static function fromArray(array $data): self
```

Create share from array (JSON deserialization).

Delegates to ShareSerializer internally.

**Throws:** `InvalidShareException` - If required fields are missing

**Example:**
```php
$data = json_decode($json, true);
$share = Share::fromArray($data);
```

#### `jsonSerialize()`

```php
public function jsonSerialize(): array
```

Serialize to array for JSON encoding.

Delegates to ShareSerializer internally.

**Example:**
```php
$json = json_encode($share);
```

## ShareSerializer

Service for serializing and deserializing Share objects.

### `toString()`

Serialize a share to string format.

```php
public function toString(Share $share): string
```

### `fromString()`

Deserialize a share from string format.

```php
public function fromString(string $encoded): Share
```

**Throws:** `InvalidShareException`

### `toArray()`

Serialize a share to array format.

```php
public function toArray(Share $share): array
```

### `fromArray()`

Deserialize a share from array format.

```php
public function fromArray(array $data): Share
```

**Throws:** `InvalidShareException`

**Example:**
```php
$serializer = new ShareSerializer();

// String serialization
$string = $serializer->toString($share);
$share = $serializer->fromString($string);

// Array serialization
$array = $serializer->toArray($share);
$share = $serializer->fromArray($array);
```

## ShareCollection Class

Collection of shares with utility methods.

### Constructor

```php
public function __construct(private array $shares)
```

### Methods

#### `get()`

```php
public function get(int $index): Share
```

Get share by index (1-based).

**Throws:** `InvalidShareException` - If share not found

#### `take()`

```php
public function take(int $count): self
```

Take first N shares.

#### `random()`

```php
public function random(int $count): self
```

Get N random shares.

**Throws:** `InvalidArgumentException` - If count > available shares

#### `count()`

```php
public function count(): int
```

Get total number of shares.

#### `toArray()`

```php
public function toArray(): array
```

Convert to plain array of Share objects.

#### `forDistribution()`

```php
public function forDistribution(): array
```

Get shares in randomized order, indexed by share number.

**Returns:** `array<int, Share>` - Shares indexed by their index

**Example:**
```php
foreach ($shares->forDistribution() as $index => $share) {
    sendToKeyHolder($index, $share);
}
```

## Config Class

Configuration options for Shamir operations.

### Constructor

```php
public function __construct(
    public string $prime = Config::PRIME_256,
    public string $encoding = 'base64',
)
```

### Constants

#### Prime Field Sizes

```php
// 128-bit prime (secrets up to ~16 bytes per chunk)
Config::PRIME_128

// 256-bit prime (secrets up to ~32 bytes per chunk) - DEFAULT
Config::PRIME_256

// 512-bit prime (larger secrets)
Config::PRIME_512
```

### Properties

#### `$prime`

The prime number defining the Galois field size.

#### `$encoding`

Encoding format for share values: `'base64'` (default) or `'hex'`.

### Example

```php
$config = new Config(
    prime: Config::PRIME_512,
    encoding: 'hex',
);

$shares = Shamir::split($secret, 3, 5, $config);
```

## Exceptions

### `ShamirException`

Base exception for all Shamir-related errors.

### `InsufficientSharesException`

Thrown when fewer shares than threshold are provided.

**Static Constructor:**
```php
InsufficientSharesException::notEnoughShares(int $provided, int $required)
```

### `InvalidShareException`

Thrown when share format is invalid or checksum fails.

**Static Constructors:**
```php
InvalidShareException::invalidFormat(string $encoded)
InvalidShareException::missingRequiredFields()
InvalidShareException::shareNotFound(int $index)
InvalidShareException::checksumMismatch()
```

### `IncompatibleSharesException`

Thrown when shares are from different split operations.

**Static Constructors:**
```php
IncompatibleSharesException::differentThresholds()
IncompatibleSharesException::differentChecksums()
```

### `SecretTooLargeException`

Thrown when secret exceeds field size (rarely occurs due to automatic chunking).

**Static Constructor:**
```php
SecretTooLargeException::exceedsFieldSize(int $secretSize, int $maxSize)
```

## Type Reference

### Iterables

The `combine()` method accepts any iterable of shares:

```php
// Array of Share objects
Shamir::combine([$share1, $share2, $share3]);

// ShareCollection
Shamir::combine($shares->take(3));

// Array of strings
Shamir::combine([
    '1:3:checksum:value1',
    '2:3:checksum:value2',
    '3:3:checksum:value3',
]);

// Mixed
Shamir::combine([
    $shareObject,
    '2:3:checksum:value',
    Share::fromString('3:3:checksum:value'),
]);
```

<a id="doc-docs-security"></a>

Important security considerations and best practices for using Shamir's Secret Sharing in production systems.

## Information-Theoretic Security

Shamir's Secret Sharing provides **information-theoretic security**, meaning:

- Any combination of shares below the threshold reveals **zero information** about the secret
- This security doesn't rely on computational hardness (unlike RSA, AES)
- Even with unlimited computing power, shares below threshold are useless

### What This Means

```php
// With threshold = 3, shares = 5
$shares = Shamir::split($secret, 3, 5);

// An attacker with ANY 2 shares knows NOTHING about the secret
// All possible secrets are equally likely
$twoShares = [$shares->get(1), $shares->get(2)];
// ^ Completely useless to an attacker
```

## Share Protection

### Individual Share Security

**Critical**: Each share must be protected as if it were the secret itself.

❌ **Don't:**
```php
// Storing shares in plaintext
file_put_contents('/tmp/share1.txt', $share->toString());

// Logging shares
$logger->info('Share created: ' . $share->toString());

// Sending shares unencrypted
$email->send($recipient, $share->toString());
```

✅ **Do:**
```php
// Encrypt shares before storage
$encrypted = encryptShare($share->toString(), $userPassword);
$vault->store($encrypted);

// Use secure channels for distribution
$secureMessaging->sendEncrypted($recipient, $share->toString());

// Store in hardware security modules (HSMs)
$hsm->storeShare($share);
```

### Storage Best Practices

1. **Geographic Distribution** - Store shares in different physical locations
2. **Access Control** - Implement strict access controls on share storage
3. **Audit Logging** - Log all share access and reconstruction attempts
4. **Encryption at Rest** - Encrypt shares even in "secure" storage
5. **No Co-location** - Never store threshold number of shares in same system

## Checksum Validation

Shares include SHA-256 checksums to detect corruption or tampering:

```php
use Cline\Shamir\Exception\InvalidShareException;

try {
    $secret = Shamir::combine($shares);
} catch (InvalidShareException $e) {
    // Share was corrupted or tampered with
    $logger->alert('Share checksum validation failed', [
        'error' => $e->getMessage(),
        'timestamp' => now(),
    ]);
}
```

**Note**: Checksums detect **accidental corruption**, not malicious modification. An attacker with access to modify a share could recompute the checksum.

## Memory Safety

### Clearing Sensitive Data

```php
// After reconstructing a secret, clear it from memory when done
$secret = Shamir::combine($shares);
useSecret($secret);

// Clear secret from memory
sodium_memzero($secret);
```

### Avoiding Logs

```php
// Never log secrets or shares
❌ Log::info('Secret: ' . $secret);
❌ Log::debug('Share: ' . $share->toString());

// Be careful with exception messages
try {
    $secret = Shamir::combine($shares);
} catch (\Exception $e) {
    ✅ Log::error('Secret reconstruction failed', [
        'error_type' => get_class($e),
        // Don't include exception message if it might contain shares
    ]);
}
```

## Threshold Selection

Choose threshold based on security vs availability trade-off:

### Too Low Threshold

```php
// Threshold = 2, Shares = 10
// Risk: Only need to compromise 2 shares
$shares = Shamir::split($secret, threshold: 2, shares: 10);
```

**Problems:**
- Lower security bar
- Easier for attacker to gather enough shares
- Useful for: High availability scenarios

### Too High Threshold

```php
// Threshold = 9, Shares = 10
// Risk: Losing 2 shares makes secret unrecoverable
$shares = Shamir::split($secret, threshold: 9, shares: 10);
```

**Problems:**
- Low fault tolerance
- Secret lost if enough shares unavailable
- Useful for: Maximum security scenarios

### Balanced Approach

```php
// Threshold = 3, Shares = 5
// Good balance: Need 3 to reconstruct, can lose 2
$shares = Shamir::split($secret, threshold: 3, shares: 5);

// For higher security
// Threshold = 5, Shares = 7
$shares = Shamir::split($secret, threshold: 5, shares: 7);
```

## Randomness

The implementation uses PHP's `random_bytes()` for cryptographically secure randomness:

```php
// Polynomial coefficients are generated with random_bytes()
// Ensures unpredictable polynomial construction
```

**Requirements:**
- System must have access to good entropy source
- Never use `rand()` or `mt_rand()` for security purposes
- On Linux, ensure `/dev/urandom` is available

## Share Distribution

### Secure Distribution Channels

```php
// ✅ Good: Separate secure channels for each share
$share1 = $shares->get(1);
$share2 = $shares->get(2);

sendViaEncryptedEmail($recipient1, $share1);
sendViaSMS($recipient2, $share2); // Different channel
deliverInPerson($recipient3, $shares->get(3)); // Physical delivery
```

### Avoid Common Pitfalls

❌ **Don't:**
- Email all shares to same recipient
- Store shares in same database
- Send shares via same communication channel
- Keep shares in same datacenter

✅ **Do:**
- Use different distribution methods
- Geographic distribution
- Multiple authentication factors for access
- Time-delayed distribution for additional security

## Access Control

### Multi-Factor Authentication

```php
class SecureShareAccess
{
    public function retrieveShare(User $user, string $mfaToken): Share
    {
        // Require MFA
        if (!$this->verifyMFA($user, $mfaToken)) {
            throw new UnauthorizedException();
        }

        // Log access
        $this->auditLog->record([
            'user' => $user->id,
            'action' => 'share_access',
            'timestamp' => now(),
            'ip' => request()->ip(),
        ]);

        return $this->loadShare($user);
    }
}
```

### Rate Limiting

```php
class ShareReconstructionService
{
    public function combine(array $shares): string
    {
        // Rate limit reconstruction attempts
        if ($this->tooManyAttempts()) {
            throw new RateLimitException('Too many reconstruction attempts');
        }

        $this->recordAttempt();

        return Shamir::combine($shares);
    }

    private function tooManyAttempts(): bool
    {
        // Allow max 3 attempts per hour
        return Cache::get('reconstruction_attempts') >= 3;
    }
}
```

## Rotation Strategy

Periodically rotate shares for maximum security:

```php
class ShareRotationService
{
    public function rotateShares(string $secret): ShareCollection
    {
        // Generate new shares
        $newShares = Shamir::split($secret, threshold: 3, shares: 5);

        // Distribute new shares
        $this->distributeNewShares($newShares);

        // After confirming receipt, destroy old shares
        $this->revokeOldShares();

        return $newShares;
    }
}

// Rotate quarterly or after suspected compromise
$rotationService->rotateShares($masterKey);
```

## Field Size Considerations

Choose appropriate field size for your secrets:

```php
use Cline\Shamir\Config;

// Default: 256-bit field (recommended for most use cases)
$shares = Shamir::split($secret, 3, 5);

// Larger field for very large secrets
$config = new Config(prime: Config::PRIME_512);
$shares = Shamir::split($largeSecret, 3, 5, $config);
```

**Recommendations:**
- **PRIME_256** (default): Suitable for keys up to ~30 bytes per chunk
- **PRIME_512**: For very large secrets or extra security margin
- Automatic chunking handles secrets of any size

## Production Checklist

Before deploying to production:

- [ ] Shares are encrypted at rest
- [ ] Shares are distributed across different locations/systems
- [ ] Access to shares requires multi-factor authentication
- [ ] All share access is logged and monitored
- [ ] Reconstruction attempts are rate-limited
- [ ] Secrets are cleared from memory after use
- [ ] No secrets or shares are logged
- [ ] Threshold provides acceptable security/availability balance
- [ ] Disaster recovery process is documented and tested
- [ ] Share rotation schedule is established
- [ ] Audit trail is immutable and monitored

## Threat Model

### Protected Against

✅ **Single point of compromise** - Attacker needs threshold shares
✅ **Partial information leakage** - Shares below threshold reveal nothing
✅ **Accidental share loss** - Can reconstruct with remaining shares
✅ **Share corruption** - Checksums detect tampering/corruption

### Not Protected Against

❌ **Threshold shares compromised** - If attacker gets threshold shares, game over
❌ **Malicious share holders** - Threshold malicious insiders can reconstruct
❌ **Side-channel attacks** - Physical access to reconstruction process
❌ **Implementation bugs** - Use audited crypto libraries for encryption

## Additional Hardening

### Combine with Other Security Measures

```php
// Use Shamir + HSM storage
$shares = Shamir::split($masterKey, 3, 5);
foreach ($shares as $share) {
    $hsm->secureStore($share);
}

// Use Shamir + Password-based encryption
$encryptedShares = [];
foreach ($shares as $i => $share) {
    $password = $shareHolders[$i]->password;
    $encryptedShares[] = encryptWithPassword($share, $password);
}

// Use Shamir + Time-locked encryption
// Shares can only be used after specific time
foreach ($shares as $share) {
    $timeLocked = timeLockEncrypt($share, $unlockTime);
    store($timeLocked);
}
```

## Compliance

Shamir's Secret Sharing helps meet various compliance requirements:

- **PCI DSS**: Key management and dual control
- **HIPAA**: Protected health information key management
- **SOC 2**: Access controls and key custody
- **GDPR**: Data protection and encryption key management

Always consult with compliance experts for your specific requirements.

<a id="doc-docs-use-cases"></a>

Real-world examples demonstrating how to use Shamir's Secret Sharing to solve common security challenges.

## Master Key Protection

Split a master encryption key across multiple custodians to prevent single points of failure.

### Scenario

You have a master key that encrypts all sensitive data. If this key is compromised or lost, your entire system is at risk. By splitting it using Shamir's scheme, you ensure that:
- No single person can decrypt data alone
- The key can be recovered if some custodians are unavailable
- Compromise of individual shares reveals nothing

### Implementation

```php
use Cline\Shamir\Shamir;

// Generate master encryption key
$masterKey = random_bytes(32); // 256-bit AES key

// Split into 5 shares, requiring 3 to reconstruct - static API
$shares = Shamir::split($masterKey, threshold: 3, shares: 5);

// Or fluent API for more expressive configuration
$shares = Shamir::for($masterKey)->threshold(3)->shares(5)->split();

// Distribute to key custodians
$custodians = [
    'CEO' => $shares->get(1),
    'CTO' => $shares->get(2),
    'CFO' => $shares->get(3),
    'Security Officer' => $shares->get(4),
    'Compliance Officer' => $shares->get(5),
];

foreach ($custodians as $role => $share) {
    // Store securely (HSM, encrypted file, password manager)
    storeShareSecurely($role, $share->toString());
}

// To reconstruct the key, collect any 3 shares
$collectedShares = [
    loadShareFromStorage('CEO'),
    loadShareFromStorage('CTO'),
    loadShareFromStorage('CFO'),
];

// Static API
$reconstructedKey = Shamir::combine($collectedShares);

// Or fluent API
$reconstructedKey = Shamir::from($collectedShares)->combine();

// Use $reconstructedKey to decrypt data
```

### Best Practices

1. **Geographic Distribution** - Store shares in different physical locations
2. **Role Separation** - Distribute to people with different responsibilities
3. **Secure Storage** - Use HSMs, encrypted containers, or secure vaults
4. **Access Logging** - Log all share access and reconstruction attempts
5. **Regular Rotation** - Periodically re-split with new shares

## Backup Encryption

Require multiple parties to decrypt backup archives.

### Scenario

Your application creates encrypted backups. You want to ensure that:
- Backups can only be restored with approval from multiple parties
- A single compromised backup key doesn't expose all historical data
- Backups remain accessible even if some key holders are unavailable

### Implementation

```php
use Cline\Shamir\Shamir;

class BackupManager
{
    public function createBackup(string $data): array
    {
        // Generate unique encryption key for this backup
        $encryptionKey = random_bytes(32);

        // Encrypt the backup
        $encryptedData = sodium_crypto_secretbox(
            $data,
            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),
            $encryptionKey
        );

        // Split the encryption key - fluent API
        $shares = Shamir::for($encryptionKey)->threshold(2)->shares(3)->split();

        return [
            'encrypted_data' => base64_encode($nonce . $encryptedData),
            'key_shares' => [
                'primary_admin' => $shares->get(1)->toString(),
                'backup_admin' => $shares->get(2)->toString(),
                'disaster_recovery' => $shares->get(3)->toString(),
            ],
        ];
    }

    public function restoreBackup(string $encryptedData, array $keyShares): string
    {
        // Require at least 2 shares
        if (count($keyShares) < 2) {
            throw new \RuntimeException('Need 2 administrators to restore backup');
        }

        // Reconstruct encryption key - fluent API
        $encryptionKey = Shamir::from($keyShares)->combine();

        // Decrypt backup
        $decoded = base64_decode($encryptedData);
        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $data = sodium_crypto_secretbox_open($ciphertext, $nonce, $encryptionKey);

        if ($data === false) {
            throw new \RuntimeException('Backup decryption failed');
        }

        return $data;
    }
}
```

## Cryptocurrency Wallet Protection

Multi-signature-like key management for crypto wallets.

### Scenario

You're managing a cryptocurrency wallet for a DAO or organization. Requirements:
- Multiple signers must approve transactions
- Wallet remains accessible if some signers are unavailable
- Private key is never stored in a single location

### Implementation

```php
use Cline\Shamir\Shamir;

class WalletManager
{
    public function createMultiSigWallet(int $requiredSigners, int $totalSigners): array
    {
        // Generate wallet private key
        $privateKey = generatePrivateKey(); // 32 bytes

        // Split into shares - fluent API
        $shares = Shamir::for($privateKey)
            ->threshold($requiredSigners)
            ->shares($totalSigners)
            ->split();

        return [
            'public_address' => derivePublicAddress($privateKey),
            'shares' => $shares->forDistribution(),
        ];
    }

    public function signTransaction(array $signerShares, string $transaction): string
    {
        // Reconstruct private key from collected shares - fluent API
        $privateKey = Shamir::from($signerShares)->combine();

        // Sign transaction
        $signature = signTransaction($transaction, $privateKey);

        // Zero out private key from memory
        sodium_memzero($privateKey);

        return $signature;
    }
}

// Usage
$wallet = new WalletManager();

// Create 3-of-5 multisig wallet
$setup = $wallet->createMultiSigWallet(requiredSigners: 3, totalSigners: 5);

// Distribute shares to board members
distributeShares($setup['shares']);

// To sign a transaction, collect 3 shares
$signature = $wallet->signTransaction(
    signerShares: [
        loadShare('member_1'),
        loadShare('member_2'),
        loadShare('member_3'),
    ],
    transaction: $transactionData
);
```

## Vault Seal/Unseal Mechanism

Implement a HashiCorp Vault-style seal/unseal mechanism.

### Scenario

Your application has a "sealed" state where all encrypted data is inaccessible. To "unseal" (make data accessible), a quorum of operators must provide their key shares.

### Implementation

```php
use Cline\Shamir\Shamir;

class VaultManager
{
    private ?string $unsealedKey = null;

    public function initialize(int $threshold, int $totalKeys): array
    {
        // Generate master encryption key
        $masterKey = random_bytes(32);

        // Split into shares - fluent API
        $shares = Shamir::for($masterKey)->threshold($threshold)->shares($totalKeys)->split();

        // Encrypt and store master key
        $this->storeSealedKey($masterKey);

        // Return shares for distribution
        return $shares->forDistribution();
    }

    public function submitUnsealKey(string $shareString): array
    {
        $share = Share::fromString($shareString);
        $threshold = $share->getThreshold();

        // Store share temporarily
        $this->storeUnsealShare($share);

        // Get all submitted shares
        $shares = $this->getSubmittedShares();

        if (count($shares) >= $threshold) {
            // Attempt unseal - fluent API
            try {
                $this->unsealedKey = Shamir::from($shares)->combine();
                $this->clearSubmittedShares();

                return [
                    'sealed' => false,
                    'progress' => count($shares),
                    'threshold' => $threshold,
                ];
            } catch (\Exception $e) {
                return [
                    'error' => 'Invalid share combination',
                    'sealed' => true,
                ];
            }
        }

        return [
            'sealed' => true,
            'progress' => count($shares),
            'threshold' => $threshold,
        ];
    }

    public function seal(): void
    {
        // Zero out unsealed key
        if ($this->unsealedKey !== null) {
            sodium_memzero($this->unsealedKey);
            $this->unsealedKey = null;
        }

        $this->clearSubmittedShares();
    }

    public function isSealed(): bool
    {
        return $this->unsealedKey === null;
    }
}
```

## Compliance & Dual Control

Enforce dual-control requirements for sensitive operations.

### Scenario

Regulatory compliance requires two authorized personnel to approve certain operations (e.g., wire transfers, data exports, system changes).

### Implementation

```php
use Cline\Shamir\Shamir;

class DualControlSystem
{
    public function protectSensitiveOperation(callable $operation): string
    {
        // Generate one-time operation key
        $operationKey = random_bytes(32);

        // Split into 2 shares (threshold = 2, shares = 2) - fluent API
        $shares = Shamir::for($operationKey)->threshold(2)->shares(2)->split();

        // Encrypt operation data with operation key
        $encryptedOp = $this->encryptOperation($operation, $operationKey);

        // Return shares and encrypted operation
        return [
            'operation_id' => uniqid('op_'),
            'encrypted_operation' => $encryptedOp,
            'approver_1_share' => $shares->get(1)->toString(),
            'approver_2_share' => $shares->get(2)->toString(),
        ];
    }

    public function executeWithApproval(
        string $encryptedOperation,
        string $share1,
        string $share2
    ): mixed {
        // Reconstruct operation key - fluent API
        $operationKey = Shamir::from([
            Share::fromString($share1),
            Share::fromString($share2),
        ])->combine();

        // Decrypt and execute operation
        $operation = $this->decryptOperation($encryptedOperation, $operationKey);

        // Log dual approval
        $this->logApprovals($share1, $share2);

        // Execute
        return $operation();
    }
}

// Usage
$dualControl = new DualControlSystem();

// Protect a wire transfer
$protected = $dualControl->protectSensitiveOperation(function() use ($amount, $recipient) {
    return $this->bank->wireTransfer($amount, $recipient);
});

// Send shares to two different approvers
sendToApprover1($protected['approver_1_share']);
sendToApprover2($protected['approver_2_share']);

// When both approve, execute
$result = $dualControl->executeWithApproval(
    $protected['encrypted_operation'],
    $approver1Share,
    $approver2Share
);
```

## Disaster Recovery Keys

Distribute recovery keys to trusted parties for emergency access.

### Implementation

```php
use Cline\Shamir\Shamir;

class DisasterRecoveryManager
{
    public function setupRecoveryKeys(): array
    {
        // Generate recovery master key
        $recoveryKey = random_bytes(32);

        // Split into 7 shares, requiring 3 for recovery - fluent API
        $shares = Shamir::for($recoveryKey)->threshold(3)->shares(7)->split();

        // Distribute to trusted parties
        $distribution = [
            'Board Member 1' => $shares->get(1),
            'Board Member 2' => $shares->get(2),
            'Board Member 3' => $shares->get(3),
            'External Auditor' => $shares->get(4),
            'Legal Counsel' => $shares->get(5),
            'Bank Safety Deposit' => $shares->get(6),
            'Secure Backup Location' => $shares->get(7),
        ];

        // Store encrypted recovery procedures with recovery key
        $this->storeRecoveryProcedures($recoveryKey);

        return $distribution;
    }

    public function executeRecovery(array $shares): array
    {
        // Require at least 3 shares
        if (count($shares) < 3) {
            throw new \RuntimeException('Need 3 recovery key holders for disaster recovery');
        }

        // Reconstruct recovery key - fluent API
        $recoveryKey = Shamir::from($shares)->combine();

        // Decrypt recovery procedures
        $procedures = $this->decryptRecoveryProcedures($recoveryKey);

        // Execute recovery steps
        return $this->executeRecoverySteps($procedures);
    }
}
```

## Key Points

1. **Threshold Selection**: Choose threshold based on availability vs security trade-off
2. **Share Distribution**: Never store all shares in the same location or system
3. **Secure Storage**: Protect individual shares as if they were the secret itself
4. **Access Control**: Log and monitor all share access and combination attempts
5. **Regular Testing**: Periodically verify that shares can successfully reconstruct secrets
6. **Rotation**: Consider rotating shares periodically for high-security scenarios
