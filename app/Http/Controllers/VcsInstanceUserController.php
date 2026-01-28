<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VcsInstanceUserController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $users = DB::table('vcs_instance_users')
            ->select([
                'vcs_instance_users.id AS value',
                DB::raw("CONCAT(name, ' / ', username) AS label")
            ])
            ->join('vcs_instances', 'vcs_instance_users.vcs_instance_id', '=', 'vcs_instances.id')
            ->whereRaw("CONCAT(name, ' / ', username) LIKE ?", ["%{$request->query->getString('query')}%"])
            ->limit(10)
            ->get();

        return response()->json($users);
    }
}
