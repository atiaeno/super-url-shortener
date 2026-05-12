<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Masthead from '@/Components/Masthead.vue';
import EditorialFooter from '@/Components/EditorialFooter.vue';

const page = usePage();
const seoTitle = computed(() => page.props.settings?.seo_api_docs_title || 'API Documentation');
const seoDescription = computed(() => page.props.settings?.seo_api_docs_description || '');

const activeEndpoint = ref('create');

// Get rate limits from settings
const apiRateLimit = computed(() => page.props.settings?.api_rate_limit_per_hour || 100);
const tokenRateLimit = computed(() => page.props.settings?.api_token_rate_limit_per_hour || 10);
const copyFeedback = ref({});
const expandedSections = ref({ 'links': true });

const toggleSection = (section) => {
    expandedSections.value[section] = !expandedSections.value[section];
};

const getCodeExamples = () => {
    const examples = { curl: '', javascript: '', php: '' };
    const ep = activeEndpoint.value;

    if (ep === 'create') {
        examples.curl = `curl -X POST ${baseUrl}/links \\
  -H "Content-Type: application/json" \\
  -H "Authorization: Bearer YOUR_API_KEY" \\
  -d '{"url": "https://example.com"}'`;
        examples.javascript = `fetch('${baseUrl}/links', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer YOUR_API_KEY'
  },
  body: JSON.stringify({ url: 'https://example.com' })
})
  .then(r => r.json())
  .then(data => console.log(data.data.short_url));`;
        examples.php = `<?php
$client = new GuzzleHttp\\Client();
$response = $client->post('${baseUrl}/links', [
    'headers' => [
        'Authorization' => 'Bearer YOUR_API_KEY',
        'Content-Type' => 'application/json'
    ],
    'json' => ['url' => 'https://example.com']
]);
$data = json_decode($response->getBody(), true);
echo $data['data']['short_url'];`;
    } else if (ep === 'list') {
        examples.curl = `curl -H "Authorization: Bearer YOUR_API_KEY" \\
  "${baseUrl}/links?per_page=20"`;
        examples.javascript = `fetch('${baseUrl}/links?per_page=20', {
  headers: { 'Authorization': 'Bearer YOUR_API_KEY' }
})
  .then(r => r.json())
  .then(data => console.log(data.data));`;
        examples.php = `<?php
$client = new GuzzleHttp\\Client();
$response = $client->get('${baseUrl}/links', [
    'headers' => ['Authorization' => 'Bearer YOUR_API_KEY']
]);
$data = json_decode($response->getBody(), true);
print_r($data['data']);`;
    } else if (ep === 'get') {
        examples.curl = `curl -H "Authorization: Bearer YOUR_API_KEY" \\
  ${baseUrl}/links/abc123`;
        examples.javascript = `fetch('${baseUrl}/links/abc123', {
  headers: { 'Authorization': 'Bearer YOUR_API_KEY' }
})
  .then(r => r.json())
  .then(data => console.log(data.data));`;
        examples.php = `<?php
$client = new GuzzleHttp\\Client();
$response = $client->get('${baseUrl}/links/abc123', [
    'headers' => ['Authorization' => 'Bearer YOUR_API_KEY']
]);
$data = json_decode($response->getBody(), true);
echo $data['data']['short_url'];`;
    } else if (ep === 'update') {
        examples.curl = `curl -X PUT ${baseUrl}/links/abc123 \\
  -H "Content-Type: application/json" \\
  -H "Authorization: Bearer YOUR_API_KEY" \\
  -d '{"url": "https://new-url.com"}'`;
        examples.javascript = `fetch('${baseUrl}/links/abc123', {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer YOUR_API_KEY'
  },
  body: JSON.stringify({ url: 'https://new-url.com' })
})
  .then(r => r.json())
  .then(data => console.log(data));`;
        examples.php = `<?php
$client = new GuzzleHttp\\Client();
$response = $client->put('${baseUrl}/links/abc123', [
    'headers' => [
        'Authorization' => 'Bearer YOUR_API_KEY',
        'Content-Type' => 'application/json'
    ],
    'json' => ['url' => 'https://new-url.com']
]);
$data = json_decode($response->getBody(), true);
echo $data['message'];`;
    } else if (ep === 'delete') {
        examples.curl = `curl -X DELETE ${baseUrl}/links/abc123 \\
  -H "Authorization: Bearer YOUR_API_KEY"`;
        examples.javascript = `fetch('${baseUrl}/links/abc123', {
  method: 'DELETE',
  headers: { 'Authorization': 'Bearer YOUR_API_KEY' }
})
  .then(r => r.json())
  .then(data => console.log(data));`;
        examples.php = `<?php
$client = new GuzzleHttp\\Client();
$response = $client->delete('${baseUrl}/links/abc123', [
    'headers' => ['Authorization' => 'Bearer YOUR_API_KEY']
]);
echo $response->getBody();`;
    } else if (ep === 'affiliateGet') {
        examples.curl = `curl -H "Authorization: Bearer YOUR_API_KEY" \\
  ${baseUrl}/affiliate`;
        examples.javascript = `fetch('${baseUrl}/affiliate', {
  headers: { 'Authorization': 'Bearer YOUR_API_KEY' }
})
  .then(r => r.json())
  .then(data => console.log(data));`;
        examples.php = `<?php
$client = new GuzzleHttp\\Client();
$response = $client->get('${baseUrl}/affiliate', [
    'headers' => ['Authorization' => 'Bearer YOUR_API_KEY']
]);
$data = json_decode($response->getBody(), true);
echo $data['enrolled'];`;
    } else if (ep === 'affiliateEnroll') {
        examples.curl = `curl -X POST ${baseUrl}/affiliate/enroll \\
  -H "Authorization: Bearer YOUR_API_KEY"`;
        examples.javascript = `fetch('${baseUrl}/affiliate/enroll', {
  method: 'POST',
  headers: { 'Authorization': 'Bearer YOUR_API_KEY' }
})
  .then(r => r.json())
  .then(data => console.log(data));`;
        examples.php = `<?php
$client = new GuzzleHttp\\Client();
$response = $client->post('${baseUrl}/affiliate/enroll', [
    'headers' => ['Authorization' => 'Bearer YOUR_API_KEY']
]);
echo $response->getBody();`;
    } else if (ep === 'affiliateTiers') {
        examples.curl = `curl -H "Authorization: Bearer YOUR_API_KEY" \\
  ${baseUrl}/affiliate/tiers`;
        examples.javascript = `fetch('${baseUrl}/affiliate/tiers', {
  headers: { 'Authorization': 'Bearer YOUR_API_KEY' }
})
  .then(r => r.json())
  .then(data => console.log(data.tiers));`;
        examples.php = `<?php
$client = new GuzzleHttp\\Client();
$response = $client->get('${baseUrl}/affiliate/tiers', [
    'headers' => ['Authorization' => 'Bearer YOUR_API_KEY']
]);
$data = json_decode($response->getBody(), true);
print_r($data['tiers']);`;
    } else if (ep === 'affiliatePayout') {
        examples.curl = `curl -X POST ${baseUrl}/affiliate/payout \\
  -H "Content-Type: application/json" \\
  -H "Authorization: Bearer YOUR_API_KEY" \\
  -d '{"payment_email": "you@example.com"}'`;
        examples.javascript = `fetch('${baseUrl}/affiliate/payout', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer YOUR_API_KEY'
  },
  body: JSON.stringify({ payment_email: 'you@example.com' })
})
  .then(r => r.json())
  .then(data => console.log(data));`;
        examples.php = `<?php
$client = new GuzzleHttp\\Client();
$response = $client->post('${baseUrl}/affiliate/payout', [
    'headers' => [
        'Authorization' => 'Bearer YOUR_API_KEY',
        'Content-Type' => 'application/json'
    ],
    'json' => ['payment_email' => 'you@example.com']
]);
echo $response->getBody();`;
    } else if (ep === 'tokensList') {
        examples.curl = `curl -H "Authorization: Bearer YOUR_API_KEY" \\
  ${baseUrl}/tokens`;
        examples.javascript = `fetch('${baseUrl}/tokens', {
  headers: { 'Authorization': 'Bearer YOUR_API_KEY' }
})
  .then(r => r.json())
  .then(data => console.log(data.data));`;
        examples.php = `<?php
$client = new GuzzleHttp\\Client();
$response = $client->get('${baseUrl}/tokens', [
    'headers' => ['Authorization' => 'Bearer YOUR_API_KEY']
]);
$data = json_decode($response->getBody(), true);
print_r($data['data']);`;
    } else if (ep === 'tokensCreate') {
        examples.curl = `curl -X POST ${baseUrl}/tokens \\
  -H "Content-Type: application/json" \\
  -H "Authorization: Bearer YOUR_API_KEY" \\
  -d '{"name": "My Token"}'`;
        examples.javascript = `fetch('${baseUrl}/tokens', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization' => 'Bearer YOUR_API_KEY'
  },
  body: JSON.stringify({ name: 'My Token' })
})
  .then(r => r.json())
  .then(data => console.log(data.token));`;
        examples.php = `<?php
$client = new GuzzleHttp\\Client();
$response = $client->post('${baseUrl}/tokens', [
    'headers' => [
        'Authorization' => 'Bearer YOUR_API_KEY',
        'Content-Type' => 'application/json'
    ],
    'json' => ['name' => 'My Token']
]);
$data = json_decode($response->getBody(), true);
echo $data['token'];`;
    } else if (ep === 'tokensDelete') {
        examples.curl = `curl -X DELETE ${baseUrl}/tokens/1 \\
  -H "Authorization: Bearer YOUR_API_KEY"`;
        examples.javascript = `fetch('${baseUrl}/tokens/1', {
  method: 'DELETE',
  headers: { 'Authorization': 'Bearer YOUR_API_KEY' }
})
  .then(r => r.json())
  .then(data => console.log(data));`;
        examples.php = `<?php
$client = new GuzzleHttp\\Client();
$response = $client->delete('${baseUrl}/tokens/1', [
    'headers' => ['Authorization' => 'Bearer YOUR_API_KEY']
]);
echo $response->getBody();`;
    } else {
        examples.curl = `curl -H "Authorization: Bearer YOUR_API_KEY" ${baseUrl}/${ep}`;
        examples.javascript = `fetch('${baseUrl}/${ep}', {
  headers: { 'Authorization': 'Bearer YOUR_API_KEY' }
})
  .then(r => r.json())
  .then(data => console.log(data));`;
        examples.php = `<?php
$client = new GuzzleHttp\\Client();
$response = $client->get('${baseUrl}/${ep}', [
    'headers' => ['Authorization' => 'Bearer YOUR_API_KEY']
]);
echo $response->getBody();`;
    }
    return examples;
};

