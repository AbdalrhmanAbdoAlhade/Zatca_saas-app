<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Services\ActivityLogService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AdminActivityLogController extends Controller
{
    use ApiResponse;

    public function __construct(protected ActivityLogService $activityLogService)
    {
    }

    public function index(Request $request)
    {
        $logs = $this->activityLogService->list($request->only(['module', 'user_id', 'company_id', 'per_page']));

        return $this->success(ActivityLogResource::collection($logs)->response()->getData(true));
    }
}
