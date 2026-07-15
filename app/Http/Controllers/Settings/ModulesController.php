<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Services\BusinessContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Nwidart\Modules\Facades\Module;

class ModulesController extends Controller
{
    /**
     * Show the modules settings form.
     */
    public function edit(Request $request): Response
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $businessId = BusinessContextService::getCurrentBusinessId() ?: ($request->user()->business_id ?? 1);
        $business = Business::findOrFail($businessId);

        $allModules = [];
        foreach (Module::all() as $module) {
            $allModules[] = [
                'name' => $module->getName(),
                'description' => $module->get('description') ?: "Manage {$module->getName()} functionality",
            ];
        }

        // Get enabled modules from business column
        $enabledModules = array_values($business->enabled_modules ?: []);

        // One-time migration: if settings table still has enabled_modules, merge and update the business column, then delete from settings
        $enabledSetting = DB::table('settings')->where('business_id', $businessId)->where('key', 'enabled_modules')->first();
        if ($enabledSetting) {
            $settingModules = array_values(json_decode($enabledSetting->value, true) ?: []);
            $enabledModules = array_values(array_unique(array_merge($enabledModules, $settingModules)));
            $business->enabled_modules = $enabledModules;
            $business->save();
            DB::table('settings')->where('business_id', $businessId)->where('key', 'enabled_modules')->delete();
        }

        // Get installed modules from settings table, fall back to all modules if missing
        $installedSetting = DB::table('settings')->where('business_id', $businessId)->where('key', 'installed_modules')->first();
        if ($installedSetting) {
            $installedModules = array_values(json_decode($installedSetting->value, true) ?: []);
        } else {
            $installedModules = array_values(array_map(fn($m) => $m->getName(), Module::all()));
            DB::table('settings')->updateOrInsert(
                ['business_id' => $businessId, 'key' => 'installed_modules'],
                ['value' => json_encode($installedModules), 'updated_at' => now(), 'created_at' => now()]
            );
        }