const baseUrl = window.location.origin + '/api/v1';

const endpoints = [
    { id: 'create', label: 'Create Link', method: 'POST', path: '/links', num: 'I.' },
    { id: 'list', label: 'List Links', method: 'GET', path: '/links', num: 'II.' },
    { id: 'get', label: 'Get Link', method: 'GET', path: '/links/{id}', num: 'III.' },
    { id: 'update', label: 'Update Link', method: 'PATCH', path: '/links/{id}', num: 'IV.' },
    { id: 'delete', label: 'Delete Link', method: 'DELETE', path: '/links/{id}', num: 'V.' },
    { id: 'analytics', label: 'Get Analytics', method: 'GET', path: '/links/{id}/analytics', num: 'VI.' },
    { id: 'tokensList', label: 'List Tokens', method: 'GET', path: '/tokens', num: 'VII.' },
    { id: 'tokensCreate', label: 'Create Token', method: 'POST', path: '/tokens', num: 'VIII.' },
    { id: 'tokensDelete', label: 'Revoke Token', method: 'DELETE', path: '/tokens/{id}', num: 'IX.' },
    { id: 'affiliateGet', label: 'Get Affiliate', method: 'GET', path: '/affiliate', num: 'X.' },
    { id: 'affiliateEnroll', label: 'Enroll Affiliate', method: 'POST', path: '/affiliate/enroll', num: 'XI.' },
    { id: 'affiliateTiers', label: 'List Tiers', method: 'GET', path: '/affiliate/tiers', num: 'XII.' },
    { id: 'affiliatePayout', label: 'Request Payout', method: 'POST', path: '/affiliate/payout', num: 'XIII.' },
    { id: 'affiliatePayouts', label: 'Payout History', method: 'GET', path: '/affiliate/payouts', num: 'XIV.' },
    { id: 'domainsActive', label: 'Active Domains', method: 'GET', path: '/domains/active', num: 'XV.' },
];

