# Notes for comments.json

Because comments are not allowed in JSON this file is used.

`"symfony/cache": ">=6"` is part of the conflict section to avoid this error:

> PHP Fatal error:  Declaration of Symfony\Component\Cache\CacheItem::expiresAt(?DateTimeInterface $expiration): Symfony\Component\ErrorHandler\DebugClassLoader
> must be compatible with Psr\Cache\CacheItemInterface::expiresAt($expiration)

The code of this extension itself doesn't require `symfony/cache`.