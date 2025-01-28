<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        return view('admin.settings.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
            'avatar' => 'nullable|image|max:2048',
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'notifications_enabled' => 'boolean',
            'theme' => 'required|in:light,dark',
            'language' => 'required|in:pt_BR,en'
        ]);

        // Verifica a senha atual
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'A senha atual está incorreta.']);
            }
            $validated['password'] = Hash::make($request->new_password);
        }

        // Upload do avatar
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Remove campos não utilizados
        unset($validated['current_password']);
        unset($validated['new_password']);
        unset($validated['new_password_confirmation']);

        $user->update($validated);

        // Atualiza as preferências do usuário
        $user->preferences()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'notifications_enabled' => $request->boolean('notifications_enabled'),
                'theme' => $validated['theme'],
                'language' => $validated['language']
            ]
        );

        return redirect()->route('admin.settings')
            ->with('success', 'Configurações atualizadas com sucesso!');
    }

    public function deleteAvatar()
    {
        $user = auth()->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }

        return redirect()->route('admin.settings')
            ->with('success', 'Avatar removido com sucesso!');
    }
}