const endpointDetails = {
    create: {
        description: 'Create a new shortened URL. Supports optional parameters: alias, domain_id (from /domains/active), campaign_tag, visibility (public/private), password (for private links), expires_at, og_title, og_description, og_image.',
        request: `POST ${baseUrl}/links
Content-Type: application/json
Authorization: Bearer YOUR_API_KEY

{
  "url": "https://example.com/very/long/path/to/resource",
  "alias": "my-link",
  "domain_id": 2,
  "visibility": "private",
  "password": "secret123"
}`,
        response: `{
  "success": true,
  "message": "Link created successfully",
  "data": {
    "short_code": "abc123",
    "short_url": "http://localhost:8000/abc123",
    "original_url": "https://example.com/very/long/path/to/resource",
    "alias": "my-link",
    "visibility": "private",
    "clicks": 0,
    "qr_code": "http://localhost:8000/qr/abc123/svg",
    "created_at": "2026-05-06T10:30:00Z"
  }
}`
    },
    list: {
        description: 'Retrieve all links created by your account with pagination support.',
        request: `GET ${baseUrl}/links?page=1&per_page=20
Authorization: Bearer YOUR_API_KEY`,
        response: `{
  "success": true,
  "data": [
    {
      "short_code": "abc123",
      "short_url": "http://localhost:8000/abc123",
      "original_url": "https://example.com",
      "alias": "my-link",
      "campaign_tag": "summer-sale",
      "is_active": true,
      "visibility": "public",
      "clicks": 42,
      "created_at": "2026-05-06T10:30:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total_pages": 5,
    "total_count": 87,
    "per_page": 20
  },
  "links": {
    "first_page": "http://127.0.0.1:8001/api/v1/links?page=1",
    "prev_page": null,
    "next_page": "http://127.0.0.1:8001/api/v1/links?page=2",
    "last_page": "http://127.0.0.1:8001/api/v1/links?page=5"
  }
}`
    },
    get: {
        description: 'Retrieve details for a specific link by its ID or short code.',
        request: `GET ${baseUrl}/links/abc123
Authorization: Bearer YOUR_API_KEY`,
        response: `{
  "success": true,
  "message": "Success",
  "data": {
    "short_code": "abc123",
    "short_url": "http://localhost:8000/abc123",
    "original_url": "https://example.com",
    "alias": "my-link",
    "is_active": true,
    "visibility": "public",
    "clicks": 42,
    "qr_code": "http://localhost:8000/qr/abc123/svg",
    "created_at": "2026-05-06T10:30:00Z",
    "updated_at": "2026-05-06T14:20:00Z"
  }
}`
    },
    update: {
        description: 'Update link properties. URL is required, other fields optional. Supports domain_id to change which domain the link uses.',
        request: `PUT ${baseUrl}/links/abc123
Content-Type: application/json
Authorization: Bearer YOUR_API_KEY

{
  "url": "https://new-destination.com/updated-path",
  "alias": "new-alias",
  "domain_id": 2
}`,
        response: `{
  "success": true,
  "message": "Link updated successfully",
  "data": {
    "id": 1,
    "short_code": "new-alias",
    "short_url": "https://go.example.com/new-alias",
    "original_url": "https://new-destination.com/updated-path",
    "alias": "new-alias",
    "domain_id": 2,
    "domain": "go.example.com"
  }
}`
    },
    delete: {
        description: 'Delete a link (soft delete).',
        request: `DELETE ${baseUrl}/links/abc123
Authorization: Bearer YOUR_API_KEY`,
        response: `{
  "success": true,
  "message": "Link deleted successfully",
  "data": {
    "deleted_at": "2026-05-06T16:50:00Z"
  }
}`
    },
    analytics: {
        description: 'Retrieve detailed analytics for a specific link. Period can be: today, week, month, or all.',
        request: `GET ${baseUrl}/links/abc123/analytics?period=month
Authorization: Bearer YOUR_API_KEY`,
        response: `{
  "link_id": "abc123",
  "period": "month",
  "summary": {
    "total_clicks": 1523,
    "unique_visitors": 1089,
    "ctr": 12.5
  },
  "geography": {
    "US": 450,
    "UK": 230,
    "DE": 180,
    "FR": 145
  },
  "referrers": {
    "direct": 600,
    "twitter.com": 400,
    "facebook.com": 300,
    "google.com": 223
  },
  "devices": {
    "mobile": 65,
    "desktop": 30,
    "tablet": 5
  },
  "browsers": {
    "Chrome": 800,
    "Safari": 400,
    "Firefox": 323
  },
  "daily_clicks": [
    { "date": "2025-01-14", "clicks": 45 },
    { "date": "2025-01-15", "clicks": 62 }
  ]
}`
    },
    tokensList: {
        description: 'List all API tokens for your account. The actual token values are not returned for security.',
        request: `GET ${baseUrl}/tokens
Authorization: Bearer YOUR_API_KEY`,
        response: `{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Production API",
      "last_used_at": "2026-05-06T14:30:00Z",
      "expires_at": "2027-05-06T10:00:00Z",
      "created_at": "2026-01-01T10:00:00Z"
    }
  ],
  "meta": { "total_count": 1 }
}`
    },
    tokensCreate: {
        description: `Create a new API token. The token is only shown once - store it securely! Rate limited to ${tokenRateLimit.value} per hour.`,
        request: `POST ${baseUrl}/tokens
Content-Type: application/json
Authorization: Bearer YOUR_API_KEY

{
  "name": "My New Token",
  "expires_days": 30
}`,
        response: `{
  "success": true,
  "message": "Token created successfully",
  "token": "abc123...xyz"
}`
    },
    tokensDelete: {
        description: 'Revoke (delete) an API token. Revoked tokens are immediately invalid.',
        request: `DELETE ${baseUrl}/tokens/2
Authorization: Bearer YOUR_API_KEY`,
        response: `{
  "success": true,
  "message": "Token revoked successfully"
}`
    },
    affiliateGet: {
        description: 'Get affiliate profile and stats. Returns enrollment status, earnings, and visits by tier. Returns 403 if affiliate program is disabled.',
        request: `GET ${baseUrl}/affiliate
Authorization: Bearer YOUR_API_KEY`,
        response: `{
  "enrolled": true,
  "affiliate": {
    "id": 1,
    "referral_code": "ABC123",
    "tier": "Tier 1",
    "total_earnings": 250.50,
    "pending_earnings": 75.25,
    "paid_earnings": 175.25,
    "total_visits": 1500,
    "stats_visits": 1500,
    "stats_earnings": 250.50,
    "is_active": true,
    "created_at": "2026-05-06T10:30:00Z"
  },
  "visits_by_tier": [
    { "tier_id": 1, "name": "Tier 1", "visits": 800, "rate": "3.0000", "multiplier": 10000, "earned": 150 }
  ],
  "min_payout": 50,
  "payout_methods": ["PayPal", "Bank Transfer", "Crypto"]
}`
    },
    affiliateEnroll: {
        description: 'Enroll in the affiliate program. Returns 403 if disabled, 409 if already enrolled.',
        request: `POST ${baseUrl}/affiliate/enroll
Authorization: Bearer YOUR_API_KEY`,
        response: `{
  "success": true,
  "enrolled": true,
  "message": "Successfully enrolled in affiliate program",
  "affiliate": {
    "id": 1,
    "referral_code": "XYZ789",
    "tier": "Tier 1",
    "total_earnings": 0,
    "pending_earnings": 0,
    "paid_earnings": 0,
    "total_visits": 0
  }
}`
    },
    affiliateTiers: {
        description: 'List all available affiliate tiers with commission rates and country multipliers.',
        request: `GET ${baseUrl}/affiliate/tiers
Authorization: Bearer YOUR_API_KEY`,
        response: `{
  "tiers": [
    {
      "id": 1,
      "name": "Bronze",
      "commission_rate": 5,
      "visit_threshold": 0,
      "view_rate": 0.02,
      "view_multiplier": 1000,
      "countries": []
    },
    {
      "id": 2,
      "name": "Gold",
      "commission_rate": 10,
      "visit_threshold": 1000,
      "view_rate": 0.05,
      "view_multiplier": 1000,
      "countries": [
        { "country_code": "US", "multiplier": 2.0 },
        { "country_code": "UK", "multiplier": 1.5 }
      ]
    }
  ]
}`
    },
    affiliatePayout: {
        description: 'Request a payout. Requires minimum balance ($50). Returns 422 if insufficient, 409 if pending payout exists.',
        request: `POST ${baseUrl}/affiliate/payout
Content-Type: application/json
Authorization: Bearer YOUR_API_KEY

{
  "payment_email": "you@example.com"
}`,
        response: `{
  "success": true,
  "message": "Payout request submitted successfully",
  "payout": {
    "id": 1,
    "amount": 75.25,
    "status": "pending",
    "payment_email": "you@example.com",
    "created_at": "2026-05-06T10:30:00Z"
  }
}`
    },
    affiliatePayouts: {
        description: 'Get payout history with pagination.',
        request: `GET ${baseUrl}/affiliate/payouts?per_page=15
Authorization: Bearer YOUR_API_KEY`,
        response: `{
  "data": [
    {
      "id": 1,
      "amount": "75.25",
      "status": "pending",
      "paypal_email": "you@example.com",
      "created_at": "2026-05-06T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total_pages": 1,
    "total_count": 1
  }
}`
    },
    domainsActive: {
        description: 'Get active domains for link creation. Public endpoint, no auth required. Use when creating links to show available domain options.',
        request: `GET ${baseUrl}/domains/active`,
        response: `{
  "success": true,
  "data": {
    "domains": [
      { "id": 1, "domain": "short.io", "is_default": true },
      { "id": 2, "domain": "go.example.com", "is_default": false }
    ],
    "default": { "id": 1, "domain": "short.io", "is_default": true }
  }
}`
    }
};

