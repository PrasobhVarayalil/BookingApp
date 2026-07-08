<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponse;

abstract class Controller
{
    use ApiResponse;
}
