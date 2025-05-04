<?php

namespace App\Http\Middleware;

use App\Models\DecisionList;
use App\Services\ListClaimService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to handle claiming anonymous lists after user authentication.
 *
 * This middleware:
 * 1. Checks if there's an anonymous list in the session
 * 2. Claims it for the newly authenticated user
 * 3. Redirects to the intended URL
 */
class ClaimAnonymousList
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('ClaimAnonymousList middleware running', [
            'user' => $request->user()?->id,
            'session_data' => [
                'anonymous_list_id' => session('anonymous_list_id'),
                'intended_url' => session('intended_url'),
            ],
        ]);

        // Only proceed if user is authenticated
        if (! $request->user()) {
            Log::info('No authenticated user, skipping list claim');
            return $next($request);
        }

        // Check if we have a list ID in the session
        $listId = session('anonymous_list_id');
        if (! $listId) {
            // Try to extract list ID from intended URL if it exists
            $intendedUrl = session('intended_url');
            if ($intendedUrl && preg_match('/\/lists\/(\d+)\/results/', $intendedUrl, $matches)) {
                $listId = $matches[1];
                session(['anonymous_list_id' => $listId]);
                Log::info('Extracted list ID from intended URL', ['list_id' => $listId]);
            } else {
                Log::info('No list ID found in session or URL');
            }
        }

        if ($listId) {
            try {
                $list = DecisionList::find($listId);
                Log::info('Found list', [
                    'list_id' => $listId,
                    'is_anonymous' => $list?->is_anonymous,
                    'claimed_at' => $list?->claimed_at,
                ]);
                
                if ($list && $list->is_anonymous && ! $list->claimed_at) {
                    app(ListClaimService::class)->claimList($list, $request->user());
                    
                    Log::info('Anonymous list claimed after authentication', [
                        'list_id' => $list->id,
                        'user_id' => $request->user()->id,
                    ]);

                    // Clear the session data after successful claim
                    session()->forget(['anonymous_list_id', 'intended_url']);
                }
            } catch (\Exception $e) {
                Log::error('Failed to claim anonymous list', [
                    'list_id' => $listId,
                    'user_id' => $request->user()->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $next($request);
    }
} 