const codeExamples = {
    curl: `# Create a new short link
curl -X POST ${baseUrl}/links \\
  -H "Content-Type: application/json" \\
  -H "Authorization: Bearer YOUR_API_KEY" \\
  -d '{"url": "https://example.com"}'

# List your links
curl -H "Authorization: Bearer YOUR_API_KEY" \\
  ${baseUrl}/links

# Get affiliate profile
curl -H "Authorization: Bearer YOUR_API_KEY" \\
  ${baseUrl}/affiliate

# Enroll in affiliate program
curl -X POST ${baseUrl}/affiliate/enroll \\
  -H "Authorization: Bearer YOUR_API_KEY"

# Request payout
curl -X POST ${baseUrl}/affiliate/payout \\
  -H "Content-Type: application/json" \\
  -H "Authorization: Bearer YOUR_API_KEY" \\
  -d '{"payment_method": "PayPal", "payment_email": "you@example.com"}'`,
    javascript: `// Using fetch API
const createLink = async (url) => {
  const response = await fetch('${baseUrl}/links', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer YOUR_API_KEY'
    },
    body: JSON.stringify({ url })
  });
  return response.json();
};

// Using axios
const listLinks = async () => {
  const { data } = await axios.get('${baseUrl}/links', {
    headers: { 'Authorization': 'Bearer YOUR_API_KEY' }
  });
  return data;
};

// Affiliate: Get profile
const getAffiliate = async () => {
  const { data } = await axios.get('${baseUrl}/affiliate', {
    headers: { 'Authorization': 'Bearer YOUR_API_KEY' }
  });
  return data;
};

// Affiliate: Enroll
const enrollAffiliate = async () => {
  const { data } = await axios.post('${baseUrl}/affiliate/enroll', {}, {
    headers: { 'Authorization': 'Bearer YOUR_API_KEY' }
  });
  return data;
};

// Affiliate: Request payout
const requestPayout = async (method, email) => {
  const { data } = await axios.post('${baseUrl}/affiliate/payout', {
    payment_method: method,
    payment_email: email
  }, {
    headers: { 'Authorization': 'Bearer YOUR_API_KEY' }
  });
  return data;
};`,
    php: `<?php
// Using Guzzle
$client = new GuzzleHttp\\Client();

// Create a link
$response = $client->post('${baseUrl}/links', [
    'headers' => [
        'Authorization' => 'Bearer YOUR_API_KEY',
        'Content-Type' => 'application/json'
    ],
    'json' => ['url' => 'https://example.com']
]);

$data = json_decode($response->getBody(), true);
echo $data['short_url'];

// Affiliate: Get profile
$response = $client->get('${baseUrl}/affiliate', [
    'headers' => ['Authorization' => 'Bearer YOUR_API_KEY']
]);
$affiliate = json_decode($response->getBody(), true);

// Affiliate: Enroll
$response = $client->post('${baseUrl}/affiliate/enroll', [
    'headers' => ['Authorization' => 'Bearer YOUR_API_KEY']
]);
$result = json_decode($response->getBody(), true);

// Affiliate: Request payout
$response = $client->post('${baseUrl}/affiliate/payout', [
    'headers' => [
        'Authorization' => 'Bearer YOUR_API_KEY',
        'Content-Type' => 'application/json'
    ],
    'json' => [
        'payment_method' => 'PayPal',
        'payment_email' => 'you@example.com'
    ]
]);
$payout = json_decode($response->getBody(), true);`,
    python: `# Using requests
import requests

headers = {
    'Authorization': 'Bearer YOUR_API_KEY',
    'Content-Type': 'application/json'
}

# Create a link
response = requests.post(
    '${baseUrl}/links',
    headers=headers,
    json={'url': 'https://example.com'}
)
link = response.json()
print(link['short_url'])

# Get analytics
analytics = requests.get(
    f"${baseUrl}/links/{link['id']}/analytics",
    headers=headers
).json()

# --- Affiliate Examples ---

# Get affiliate profile
affiliate = requests.get(
    '${baseUrl}/affiliate',
    headers=headers
).json()

# Enroll in affiliate program
enroll = requests.post(
    '${baseUrl}/affiliate/enroll',
    headers=headers
).json()

# List affiliate tiers
tiers = requests.get(
    '${baseUrl}/affiliate/tiers',
    headers=headers
).json()

# Request payout
payout = requests.post(
    '${baseUrl}/affiliate/payout',
    headers=headers,
    json={'payment_method': 'PayPal', 'payment_email': 'you@example.com'}
).json()

# Get payout history
payouts = requests.get(
    '${baseUrl}/affiliate/payouts',
    headers=headers
).json()

# --- Domain Examples ---

# List active domains (public - no auth required)
domains = requests.get('${baseUrl}/domains/active').json()
print(domains['data']['domains'])
`,

};

