<?php

namespace App\Exceptions;

use RuntimeException;

/** Raised when a cart / stock rule is violated. The message is safe to show to customers. */
class CartException extends RuntimeException
{
}
