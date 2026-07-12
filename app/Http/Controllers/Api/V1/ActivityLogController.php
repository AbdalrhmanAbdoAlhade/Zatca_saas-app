<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use App\Services\ActivityLogService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    use ApiResponse;

    public function __construct(protected ActivityLogService $activityLogService)
    {
    }

    public function index(Request $request)
    {
        $logs = $this->activityLogService->list($request->only(['module', 'user_id', 'per_page']));

        return $this->success(ActivityLogResource::collection($logs)->response()->getData(true));
    }

    public function show(ActivityLog $activityLog)
    {
        return $this->success(new ActivityLogResource($activityLog));
    }
}
