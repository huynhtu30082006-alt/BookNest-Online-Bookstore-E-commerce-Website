<?php

namespace App\Exceptions;

use RuntimeException;

/** Raised when an order action is not allowed. The message is safe to show to customers. */
class OrderException extends RuntimeException
{
}