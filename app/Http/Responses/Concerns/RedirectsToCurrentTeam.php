<?php

namespace App\Http\Responses\Concerns;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

trait RedirectsToCurrentTeam
{
    protected function redirectPathForCurrentTeam(Request $request, string $redirect): string
    {
        $user = $request->user();
        $team = $this->currentTeam($request);

        if ($user && $user->isAdmin()) {
            if (! $team) {
                return '/dashboard';
            }

            URL::defaults(['current_team' => $team->slug]);

            return "/{$team->slug}{$redirect}";
        }

        return '/shop';
    }

    protected function currentTeam(Request $request): ?Team
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        return $user->currentTeam ?? $user->personalTeam();
    }
}
