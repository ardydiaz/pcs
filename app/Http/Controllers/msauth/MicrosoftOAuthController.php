<?php

namespace App\Http\Controllers\msauth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MicrosoftOAuthController extends Controller
{
    public function redirect()
    {
        // Clear any existing session data to prevent conflicts
        session()->forget(['state', '_token']);

        return Socialite::driver('microsoft')
            ->scopes(['openid', 'profile', 'email', 'User.Read'])
            ->redirect();
    }

    public function callback()
    {
        try {
            $microsoftUser = Socialite::driver('microsoft')->user();

            $email = $microsoftUser->getEmail();
            $normalizedEmail = strtolower($email ?? '');
            if ($normalizedEmail === '') {
                return redirect()->route('login')
                    ->withErrors(['msg' => 'Microsoft account is missing an email address. Please contact administrator.']);
            }

            $avatar = $this->fetchMicrosoftAvatar($microsoftUser->token ?? null)
                ?? ($microsoftUser->attributes['avatar'] ?? null);

            $existingUser = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();
            if ($existingUser) {
                if ($avatar && $existingUser->avatar !== $avatar) {
                    $existingUser->forceFill(['avatar' => $avatar])->save();
                }

                Auth::login($existingUser);
                return redirect()->intended('/dashboard');
            }

            $role = 'Faculty'; // default role

            // Role conditions
            $adminEmails = [
                'kcavila@mcu.edu.ph',
                'cbmonterey@mcu.edu.ph',
                'oppioquinto@mcu.edu.ph',
                'rrpellazo@mcu.edu.ph',
            ];

            if (in_array($normalizedEmail, $adminEmails, true)) {
                $role = 'Admin';
            } elseif (str_ends_with($normalizedEmail, '@mcu.edu.ph')) {
                $role = 'NTP';
            } elseif (str_ends_with($normalizedEmail, '@faculty.mcu.edu.ph')) {
                $role = 'Faculty';
            } elseif (str_ends_with($normalizedEmail, '@student.mcu.edu.ph')) {
                $role = 'Student';
            }

            $allAccessLevels = [
                'View All Reports',
                'View Department Reports',
                'Manage Faculties',
                'Manage Courses',
                'Manage Schedules',
                'Manage Evaluations',
                'Manage Evaluation QR/Link',
                'View/Answer Forms',
            ];
            $accessLevel = $role === 'Admin'
                ? $allAccessLevels
                : ($role === 'Student' ? ['View/Answer Forms'] : []);
            if (is_array($accessLevel)) {
                if (in_array('View All Reports', $accessLevel, true)) {
                    $accessLevel = array_values(array_filter($accessLevel, function ($level) {
                        return $level !== 'View Department Reports';
                    }));
                }
                if (in_array('Manage Evaluations', $accessLevel, true)) {
                    $accessLevel = array_values(array_filter($accessLevel, function ($level) {
                        return $level !== 'Manage Evaluation QR/Link';
                    }));
                }
            }
            $user = User::create([
                'name' => $microsoftUser->getName(),
                'email' => $normalizedEmail,
                'password' => null,
                'department' => $microsoftUser->attributes['department'] ?? null,
                'job_title' => $microsoftUser->attributes['jobTitle'] ?? null,
                'role' => $role,
                'access_level' => $accessLevel,
                'status' => 'Active',
                'provider_id' => $microsoftUser->getId(),
                'provider' => 'microsoft',
                'avatar' => $avatar,
            ]);

            Auth::login($user);

            return redirect()->intended('/dashboard');

        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::error('Microsoft OAuth Invalid State Exception', ['error' => $e->getMessage()]);
            session()->flush();
            return redirect()->route('login')
                ->withErrors(['msg' => 'Session expired. Please try logging in again.']);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error('Microsoft OAuth Client Exception', ['error' => $e->getMessage()]);
            session()->flush();
            return redirect()->route('login')
                ->withErrors(['msg' => 'Authentication expired. Please try logging in again.']);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Microsoft OAuth Database Exception', ['error' => $e->getMessage()]);
            session()->flush();
            return redirect()->route('login')
                ->withErrors(['msg' => 'Database error. Please contact administrator.']);

        } catch (\Throwable $e) {
            Log::error('Microsoft OAuth General Exception', ['error' => $e->getMessage()]);
            session()->flush();
            return redirect()->route('login')
                ->withErrors(['msg' => 'Microsoft login failed. Please try again.']);
        }
    }

    private function fetchMicrosoftAvatar(?string $accessToken): ?string
    {
        if (!$accessToken) {
            return null;
        }

        try {
            $response = Http::withToken($accessToken)
                ->accept('image/*')
                ->timeout(10)
                ->get('https://graph.microsoft.com/v1.0/me/photo/$value');

            if (!$response->successful() || $response->body() === '') {
                return null;
            }

            $contentType = $response->header('Content-Type', 'image/jpeg');

            return 'data:' . $contentType . ';base64,' . base64_encode($response->body());
        } catch (\Throwable $e) {
            Log::warning('Microsoft profile photo unavailable', ['error' => $e->getMessage()]);

            return null;
        }
    }

}
