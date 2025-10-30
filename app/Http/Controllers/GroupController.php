<?php

namespace App\Http\Controllers;

use App\Http\Requests\Groups\SearchGroupsRequest;
use App\Services\GroupService;

class GroupController extends Controller
{
    public function __construct(private readonly GroupService $groupService)
    {
    }

    public function searchGroups(SearchGroupsRequest $request)
    {
        $groups = $this->groupService->search($request->validated());

        if ($groups->isEmpty()) {
            return errorResponse('Группы не найдены', 404);
        }

        return successResponse($groups, 'Группы получены', 200);
    }
}
