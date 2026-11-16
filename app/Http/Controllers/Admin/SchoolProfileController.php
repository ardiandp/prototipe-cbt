<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSchoolProfileRequest;
use App\Models\SchoolProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SchoolProfileController extends Controller
{
    /**
     * Display the school profile page.
     */
    public function show(): View
    {
        $profile = SchoolProfile::current();

        return view('admin.school-profile.index', [
            'profile' => $profile,
        ]);
    }

    /**
     * Update the school profile.
     */
    public function update(UpdateSchoolProfileRequest $request): RedirectResponse
    {
        $profile = SchoolProfile::current();

        $profile->update($request->validated());

        return redirect()->route('admin.school.profile')
            ->with('success', 'Profil sekolah berhasil diperbarui.');
    }
}

