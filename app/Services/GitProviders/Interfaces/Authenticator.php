<?php

declare(strict_types=1);

namespace App\Services\GitProviders\Interfaces;

use App\Models\VcsInstance;
use Illuminate\Http\Client\PendingRequest;

interface Authenticator
{
    public function authenticate(PendingRequest $request, VcsInstance $vcsInstance): void;
}
