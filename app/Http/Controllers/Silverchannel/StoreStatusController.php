<?php

declare(strict_types=1);

namespace App\Http\Controllers\Silverchannel;

use App\Http\Controllers\Controller;
use App\Services\StoreOperationalService;

class StoreStatusController extends Controller
{
    protected StoreOperationalService $storeOperationalService;

    public function __construct(StoreOperationalService $storeOperationalService)
    {
        $this->storeOperationalService = $storeOperationalService;
    }

    public function show()
    {
        $status = $this->storeOperationalService->getStatus();

        return response()->json($status);
    }
}