const activeLanguage = ref('curl');

const copyCode = async (text, key) => {
    await navigator.clipboard.writeText(text);
    copyFeedback.value[key] = true;
    setTimeout(() => {
        copyFeedback.value[key] = false;
    }, 2000);
};
</script>

<template>

    <Head :title="`${seoTitle} — ${page.props.settings?.app_name || 'ShortLink'}`">
        <meta v-if="seoDescription" name="description" :content="seoDescription">
    </Head>

    <div class="api-page">
        <Masthead variant="light" :show-nav="true" />

        <main class="api-content">
            <!-- Header -->
            <header class="api-header">
                <div class="issue-label">Developer Resources</div>
                <h1>API<br><span>Documentation</span></h1>
                <p class="deck">Integrate URL shortening into your applications with our RESTful API. Simple, powerful,
                    and well-documented.</p>
                <div class="meta-badge">Version 1.0</div>
            </header>

            <!-- Quick Start -->
            <section class="quick-start">
                <div class="section-marker">
                    <span class="roman-num">I.</span>
                    <h2>Quick Start</h2>
                </div>
                <div class="quick-cards">
                    <div class="quick-card">
                        <div class="quick-num">01</div>
                        <h3>Generate API Key</h3>
                        <p>Navigate to your Profile settings or use the <code>POST /api/v1/tokens</code> endpoint to
                            create a new token.</p>
                    </div>
                    <div class="quick-card">
                        <div class="quick-num">02</div>
                        <h3>Make Requests</h3>
                        <p>Include your API key in the Authorization header: <code>Bearer YOUR_API_KEY</code></p>
                    </div>
                    <div class="quick-card">
                        <div class="quick-num">03</div>
                        <h3>Build & Deploy</h3>
                        <p>Integrate the API into your application. All endpoints return JSON with consistent structure.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Rate Limits -->
            <section class="rate-limits">
                <div class="section-marker">
                    <span class="roman-num">II.</span>
                    <h2>Rate Limits</h2>
                </div>
                <div class="limit-table">
                    <div class="limit-row limit-header">
                        <span>Endpoint</span>
                        <span>Requests / Hour</span>
                        <span>Limit Key</span>
                    </div>
                    <div class="limit-row">
                        <span class="limit-plan">All API endpoints</span>
                        <span>{{ apiRateLimit }}</span>
                        <span>Per user</span>
                    </div>
                    <div class="limit-row">
                        <span class="limit-plan">Token generation</span>
                        <span>{{ tokenRateLimit }}</span>
                        <span>Per user</span>
                    </div>
                </div>
                <p class="limit-note">Rate limit headers are included in every response: <code>X-RateLimit-Limit</code>,
                    <code>X-RateLimit-Remaining</code>, <code>X-RateLimit-Reset</code>
                </p>
            </section>

            <!-- Endpoints -->
            <section class="endpoints">
                <div class="section-marker">
                    <span class="roman-num">III.</span>
                    <h2>Endpoints</h2>
                </div>

                <div class="endpoint-layout">
                    <!-- Endpoint Navigation -->
                    <nav class="endpoint-nav">
                        <button v-for="ep in endpoints" :key="ep.id" class="endpoint-btn"
                            :class="{ 'active': activeEndpoint === ep.id }" @click="activeEndpoint = ep.id">
                            <span class="endpoint-method" :class="ep.method.toLowerCase()">{{ ep.method }}</span>
                            <span class="endpoint-label">{{ ep.label }}</span>
                            <span class="endpoint-num">{{ ep.num }}</span>
                        </button>
                    </nav>

                    <!-- Endpoint Details -->
                    <div class="endpoint-detail" v-if="endpointDetails[activeEndpoint]">
                        <div class="detail-header">
                            <span class="detail-method"
                                :class="endpoints.find(e => e.id === activeEndpoint).method.toLowerCase()">
                                {{endpoints.find(e => e.id === activeEndpoint).method}}
                            </span>
                            <code class="detail-path">{{endpoints.find(e => e.id === activeEndpoint).path}}</code>
                        </div>
                        <p class="detail-desc">{{ endpointDetails[activeEndpoint].description }}</p>

                        <div class="code-blocks">
                            <div class="code-block">
                                <div class="code-header">
                                    <span>Request</span>
                                    <button class="copy-btn"
                                        @click="copyCode(endpointDetails[activeEndpoint].request, 'request')">
                                        {{ copyFeedback['request'] ? 'Copied!' : 'Copy' }}
                                    </button>
                                </div>
                                <pre><code>{{ endpointDetails[activeEndpoint].request }}</code></pre>
                            </div>

                            <div class="code-block">
                                <div class="code-header">
                                    <span>Response</span>
                                    <button class="copy-btn"
                                        @click="copyCode(endpointDetails[activeEndpoint].response, 'response')">
                                        {{ copyFeedback['response'] ? 'Copied!' : 'Copy' }}
                                    </button>
                                </div>
                                <pre><code>{{ endpointDetails[activeEndpoint].response }}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Code Examples -->
            <section class="code-examples" :class="{ 'collapsed': !expandedSections['code'] }">
                <div class="section-marker" @click="toggleSection('code')">
                    <span class="roman-num">IV.</span>
                    <h2>Code Examples</h2>
                    <span class="expand-icon">{{ expandedSections['code'] ? '−' : '+' }}</span>
                </div>
                <div class="section-content" v-show="expandedSections['code']">
                    <div class="lang-tabs">
                        <button v-for="(code, lang) in getCodeExamples()" :key="lang" class="lang-tab"
                            :class="{ 'active': activeLanguage === lang }" @click="activeLanguage = lang">
                            {{ lang }}
                        </button>
                    </div>

                    <div class="example-code">
                        <div class="code-header">
                            <span>{{ activeLanguage }}.example</span>
                            <button class="copy-btn" @click="copyCode(getCodeExamples()[activeLanguage], 'example')">
                                {{ copyFeedback['example'] ? 'Copied!' : 'Copy' }}
                            </button>
                        </div>
                        <pre><code>{{ getCodeExamples()[activeLanguage] }}</code></pre>
                    </div>
                </div>
            </section>

            <!-- Error Reference -->
            <section class="error-ref" :class="{ 'collapsed': !expandedSections['error'] }">
                <div class="section-marker" @click="toggleSection('error')">
                    <span class="roman-num">V.</span>
                    <h2>Error Reference</h2>
                    <span class="expand-icon">{{ expandedSections['error'] ? '−' : '+' }}</span>
                </div>
                <div class="section-content" v-show="expandedSections['error']">
                    <table class="error-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Error</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code class="error-code">400</code></td>
                                <td>Bad Request</td>
                                <td>Invalid request parameters</td>
                            </tr>
                            <tr>
                                <td><code class="error-code">401</code></td>
                                <td>Unauthorized</td>
                                <td>Invalid or missing API key</td>
                            </tr>
                            <tr>
                                <td><code class="error-code">403</code></td>
                                <td>Forbidden</td>
                                <td>Access denied or affiliate program disabled</td>
                            </tr>
                            <tr>
                                <td><code class="error-code">404</code></td>
                                <td>Not Found</td>
                                <td>Link or resource not found</td>
                            </tr>
                            <tr>
                                <td><code class="error-code">409</code></td>
                                <td>Conflict</td>
                                <td>Alias already exists or already enrolled</td>
                            </tr>
                            <tr>
                                <td><code class="error-code">422</code></td>
                                <td>Unprocessable</td>
                                <td>Validation error (invalid parameters)</td>
                            </tr>
                            <tr>
                                <td><code class="error-code">429</code></td>
                                <td>Too Many Requests</td>
                                <td>Rate limit exceeded</td>
                            </tr>
                            <tr>
                                <td><code class="error-code">500</code></td>
                                <td>Server Error</td>
                                <td>Internal server error</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>

        <EditorialFooter />
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Oswald:wght@500;700&family=JetBrains+Mono:wght@400;500&display=swap');

