<?php

if (! function_exists('is_super_team')) {
    /**
     * True kalau user yang login ada di tim dengan kode 'IE' (akses semua menu).
     */
    function is_super_team(): bool
    {
        if (! auth()->loggedIn()) {
            return false;
        }

        static $cached = null;

        if ($cached !== null) {
            return $cached;
        }

        $profile = (new \App\Models\UserProfileModel())
            ->select('user_profiles.*, teams.code as team_code')
            ->join('teams', 'teams.id = user_profiles.team_id', 'left')
            ->where('user_profiles.user_id', auth()->id())
            ->first();

        return $cached = (($profile['team_code'] ?? null) === 'IE');
    }
}
