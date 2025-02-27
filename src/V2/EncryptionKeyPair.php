<?php
declare(strict_types=1);
namespace ParagonIE\HaliteLegacy\V2;

use ParagonIE\Halite\{
    Alerts as CryptoException,
    Asymmetric\EncryptionPublicKey,
    Asymmetric\EncryptionSecretKey
};

/**
 * Class EncryptionKeyPair
 *
 * Describes a pair of secret and public keys intended for encryption
 *
 * @package ParagonIE\Halite
 */
final class EncryptionKeyPair extends KeyPair
{
    /**
     * Pass it a secret key, it will automatically generate a public key
     *
     * @param Key[] $keys
     * @throws CryptoException\InvalidKey
     * @throws \InvalidArgumentException
     */
    public function __construct(Key ...$keys)
    {
        switch (\count($keys)) {
            /**
             * If we received two keys, it must be an asymmetric secret key and
             * an asymmetric public key, in either order.
             */
            case 2:
                if (!$keys[0]->isAsymmetricKey() || !$keys[1]->isAsymmetricKey()) {
                    throw new CryptoException\InvalidKey(
                        'Only keys intended for asymmetric cryptography can be used in a KeyPair object'
                    );
                }
                if ($keys[0]->isPublicKey()) {
                    if ($keys[1]->isPublicKey()) {
                        throw new CryptoException\InvalidKey(
                            'Both keys cannot be public keys'
                        );
                    }
                    $this->setupKeyPair(
                        $keys[1] instanceof EncryptionSecretKey
                            ? $keys[1]
                            : new EncryptionSecretKey($keys[1]->getRawKeyMaterial())
                    );
                } elseif ($keys[1]->isPublicKey()) {
                    $this->setupKeyPair(
                        $keys[0] instanceof EncryptionSecretKey
                            ? $keys[0]
                            : new EncryptionSecretKey($keys[0]->getRawKeyMaterial())
                    );
                } else {
                    throw new CryptoException\InvalidKey(
                        'Both keys cannot be secret keys'
                    );
                }
                break;
            /**
             * If we only received one key, it must be an asymmetric secret key!
             */
            case 1:
                if (!$keys[0]->isAsymmetricKey()) {
                    throw new CryptoException\InvalidKey(
                        'Only keys intended for asymmetric cryptography can be used in a KeyPair object'
                    );
                }
                if ($keys[0]->isPublicKey()) {
                    // Ever heard of the Elliptic Curve Discrete Logarithm Problem?
                    throw new CryptoException\InvalidKey(
                        'We cannot generate a valid keypair given only a public key; we can given only a secret key, however.'
                    );
                }
                $this->setupKeyPair(
                    $keys[0] instanceof EncryptionSecretKey
                        ? $keys[0]
                        : new EncryptionSecretKey($keys[0]->getRawKeyMaterial())
                );
                break;
            default:
                throw new \InvalidArgumentException(
                    'Halite\\EncryptionKeyPair expects 1 or 2 keys'
                );
        }
    }

    /**
     * Set up our key pair
     *
     * @param EncryptionSecretKey $secret
     */
    protected function setupKeyPair(EncryptionSecretKey $secret)
    {
        $this->secret_key = $secret;
        $this->public_key = $this->secret_key->derivePublicKey();
    }

    /**
     * Get a Key object for the public key
     *
     * @return EncryptionPublicKey
     */
    public function getPublicKey()
    {
        return parent::getPublicKey();
    }

    /**
     * Get a Key object for the public key
     *
     * @return EncryptionSecretKey
     */
    public function getSecretKey()
    {
        return parent::getSecretKey();
    }
}
