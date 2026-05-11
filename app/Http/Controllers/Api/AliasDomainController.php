<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Api;

use App\Models\AliasDomain;

class AliasDomainController extends ApiController
{
    /**
     * Get active domains for link creation.
     * GET /api/v1/domains/active
     */
    public function active(): \Illuminate\Http\JsonResponse
    {
        $domains = AliasDomain::getActiveDomains();

        return $this->success([
            'domains' => $domains,
            'default' => collect($domains)->firstWhere('is_default', true),
        ]);
    }
}