        return Inertia::render('settings/Modules', [
            'modules' => $allModules,
            'enabled_modules' => array_values($enabledModules),
            'installed_modules' => array_values($installedModules),
        ]);
    }

    /**
     * Update the enabled modules list.
     */
    public function update(Request $request): RedirectResponse
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $request->validate([
            'enabled_modules' => 'nullable|array',
            'enabled_modules.*' => 'string',
        ]);

        $businessId = BusinessContextService::getCurrentBusinessId() ?: ($request->user()->business_id ?? 1);
        $business = Business::findOrFail($businessId);

        $enabled = $request->input('enabled_modules') ?: [];

        $business->enabled_modules = array_values($enabled);
        $business->save();

        return back()->with('toast', [
            'type' => 'success',
            'message' => '⚙️ Enabled modules updated successfully!',
        ]);
    }

    /**
     * Install a module.
     */
    public function install(Request $request): RedirectResponse
    {
        abort_if(! $request->user()->isAdmin(), 403);
        $moduleName = $request->input('module');

        $businessId = BusinessContextService::getCurrentBusinessId() ?: ($request->user()->business_id ?? 1);

        $installedSetting = DB::table('settings')->where('business_id', $businessId)->where('key', 'installed_modules')->first();
        $installedModules = $installedSetting ? array_values(json_decode($installedSetting->value, true) ?: []) : [];

        if (!in_array($moduleName, $installedModules)) {
            $installedModules[] = $moduleName;
            DB::table('settings')->updateOrInsert(
                ['business_id' => $businessId, 'key' => 'installed_modules'],
                ['value' => json_encode(array_values($installedModules)), 'updated_at' => now()]
            );

            // Dynamically run migrations for this module if they exist
            try {
                \Illuminate\Support\Facades\Artisan::call('module:migrate', ['module' => $moduleName]);
            } catch (\Exception $e) {
                // Ignore failure if command or migration folder is absent
            }
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => "📦 Module {$moduleName} installed successfully!",
        ]);
    }

    /**
     * Uninstall a module.
     */
    public function uninstall(Request $request): RedirectResponse
    {
        abort_if(! $request->user()->isAdmin(), 403);
        $moduleName = $request->input('module');

        $businessId = BusinessContextService::getCurrentBusinessId() ?: ($request->user()->business_id ?? 1);

        // Remove from installed
        $installedSetting = DB::table('settings')->where('business_id', $businessId)->where('key', 'installed_modules')->first();
        $installedModules = $installedSetting ? array_values(json_decode($installedSetting->value, true) ?: []) : [];
        $installedModules = array_values(array_diff($installedModules, [$moduleName]));
        DB::table('settings')->updateOrInsert(
            ['business_id' => $businessId, 'key' => 'installed_modules'],
            ['value' => json_encode($installedModules), 'updated_at' => now()]
        );

        // Remove from enabled directly in business
        $business = Business::find($businessId);
        if ($business) {
            $enabledModules = array_values($business->enabled_modules ?: []);
            $enabledModules = array_values(array_diff($enabledModules, [$moduleName]));
            $business->enabled_modules = $enabledModules;
            $business->save();
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => "🗑️ Module {$moduleName} uninstalled successfully!",
        ]);
    }

    /**
     * Enable a module.
     */
    public function enable(Request $request): RedirectResponse
    {
        abort_if(! $request->user()->isAdmin(), 403);
        $moduleName = $request->input('module');

        $businessId = BusinessContextService::getCurrentBusinessId() ?: ($request->user()->business_id ?? 1);

        $business = Business::find($businessId);
        if ($business) {
            $enabledModules = array_values($business->enabled_modules ?: []);
            if (!in_array($moduleName, $enabledModules)) {
                $enabledModules[] = $moduleName;
                $business->enabled_modules = array_values($enabledModules);
                $business->save();
            }
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => "🚀 Module {$moduleName} enabled successfully!",
        ]);
    }

    /**
     * Disable a module.
     */
    public function disable(Request $request): RedirectResponse
    {
        abort_if(! $request->user()->isAdmin(), 403);
        $moduleName = $request->input('module');

        $businessId = BusinessContextService::getCurrentBusinessId() ?: ($request->user()->business_id ?? 1);

        $business = Business::find($businessId);
        if ($business) {
            $enabledModules = array_values($business->enabled_modules ?: []);
            $enabledModules = array_values(array_diff($enabledModules, [$moduleName]));
            $business->enabled_modules = $enabledModules;
            $business->save();
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => "⏸️ Module {$moduleName} disabled successfully!",
        ]);
    }

    /**
     * Upload and extract a new module zip file.
     */
    public function upload(Request $request): RedirectResponse
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $request->validate([
            'module_zip' => 'required|file|mimes:zip|max:50000',
        ]);

        $file = $request->file('module_zip');
        $tempDir = storage_path('app/temp_extract_' . uniqid());

        // Extract ZIP
        $zip = new \ZipArchive();
        if ($zip->open($file->getRealPath()) !== true) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => '❌ Failed to open the uploaded ZIP file.',
            ]);
        }

        // Create temp dir
        if (!\Illuminate\Support\Facades\File::exists($tempDir)) {
            \Illuminate\Support\Facades\File::makeDirectory($tempDir, 0755, true);
        }

        $zip->extractTo($tempDir);
        $zip->close();

        // Search for module.json recursively in the extracted files
        $moduleJsonPath = null;
        try {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tempDir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $fileInfo) {
                if (!$fileInfo->isDir() && $fileInfo->getFilename() === 'module.json') {
                    $moduleJsonPath = $fileInfo->getPathname();
                    break;
                }
            }
        } catch (\Exception $e) {
            // iterator failed
        }

        if (!$moduleJsonPath) {
            \Illuminate\Support\Facades\File::deleteDirectory($tempDir);
            return back()->with('toast', [
                'type' => 'error',
                'message' => '❌ Invalid module structure: module.json not found in the ZIP.',
            ]);
        }

        // Read module name from module.json
        try {
            $metadata = json_decode(\Illuminate\Support\Facades\File::get($moduleJsonPath), true);
            $moduleName = $metadata['name'] ?? null;
        } catch (\Exception $e) {
            $moduleName = null;
        }

        if (!$moduleName) {
            \Illuminate\Support\Facades\File::deleteDirectory($tempDir);
            return back()->with('toast', [
                'type' => 'error',
                'message' => '❌ Invalid module.json: "name" property is missing or invalid.',
            ]);
        }

        // The folder containing module.json is the source folder
        $sourceFolder = dirname($moduleJsonPath);
        $destinationFolder = base_path('Modules/' . $moduleName);

        // Copy source folder to destination
        try {
            if (\Illuminate\Support\Facades\File::exists($destinationFolder)) {
                \Illuminate\Support\Facades\File::deleteDirectory($destinationFolder);
            }
            \Illuminate\Support\Facades\File::copyDirectory($sourceFolder, $destinationFolder);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\File::deleteDirectory($tempDir);
            return back()->with('toast', [
                'type' => 'error',
                'message' => '❌ Failed to copy module files to destination: ' . $e->getMessage(),
            ]);
        }

        // Clean up temp dir
        \Illuminate\Support\Facades\File::deleteDirectory($tempDir);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "📦 Module '{$moduleName}' uploaded and extracted successfully!",
        ]);
    }
}