.api-page {
    font-family: 'Crimson Pro', serif;
    background: #fafafa;
    color: #1a1a1a;
    min-height: 100vh;
}

.api-content {
    padding: 140px 60px 80px;
    max-width: 1000px;
    margin: 0 auto;
}

/* ── Header ──────────────────────────────────────────────────── */
.api-header {
    margin-bottom: 60px;
    padding-bottom: 40px;
    border-bottom: 1px solid #ddd;
}

.issue-label {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #999;
    margin-bottom: 20px;
}

h1 {
    font-family: 'Oswald', sans-serif;
    font-size: 72px;
    font-weight: 700;
    line-height: 0.95;
    letter-spacing: -2px;
    margin-bottom: 24px;
}

h1 span {
    font-family: 'Crimson Pro', serif;
    font-size: 48px;
    font-weight: 400;
    font-style: italic;
    color: #e74c3c;
    letter-spacing: 0;
}

.deck {
    font-size: 20px;
    line-height: 1.6;
    color: #666;
    margin-bottom: 24px;
    max-width: 600px;
}

.meta-badge {
    display: inline-block;
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #d4af37;
    border: 1px solid #d4af37;
    padding: 8px 16px;
}

/* ── Section Marker ─────────────────────────────────────────── */
.section-marker {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 32px;
    cursor: pointer;
}

