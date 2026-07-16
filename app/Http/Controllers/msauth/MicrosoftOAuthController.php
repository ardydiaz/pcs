<?php

namespace App\Http\Controllers\msauth;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\User;
use App\Support\AuditLogger;
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
                AuditLogger::log('login_denied', [
                    'module' => 'Security',
                    'description' => 'Microsoft login denied because the account did not provide an email address.',
                    'severity' => 'warning',
                    'after_values' => [
                        'reason' => 'missing_email',
                        'provider' => 'microsoft',
                    ],
                ]);

                return redirect()->route('login')
                    ->withErrors(['msg' => 'Microsoft account is missing an email address. Please contact administrator.']);
            }

            $avatar = $this->fetchMicrosoftAvatar($microsoftUser->token ?? null)
                ?? ($microsoftUser->attributes['avatar'] ?? null);

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
            } else {
                AuditLogger::log('login_denied', [
                    'module' => 'Security',
                    'name' => $microsoftUser->getName() ?: 'Unknown Microsoft user',
                    'email' => $normalizedEmail,
                    'description' => 'Microsoft login denied for a non-MCU email domain.',
                    'severity' => 'danger',
                    'after_values' => [
                        'reason' => 'outside_domain',
                        'provider' => 'microsoft',
                        'allowed_domains' => ['mcu.edu.ph', 'faculty.mcu.edu.ph', 'student.mcu.edu.ph'],
                    ],
                ]);

                return redirect()->route('login')
                    ->withErrors(['msg' => 'Only MCU Microsoft accounts are allowed to access this system.']);
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

            $existingUser = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();
            if ($existingUser) {
                if ($role === 'Faculty' && !$existingUser->faculty) {
                    $facultyUser = $this->findFacultyUserByMicrosoftName($microsoftUser->getName());
                    if ($facultyUser && $facultyUser->id !== $existingUser->id) {
                        $this->moveMicrosoftIdentityToFacultyUser($existingUser, $facultyUser, $normalizedEmail, $microsoftUser->getId(), $avatar);

                        $this->logSuccessfulLogin($facultyUser->fresh(), 'Merged Microsoft login duplicate into imported faculty profile.');
                        Auth::login($facultyUser->fresh());
                        return redirect()->intended('/dashboard');
                    }
                }

                if ($avatar && $existingUser->avatar !== $avatar) {
                    $existingUser->forceFill(['avatar' => $avatar])->save();
                }

                $this->logSuccessfulLogin($existingUser, 'Microsoft login successful.');
                Auth::login($existingUser);
                return redirect()->intended('/dashboard');
            }

            if ($role === 'Faculty') {
                $facultyUser = $this->findFacultyUserByMicrosoftName($microsoftUser->getName());
                if ($facultyUser) {
                    $facultyUser->forceFill([
                        'email' => $normalizedEmail,
                        'provider_id' => $microsoftUser->getId(),
                        'provider' => 'microsoft',
                        'avatar' => $avatar,
                        'status' => 'Active',
                    ])->save();

                    $this->logSuccessfulLogin($facultyUser, 'Microsoft login linked to existing imported faculty profile.');
                    Auth::login($facultyUser);
                    return redirect()->intended('/dashboard');
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

            $this->logSuccessfulLogin($user, 'Microsoft login created a new user account.');
            Auth::login($user);

            return redirect()->intended('/dashboard');

        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::error('Microsoft OAuth Invalid State Exception', ['error' => $e->getMessage()]);
            $this->logFailedLogin('invalid_state', 'Microsoft login session expired or invalid.');
            session()->flush();
            return redirect()->route('login')
                ->withErrors(['msg' => 'Session expired. Please try logging in again.']);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error('Microsoft OAuth Client Exception', ['error' => $e->getMessage()]);
            $this->logFailedLogin('client_exception', 'Microsoft login client error.');
            session()->flush();
            return redirect()->route('login')
                ->withErrors(['msg' => 'Authentication expired. Please try logging in again.']);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Microsoft OAuth Database Exception', ['error' => $e->getMessage()]);
            $this->logFailedLogin('database_exception', 'Microsoft login database error.');
            session()->flush();
            return redirect()->route('login')
                ->withErrors(['msg' => 'Database error. Please contact administrator.']);

        } catch (\Throwable $e) {
            Log::error('Microsoft OAuth General Exception', ['error' => $e->getMessage()]);
            $this->logFailedLogin('general_exception', 'Microsoft login failed unexpectedly.');
            session()->flush();
            return redirect()->route('login')
                ->withErrors(['msg' => 'Microsoft login failed. Please try again.']);
        }
    }

    private function logSuccessfulLogin(User $user, string $description): void
    {
        AuditLogger::log('login_success', [
            'module' => 'Security',
            'description' => $description,
            'severity' => 'info',
            'after_values' => [
                'email' => $user->email,
                'role' => $user->role,
                'provider' => 'microsoft',
                'device' => $this->summarizeUserAgent(request()?->userAgent()),
            ],
        ], $user);
    }

    private function logFailedLogin(string $reason, string $description): void
    {
        AuditLogger::log('login_failed', [
            'module' => 'Security',
            'description' => $description,
            'severity' => 'warning',
            'after_values' => [
                'reason' => $reason,
                'provider' => 'microsoft',
                'device' => $this->summarizeUserAgent(request()?->userAgent()),
            ],
        ]);
    }

    private function summarizeUserAgent(?string $userAgent): string
    {
        $userAgent = (string) $userAgent;
        if ($userAgent === '') {
            return 'Unknown device';
        }

        $browser = str_contains($userAgent, 'Edg/') ? 'Edge'
            : (str_contains($userAgent, 'Chrome/') ? 'Chrome'
                : (str_contains($userAgent, 'Firefox/') ? 'Firefox'
                    : (str_contains($userAgent, 'Safari/') ? 'Safari' : 'Browser')));
        $platform = str_contains($userAgent, 'Windows') ? 'Windows'
            : (str_contains($userAgent, 'Mac OS') ? 'macOS'
                : (str_contains($userAgent, 'Android') ? 'Android'
                    : (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') ? 'iOS' : 'Unknown OS')));

        return $browser . ' on ' . $platform;
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

    private function moveMicrosoftIdentityToFacultyUser(
        User $orphanUser,
        User $facultyUser,
        string $email,
        ?string $providerId,
        ?string $avatar
    ): void {
        $orphanUser->forceFill([
            'email' => null,
            'provider_id' => null,
            'provider' => null,
            'status' => 'Inactive',
        ])->save();

        $facultyUser->forceFill([
            'email' => $email,
            'provider_id' => $providerId,
            'provider' => 'microsoft',
            'avatar' => $avatar,
            'status' => 'Active',
        ])->save();

        $orphanUser->delete();

        Log::info('Merged Microsoft faculty login into imported faculty user', [
            'orphan_user_id' => $orphanUser->id,
            'faculty_user_id' => $facultyUser->id,
            'email' => $email,
        ]);
    }

    private function findFacultyUserByMicrosoftName(?string $name): ?User
    {
        $tokens = $this->nameTokens((string) $name);
        if (count($tokens) < 2) {
            return null;
        }

        $matches = Faculty::with('user')
            ->whereHas('user', function ($query) {
                $query->whereRaw('LOWER(COALESCE(role, "")) = ?', ['faculty']);
            })
            ->get()
            ->map(function (Faculty $faculty) use ($tokens) {
                $facultyTokens = $this->nameTokens($faculty->user?->name ?? '');
                $score = count(array_intersect($tokens, $facultyTokens));

                return [
                    'user' => $faculty->user,
                    'score' => $score,
                ];
            })
            ->filter(function (array $match) use ($tokens) {
                $requiredScore = count($tokens) >= 3 ? 3 : count($tokens);

                return $match['user'] && $match['score'] >= $requiredScore;
            })
            ->sortByDesc('score')
            ->values();

        if ($matches->isEmpty()) {
            return null;
        }

        $topScore = $matches->first()['score'];
        $topMatches = $matches->filter(fn (array $match) => $match['score'] === $topScore);

        return $topMatches->count() === 1 ? $topMatches->first()['user'] : null;
    }

    private function nameTokens(string $name): array
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/[^a-z0-9 ]+/', ' ', $name);

        return collect(preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY))
            ->reject(fn ($token) => strlen($token) <= 1 || in_array($token, ['dr', 'dra', 'jr', 'sr', 'ii', 'iii', 'iv'], true))
            ->unique()
            ->values()
            ->all();
    }

}
