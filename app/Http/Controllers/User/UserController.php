<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AuditorOpdAssignment;
use App\Models\Opd;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Role internal Inspektorat yang dikelola lewat halaman ini.
     * Role sisi OPD (opd_admin, kepala_opd) tidak dikelola di sini.
     */
    private array $manageableRoles = ['admin', 'irban', 'operator_irban', 'inspektur'];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $roleFilter = $request->query('role', '');

        $query = User::role($this->manageableRoles)
            ->with(['auditorOpdAssignments.opd', 'parentIrban']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (in_array($roleFilter, $this->manageableRoles, true)) {
            $query->role($roleFilter);
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();

        $roleCounts = collect($this->manageableRoles)->mapWithKeys(fn ($role) => [$role => User::role($role)->count()]);

        return view('users.index', compact('users', 'search', 'roleFilter', 'roleCounts'));
    }

    public function create()
    {
        $opds = Opd::orderBy('nama_opd')->get();
        $irbanUsers = User::role('irban')->orderBy('name')->get();

        return view('users.create', compact('opds', 'irbanUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in($this->manageableRoles)],
            'opd_ids' => 'nullable|array',
            'opd_ids.*' => 'exists:opds,id',
            'parent_irban_id' => [
                Rule::requiredIf($request->input('role') === 'operator_irban'),
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value && ! User::role('irban')->whereKey($value)->exists()) {
                        $fail('Induk operator harus merupakan pengguna dengan peran Irban.');
                    }
                },
            ],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'parent_irban_id' => $validated['role'] === 'operator_irban' ? $validated['parent_irban_id'] : null,
            ]);
            $user->assignRole($validated['role']);

            if ($validated['role'] === 'irban') {
                foreach ($request->input('opd_ids', []) as $opdId) {
                    AuditorOpdAssignment::create(['user_id' => $user->id, 'opd_id' => $opdId]);
                }
            }

            ActivityLogger::log('create', 'user', "Menambahkan user: {$user->name} ({$user->email}), role: {$user->primary_role}", $user);

        });

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $opds = Opd::orderBy('nama_opd')->get();
        $irbanUsers = User::role('irban')->where('id', '!=', $user->id)->orderBy('name')->get();
        $assignedOpdIds = AuditorOpdAssignment::where('user_id', $user->id)->pluck('opd_id')->toArray();

        return view('users.edit', compact('user', 'opds', 'irbanUsers', 'assignedOpdIds'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => ['required', Rule::in($this->manageableRoles)],
            'opd_ids' => 'nullable|array',
            'opd_ids.*' => 'exists:opds,id',
            'parent_irban_id' => [
                Rule::requiredIf($request->input('role') === 'operator_irban'),
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value && ! User::role('irban')->whereKey($value)->exists()) {
                        $fail('Induk operator harus merupakan pengguna dengan peran Irban.');
                    }
                },
            ],
        ]);

        DB::transaction(function () use ($validated, $request, $user) {
            $admins = User::role('admin')->orderBy('id')->lockForUpdate()->get();
            if ($user->hasRole('admin') && $validated['role'] !== 'admin' && $admins->count() <= 1) {
                throw ValidationException::withMessages(['role' => 'Admin terakhir tidak dapat diganti perannya.']);
            }
            if ($validated['role'] !== 'irban' && User::where('parent_irban_id', $user->id)->exists()) {
                throw ValidationException::withMessages(['role' => 'Pindahkan operator ke Irban lain sebelum mengganti peran induk.']);
            }
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->parent_irban_id = $validated['role'] === 'operator_irban' ? $validated['parent_irban_id'] : null;

            if (! empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();
            $user->syncRoles([$validated['role']]);

            // Sinkronkan wilayah kerja (hanya relevan untuk irban)
            AuditorOpdAssignment::where('user_id', $user->id)->delete();
            if ($validated['role'] === 'irban') {
                foreach ($request->input('opd_ids', []) as $opdId) {
                    AuditorOpdAssignment::create(['user_id' => $user->id, 'opd_id' => $opdId]);
                }
            }

            ActivityLogger::log('update', 'user', "Memperbarui user: {$user->name} ({$user->email}), role: {$user->primary_role}", $user);

        });

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        DB::transaction(function () use ($user) {
            User::role('admin')->orderBy('id')->lockForUpdate()->get();
            ActivityLogger::log('delete', 'user', "Menghapus user: {$user->name} ({$user->email})", $user);
            $user->delete();
        });

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
