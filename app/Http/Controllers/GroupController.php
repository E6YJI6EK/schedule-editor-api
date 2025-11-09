<?php

namespace App\Http\Controllers;

use App\Http\Requests\Groups\SearchGroupsRequest;
use App\Http\Requests\Groups\SearchGroupsByNameRequest;
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

    public function searchGroupsByName(SearchGroupsByNameRequest $request)
    {
        $groups = $this->groupService->search($request->validated());

        if ($groups->isEmpty()) {
            return errorResponse('Группы не найдены', 404);
        }

        return successResponse($groups, 'Группы получены', 200);
    }
}
