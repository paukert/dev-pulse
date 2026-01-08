<?php

declare(strict_types=1);

namespace App\Services\GitProviders\Drivers\GitHub;

use App\Models\VcsInstance;
use App\Services\GitProviders\Interfaces\Authenticator;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final readonly class GitHubAuthenticator implements Authenticator
{
    public function authenticate(PendingRequest $request, VcsInstance $vcsInstance): void
    {
        $installationToken = Cache::remember(
            key: "github-$vcsInstance->id-installation-token",
            ttl: 59 * 60, // the installation token is valid for 1 hour
            callback: fn (): string => $this->getInstallationToken(vcsInstance: $vcsInstance)
        );

        $request->withHeader(
            name: 'Authorization',
            value: "Bearer $installationToken"
        );

        $request->withHeader(
            name: 'X-Github-Next-Global-ID',
            value: 1
        );
    }

    private function getInstallationToken(VcsInstance $vcsInstance): string
    {
        $payload = [
            'iat' => time() - 60,
            'exp' => time() + (10 * 60),
            'iss' => config('services.github.app_client_id'),
            'alg' => 'RS256',
        ];

        $jwt = JWT::encode($payload, config('services.github.app_private_key'), 'RS256');

        $response = Http::withHeaders([
            'Authorization' => "Bearer $jwt",
            'Accept' => 'application/vnd.github+json',
        ])->post("$vcsInstance->api_url/app/installations/$vcsInstance->installation_id/access_tokens");

        if ($response->failed()) {
            throw new Exception('Cannot get installation token: ' . $response->body());
        }

        return $response->json('token');
    }
}
