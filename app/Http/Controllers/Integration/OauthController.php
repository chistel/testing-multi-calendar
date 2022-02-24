<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\ExternalAccount;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

/**
 * Class OauthController
 *
 * @package App\Http\Controllers\Integration
 */
class OauthController extends Controller
{
    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function list(Request $request)
    {
        $externalAccounts = $request->user()->externalAccounts()->paginate(10);
        return view('account.external-account.list', compact('externalAccounts'));
    }

    /**
     * Handle the OAuth connection which leads to
     * the creating of a new Google Account.
     */
    public function redirectToProvider(Request $request)
    {
        $scopes = [];
        $provider = $request->provider;
        $flowState = $request->flow_state;
        $flowService = $request->flow_service;
        $user = $request->user();
        if ($flowState === 'calendar' && $user && $flowService) {
           $scopes =  get_provider_scopes($flowService, $provider);
        }

        $socialLite = Socialite::driver($provider);
        if (Arr::has(config('services.' . $provider), 'with') && count($with = config('services.' . $provider . '.with'))) {
            $socialLite = $socialLite->with($with);
        }
        if (is_array($scopes) && count($scopes)) {
            $socialLite = $socialLite->scopes(array_values($scopes));
        }
        return $socialLite->redirect();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function callBack(Request $request)
    {
        $user = $request->user();

        $externalProvider = Socialite::driver($request->provider)->user();

        $externalProviderCheck = ExternalAccount::where([
            'provider_id' => $externalProvider->getId(),
            'provider_name' => strtolower($request->provider)
        ])->first();
        if ($externalProviderCheck && ($externalProviderCheck->user_id != $user->id)) {
            abort(404, 'This external account already belongs to a user');
        } else {
            $user->externalAccounts()->updateOrCreate(
                [
                    'provider_name' => strtolower($request->provider),
                    'provider_id' => $externalProvider->getId(),
                ],
                [
                    'scopes' => json_encode($externalProvider->approvedScopes),
                    'name' => $externalProvider->getName(),
                    'token' => $externalProvider->token,
                    'secret' => $externalProvider->tokenSecret ?? null,
                    'refresh_token' => $externalProvider->refreshToken ?? null,
                    'expires_at' => property_exists($externalProvider,
                        'expiresIn') ? now()->addSeconds($externalProvider->expiresIn) : null
                ]
            );
        }

        return redirect()->route('calender.services');
    }

    /**
     * Revoke the account's token and delete the it locally.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function disconnect(Request $request): RedirectResponse
    {
        $user = $request->user();
        $flowState = $request->flow_state;
        $flowService = $request->flow_service;
        $provider = $request->provider;

        $accountToDisconnect = $user->externalAccounts()->where([
            //'provider_id' => $request->provider_id,
            'provider_name' => $provider
        ])->first();
        if (!$accountToDisconnect) {
            abort(404);
        }

        $scopes = json_decode($accountToDisconnect->scopes, true);
        if ($flowState === 'calendar'){
            $calendarServiceScopes = get_provider_scopes($flowService, $provider);
            if(!has_scopes($scopes,$calendarServiceScopes)){
                abort(404);
            }

        }
        DB::beginTransaction();
        try {
            if ($flowState === 'calendar') {
                foreach ($calendarServiceScopes as $value) {
                    if (($key = array_search($value, $scopes, true)) !== false) {
                        unset($scopes[$key]);
                    }
                }
                $accountToDisconnect->update([
                    'scopes' => json_encode($scopes),
                ]);
            }else {
                $accountToDisconnect->delete();
            }

            DB::commit();
        } catch (\Exception $exception) {
            logger()->error('Disconnet error :  ' .  $exception);
            DB::rollBack();
        }

        return redirect()->back();
    }
}