.section-marker .roman-num {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: #e74c3c;
}

.section-marker h2 {
    flex: 1;
    font-family: 'Oswald', sans-serif;
    font-size: 18px;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin: 0;
}

.expand-icon {
    font-size: 20px;
    font-weight: bold;
    color: #666;
    margin-left: auto;
}

/* ── Quick Start ─────────────────────────────────────────────── */
.quick-start {
    margin-bottom: 60px;
}

.quick-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

.quick-card {
    background: #fff;
    border: 1px solid #e5e5e5;
    padding: 32px;
    position: relative;
}

.quick-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #e74c3c 0%, #d4af37 100%);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.quick-card:hover::before {
    transform: scaleX(1);
}

.quick-num {
    font-family: 'Oswald', sans-serif;
    font-size: 28px;
    font-weight: 700;
    color: #e74c3c;
    margin-bottom: 16px;
}

.quick-card h3 {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 12px;
}

.quick-card p {
    font-size: 15px;
    line-height: 1.7;
    color: #666;
    margin: 0;
}

.quick-card code {
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px;
    background: #f5f5f5;
    padding: 2px 6px;
    color: #e74c3c;
}

/* ── Rate Limits ────────────────────────────────────────────── */
.rate-limits {
    margin-bottom: 60px;
}

.limit-table {
    border: 1px solid #e5e5e5;
    background: #fff;
}

.limit-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    padding: 16px 24px;
    border-bottom: 1px solid #f0f0f0;
}

.limit-row:last-child {
    border-bottom: none;
}

.limit-header {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #666;
    background: #f5f5f5;
}

.limit-row:not(.limit-header) {
    font-size: 15px;
    color: #555;
}

.limit-plan {
    font-weight: 600;
    color: #1a1a1a;
}

.limit-note {
    font-size: 14px;
    color: #888;
    margin-top: 16px;
}

.limit-note code {
    font-family: 'JetBrains Mono', monospace;
    background: #f5f5f5;
    padding: 2px 6px;
    font-size: 13px;
}

/* ── Endpoints ──────────────────────────────────────────────── */
.endpoints {
    margin-bottom: 60px;
}

.endpoint-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 32px;
}

