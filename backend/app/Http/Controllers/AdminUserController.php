<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminUserController extends Controller
{
    private function ensureAdmin(Request $request): void
    {
        $u = $request->user();
        if (!$u || $u->role !== 'admin') {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $this->ensureAdmin($request);
        $q = User::query()
            ->when($request->search, function ($qq) use ($request) {
                $s = $request->search;
                $qq->where(function ($w) use ($s) {
                    $w->where('email', 'like', "%$s%");
                    $w->orWhere('name', 'like', "%$s%");
                });
            })
            ->orderByDesc('id')
            ;

        $per = (int)($request->input('per_page', 20));
        if ($per > 1000) { $per = 1000; }
        if ($per <= 0) { $per = 20; }
        $res = $q->paginate($per);
        return response()->json($res);
    }

    public function show(Request $request, $id)
    {
        $this->ensureAdmin($request);
        return User::findOrFail($id);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin($request);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'sometimes|in:free,premium,admin',
            'is_blocked' => 'sometimes|boolean',
            'free_daily_quota' => 'sometimes|integer|min:0',
        ]);
        $u = new User();
        $u->name = $data['name'];
        $u->email = $data['email'];
        $u->password = bcrypt($data['password']);
        $u->role = $data['role'] ?? 'free';
        $u->is_blocked = $data['is_blocked'] ?? false;
        if (array_key_exists('free_daily_quota', $data)) { $u->free_daily_quota = $data['free_daily_quota']; }
        $u->save();
        return response()->json($u, Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $this->ensureAdmin($request);
        $u = User::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $u->id,
            'password' => 'sometimes|nullable|string|min:8',
            'role' => 'sometimes|in:free,premium,admin',
            'is_blocked' => 'sometimes|boolean',
            'free_daily_quota' => 'sometimes|integer|min:0',
        ]);
        if (array_key_exists('password', $data) && $data['password']) {
            $u->password = bcrypt($data['password']);
        }
        unset($data['password']);
        $u->fill($data)->save();
        return response()->json($u);
    }

    public function destroy(Request $request, $id)
    {
        $this->ensureAdmin($request);
        if ($request->user()->id == $id) {
            return response()->json(['message' => 'Cannot delete self'], 422);
        }
        $u = User::findOrFail($id);
        $u->delete();
        return response()->noContent();
    }

    public function setRole(Request $request, $id)
    {
        $this->ensureAdmin($request);
        $data = $request->validate([
            'role' => 'required|in:free,premium,admin',
        ]);
        if ($request->user()->id == $id && $data['role'] !== 'admin') {
            return response()->json(['message' => 'Cannot demote self'], 422);
        }
        $u = User::findOrFail($id);
        $u->role = $data['role'];
        $u->save();
        return response()->json($u);
    }

    public function block(Request $request, $id)
    {
        $this->ensureAdmin($request);
        $data = $request->validate([
            'blocked' => 'required|boolean',
        ]);
        if ($request->user()->id == $id && $data['blocked'] === true) {
            return response()->json(['message' => 'Cannot block self'], 422);
        }
        $u = User::findOrFail($id);
        $u->is_blocked = $data['blocked'];
        $u->save();
        return response()->json($u);
    }
}
