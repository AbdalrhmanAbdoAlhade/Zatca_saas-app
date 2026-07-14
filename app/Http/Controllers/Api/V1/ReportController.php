<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\StoreReportRequest;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Services\ReportService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ApiResponse;

    public function __construct(protected ReportService $reportService)
    {
    }

    public function index(Request $request)
    {
        $reports = $this->reportService->list($request->only(['report_type', 'per_page']));

        return $this->success(ReportResource::collection($reports)->response()->getData(true));
    }

    public function store(StoreReportRequest $request)
    {
        $report = $this->reportService->requestGeneration($request->validated());

        return $this->success(new ReportResource($report), __('messages.report_queued'), 202);
    }

    public function show(Report $report)
    {
        return $this->success(new ReportResource($report));
    }

    public function destroy(Report $report)
    {
        $report->delete();

        return $this->success(null, __('messages.deleted_successfully'));
    }
}