.endpoint-nav {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.endpoint-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    background: #fff;
    border: 1px solid #e5e5e5;
    text-align: left;
    cursor: pointer;
    transition: all 0.3s ease;
}

.endpoint-btn:hover {
    border-color: #ccc;
}

.endpoint-btn.active {
    background: #1a1a1a;
    border-color: #1a1a1a;
}

.endpoint-method {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    font-weight: 500;
    padding: 4px 8px;
    border-radius: 2px;
}

.endpoint-method.post {
    background: #27ae60;
    color: #fff;
}

.endpoint-method.get {
    background: #3498db;
    color: #fff;
}

.endpoint-method.patch {
    background: #f39c12;
    color: #fff;
}

.endpoint-method.delete {
    background: #e74c3c;
    color: #fff;
}

.endpoint-btn.active .endpoint-method {
    filter: brightness(1.2);
}

.endpoint-label {
    flex: 1;
    font-family: 'Crimson Pro', serif;
    font-size: 15px;
    font-weight: 600;
    color: #1a1a1a;
}

.endpoint-btn.active .endpoint-label {
    color: #fafafa;
}

.endpoint-num {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    color: #999;
}

.endpoint-btn.active .endpoint-num {
    color: #d4af37;
}

/* Endpoint Detail */
.endpoint-detail {
    background: #fff;
    border: 1px solid #e5e5e5;
}

.detail-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px 24px;
    border-bottom: 1px solid #f0f0f0;
    background: #fafafa;
}

.detail-method {
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 2px;
}

.detail-method.post {
    background: #27ae60;
    color: #fff;
}

.detail-method.get {
    background: #3498db;
    color: #fff;
}

.detail-method.patch {
    background: #f39c12;
    color: #fff;
}

.detail-method.delete {
    background: #e74c3c;
    color: #fff;
}

.detail-path {
    font-family: 'JetBrains Mono', monospace;
    font-size: 14px;
    color: #666;
    background: transparent;
}

.detail-desc {
    font-size: 16px;
    line-height: 1.6;
    color: #555;
    padding: 20px 24px;
    margin: 0;
    border-bottom: 1px solid #f0f0f0;
}

.code-blocks {
    display: flex;
    flex-direction: column;
}

.code-block {
    border-bottom: 1px solid #f0f0f0;
}

.code-block:last-child {
    border-bottom: none;
}

.code-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 24px;
    background: #f8f8f8;
    border-bottom: 1px solid #f0f0f0;
}

.code-header span {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #888;
}

.copy-btn {
    font-family: 'Oswald', sans-serif;
    font-size: 10px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #888;
    background: transparent;
    border: 1px solid #ddd;
    padding: 6px 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.copy-btn:hover {
    border-color: #e74c3c;
    color: #e74c3c;
}

pre {
    margin: 0;
    padding: 20px 24px;
    overflow-x: auto;
}

code {
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px;
    line-height: 1.6;
    color: #444;
}

/* ── Code Examples ─────────────────────────────────────────── */
.code-examples {
    margin-bottom: 60px;
}

.lang-tabs {
    display: flex;
    gap: 4px;
    margin-bottom: 0;
    border-bottom: 1px solid #e5e5e5;
}

.lang-tab {
    padding: 12px 24px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px;
    color: #666;
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    transition: all 0.3s;
}

.lang-tab:hover {
    color: #1a1a1a;
}

.lang-tab.active {
    color: #e74c3c;
    border-bottom-color: #e74c3c;
}

.example-code {
    background: #1a1a1a;
    border: 1px solid #333;
}

.example-code .code-header {
    background: #111;
    border-bottom-color: #333;
}

.example-code .code-header span {
    color: #666;
}

.example-code .copy-btn {
    border-color: #444;
    color: #888;
}

.example-code .copy-btn:hover {
    border-color: #d4af37;
    color: #d4af37;
}

.example-code pre {
    background: #1a1a1a;
}

.example-code code {
    color: #c0c0c0;
}

/* ── Error Reference ───────────────────────────────────────── */
.error-ref {
    margin-bottom: 60px;
}

.error-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border: 1px solid #e5e5e5;
}

.error-table th,
.error-table td {
    padding: 16px 20px;
    text-align: left;
    border-bottom: 1px solid #e5e5e5;
}

.error-table th {
    background: #f5f5f5;
    font-weight: 600;
    font-size: 14px;
    color: #333;
}

.error-table tr:last-child td {
    border-bottom: none;
}

.error-table tr:hover {
    background: #fafafa;
}

.error-code {
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px;
    font-weight: 500;
    color: #e74c3c;
    background: #fef2f2;
    padding: 4px 8px;
    border-radius: 4px;
}

.error-name {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #1a1a1a;
}

.error-desc {
    font-size: 14px;
    color: #888;
}

/* ── Responsive ────────────────────────────────────────────── */
@media (max-width: 968px) {
    .api-content {
        padding: 120px 30px 60px;
    }

    h1 {
        font-size: 48px;
    }

    h1 span {
        font-size: 36px;
    }

    .quick-cards {
        grid-template-columns: 1fr;
    }

    .endpoint-layout {
        grid-template-columns: 1fr;
    }

    .endpoint-nav {
        flex-direction: row;
        flex-wrap: wrap;
    }

    .endpoint-btn {
        flex: 1;
        min-width: 200px;
    }

    .error-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    h1 {
        font-size: 36px;
    }

    h1 span {
        font-size: 28px;
    }

    .error-grid {
        grid-template-columns: 1fr;
    }

    .limit-row {
        grid-template-columns: 1fr;
        gap: 8px;
    }

    .limit-row span:not(.limit-plan) {
        color: #888;
    }
}
</style>
