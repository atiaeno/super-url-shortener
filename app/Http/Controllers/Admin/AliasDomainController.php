<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AliasDomain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class AliasDomainController extends Controller
{
    public function index(): Response
    {
        $domains = AliasDomain::withCount('links')
            ->orderBy('is_default', 'desc')
            ->orderBy('is_active', 'desc')
            ->orderBy('domain')
            ->paginate(20);

        return Inertia::render('Admin/AliasDomains/Index', [
            'domains' => $domains,
        ]);
    }

    public function store(Request $Request)
    {
        $validator = Validator::make($Request->all(), [
            'domain' => 'required|string|max:255|unique:alias_domains,domain',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validate domain format
        if (!AliasDomain::validateDomain($Request->domain)) {
            return back()->withErrors(['domain' => 'Invalid domain format'])->withInput();
        }

        // If setting as default, unset all other defaults
        if ($Request->boolean('is_default')) {
            AliasDomain::where('is_default', true)->update(['is_default' => false]);
        }

        AliasDomain::create([
            'domain' => $Request->domain,
            'is_active' => $Request->boolean('is_active', true),
            'is_default' => $Request->boolean('is_default', false),
            'description' => $Request->description,
        ]);

        return back()->with('success', 'Domain added successfully.');
    }

    public function update(Request $request, AliasDomain $aliasDomain)
    {
        $validator = Validator::make($request->all(), [
            'domain' => 'required|string|max:255|unique:alias_domains,domain,' . $aliasDomain->id,
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validate domain format
        if (!AliasDomain::validateDomain($request->domain)) {
            return back()->withErrors(['domain' => 'Invalid domain format'])->withInput();
        }

        // If setting as default, unset all other defaults
        if ($request->boolean('is_default')) {
            AliasDomain::where('is_default', true)->update(['is_default' => false]);
        }

        $aliasDomain->update([
            'domain' => $request->domain,
            'is_active' => $request->boolean('is_active', $aliasDomain->is_active),
            'is_default' => $request->boolean('is_default', $aliasDomain->is_default),
            'description' => $request->description,
        ]);

        return back()->with('success', 'Domain updated successfully.');
    }

    public function destroy(AliasDomain $aliasDomain)
    {
        // Check if domain has links
        if ($aliasDomain->links()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete domain with existing links.']);
        }

        // Don't allow deleting the only default domain
        if ($aliasDomain->is_default && AliasDomain::where('is_default', true)->count() === 1) {
            return back()->withErrors(['error' => 'Cannot delete the default domain.']);
        }

        $aliasDomain->delete();

        return back()->with('success', 'Domain deleted successfully.');
    }

    public function toggleStatus(AliasDomain $aliasDomain)
    {
        $aliasDomain->update(['is_active' => !$aliasDomain->is_active]);

        $status = $aliasDomain->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Domain {$status} successfully.");
    }

    public function setDefault(AliasDomain $aliasDomain)
    {
        // Unset all other defaults
        AliasDomain::where('is_default', true)->update(['is_default' => false]);

        // Set this as default
        $aliasDomain->update(['is_default' => true]);

        return back()->with('success', 'Default domain updated successfully.');
    }
}
