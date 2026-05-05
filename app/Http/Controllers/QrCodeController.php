<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    /**
     * Story 3.4: Generate a QR code for a link in SVG or PNG format.
     */
    public function generate(string $shortCode, string $format = 'svg'): Response
    {
        $link = Link::where('short_code', $shortCode)->firstOrFail();

        // Allow public access for QR codes
        // Only check ownership if user is logged in
        if (auth()->check() && auth()->id() !== $link->user_id) {
            abort(403);
        }

        $shortUrl = $link->short_url;

        if ($format === 'png') {
            $qr = QrCode::format('png')
                ->size(400)
                ->errorCorrection('H')
                ->generate($shortUrl);

            return response($qr, 200, [
                'Content-Type' => 'image/png',
                'Content-Disposition' => "attachment; filename=\"qr-{$link->short_code}.png\"",
            ]);
        }

        // Default: SVG
        $qr = QrCode::format('svg')
            ->size(400)
            ->errorCorrection('H')
            ->generate($shortUrl);

        return response($qr, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => "attachment; filename=\"qr-{$link->short_code}.svg\"",
        ]);
    }

    /**
     * Generate a QR code for a guest link (no auth required).
     */
    public function guestQr(string $shortCode): Response
    {
        $link = Link::where('short_code', $shortCode)
            ->whereNull('user_id')
            ->active()
            ->firstOrFail();

        $shortUrl = $link->short_url;

        $qr = QrCode::format('svg')
            ->size(300)
            ->errorCorrection('H')
            ->generate($shortUrl);

        return response($qr, 200, [
            'Content-Type' => 'image/svg+xml',
        ]);
    }
}
