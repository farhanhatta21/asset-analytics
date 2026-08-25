<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::when($request->search, fn($q, $s) => 
            $q->where(fn($sq) => $sq->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"))
        )->when($request->role, fn($q, $r) => 
            $q->where('role', $r)
        )->when($request->filled('status'), fn($q) => 
            $q->where('status', $request->status)
        )->latest()->paginate(10)->withQueryString();

        $statistics = [
            'total'  => User::count(),
            'admin'  => User::where('role', 'admin')->count(),
            'viewer' => User::where('role', 'viewer')->count(),
        ];

        return view('users.index', compact('users', 'statistics'));
    }    

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|min:3|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role'     => 'required|in:admin,viewer',
            'status'   => 'required|boolean',
        ]);

        $data['password'] = bcrypt($data['password']);
        User::create($data);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'   => 'required|max:100',
            'email'  => 'required|email|unique:users,email,' . $user->id,
            'role'   => 'required|in:admin,viewer',
            'status' => 'required|boolean'
        ]);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus');
    }
}