<?php

namespace App\Http\Middleware;

use App\Actions\Lists\ClaimAnonymousList as ClaimAnonymousListAction;
use App\Models\DecisionList;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Claims a pending anonymous list for the user once they authenticate.
 *
 * The list id is stashed in the session while the user is anonymous (during
 * voting or the registration prompt); on the first authenticated request it
 * is transferred to their account.
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
        if (! $request->user()) {
            return $next($request);
        }

        $listId = session('anonymous_list_id');

        if (! $listId) {
            // Fall back to the intended URL captured before registration.
            $intendedUrl = session('intended_url');
            if ($intendedUrl && preg_match('/\/lists\/(\d+)\/results/', $intendedUrl, $matches)) {
                $listId = $matches[1];
            }
        }

        if ($listId) {
            try {
                $list = DecisionList::find($listId);

                if ($list && $list->is_anonymous && ! $list->claimed_at) {
                    // The session id is regenerated at login, so the votes are
                    // keyed by the pre-login token stashed alongside the list id.
                    app(ClaimAnonymousListAction::class)->handle(
                        $list,
                        $request->user(),
                        session('anonymous_session_token', session()->getId()),
                    );
                }

                session()->forget(['anonymous_list_id', 'anonymous_session_token', 'intended_url']);
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
