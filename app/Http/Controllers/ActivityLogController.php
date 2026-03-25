<?php
namespace App\Http\Controllers;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct(
        private ActivityLogService $logService,
    ) {}

    public function index(Request $request)
    {
        $logs = $this->logService->getFiltered(
            $request->from,
            $request->to,
            $request->action,
            $request->user_id ? (int) $request->user_id : null,
        );
        $actions = $this->logService->getDistinctActions();
        $users = $this->logService->getUsers();

        return view('activity-logs.index', compact('logs', 'actions', 'users'));
    }
}
