<?php

namespace App\Exceptions;

use RuntimeException;

/** Raised when a coupon code is invalid. The message is safe to show to customers. */
class CouponException extends RuntimeException
{
}
