<?php

declare(strict_types=1);

namespace App\Services\GitProviders\Drivers\GitLab;

use App\Models\VcsInstance;
use App\Services\GitProviders\Interfaces\Authenticator;
use Illuminate\Http\Client\PendingRequest;

final readonly class GitLabAuthenticator implements Authenticator
{
    public function authenticate(PendingRequest $request, VcsInstance $vcsInstance): void
    {
        $request->withHeader(
            name: 'Authorization',
            value: "Bearer $vcsInstance->token"
        );
    }
}
