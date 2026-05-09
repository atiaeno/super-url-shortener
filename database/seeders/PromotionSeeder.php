<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Seeders;

use App\Models\Ad;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create storage directory if it doesn't exist
        if (!Storage::disk('public')->exists('ads')) {
            Storage::disk('public')->makeDirectory('ads');
        }

        // Sample banner promotions with real images
        $bannerPromotions = [
            [
                'name' => 'Tech Summit 2024',
                'format' => 'banner',
                'placement' => 'header',
                'content' => '<img src="https://images.unsplash.com/photo-1540575137025-b6d2ad8f2c14?w=728&h=90&fit=crop" alt="Tech Summit 2024" style="width:100%;height:auto;border-radius:4px;">',
                'target_url' => 'https://techsummit2024.com',
                'target_countries' => [],
                'countdown_seconds' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Cloud Hosting Special',
                'format' => 'banner',
                'placement' => 'sidebar',
                'content' => '<img src="https://images.unsplash.com/photo-1544197360-fc5ce2b5f5a2?w=300&h=250&fit=crop" alt="Cloud Hosting" style="width:100%;height:auto;border-radius:4px;">',
                'target_url' => 'https://cloudhosting.example',
                'target_countries' => [],
                'countdown_seconds' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Mobile App Launch',
                'format' => 'banner',
                'placement' => 'footer',
                'content' => '<img src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=728&h=90&fit=crop" alt="Mobile App" style="width:100%;height:auto;border-radius:4px;">',
                'target_url' => 'https://mobileapp.example',
                'target_countries' => [],
                'countdown_seconds' => 5,
                'is_active' => true,
            ],
        ];

        // Sample interstitial promotions
        $interstitialPromotions = [
            [
                'name' => 'Premium Subscription Offer',
                'format' => 'interstitial',
                'placement' => 'redirect',
                'content' => '
                    <div style="text-align:center;padding:20px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;border-radius:8px;">
                        <h2 style="margin:0 0 15px 0;font-size:24px;">🎉 Limited Time Offer!</h2>
                        <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=400&h=200&fit=crop" alt="Premium" style="width:100%;max-width:400px;height:auto;border-radius:8px;margin:15px 0;">
                        <p style="margin:15px 0;font-size:18px;">Get 50% off Premium Subscription</p>
                        <p style="margin:10px 0;font-size:14px;opacity:0.9;">Limited time only - Don\'t miss out!</p>
                        <div style="margin:20px 0;">
                            <a href="https://premium.example" style="background:white;color:#667eea;padding:12px 30px;text-decoration:none;border-radius:25px;font-weight:bold;display:inline-block;">Claim Your Discount</a>
                        </div>
                    </div>',
                'target_url' => 'https://premium.example',
                'target_countries' => [],
                'countdown_seconds' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Gaming Tournament',
                'format' => 'interstitial',
                'placement' => 'redirect',
                'content' => '
                    <div style="text-align:center;padding:20px;background:linear-gradient(135deg,#f093fb 0%,#f5576c 100%);color:white;border-radius:8px;">
                        <h2 style="margin:0 0 15px 0;font-size:24px;">🎮 Join the Tournament!</h2>
                        <img src="https://images.unsplash.com/photo-1542751371-adc38448a35e?w=400&h=200&fit=crop" alt="Gaming" style="width:100%;max-width:400px;height:auto;border-radius:8px;margin:15px 0;">
                        <p style="margin:15px 0;font-size:18px;">$10,000 Prize Pool</p>
                        <p style="margin:10px 0;font-size:14px;opacity:0.9;">Register now and compete!</p>
                        <div style="margin:20px 0;">
                            <a href="https://gaming.example" style="background:white;color:#f5576c;padding:12px 30px;text-decoration:none;border-radius:25px;font-weight:bold;display:inline-block;">Register Now</a>
                        </div>
                    </div>',
                'target_url' => 'https://gaming.example',
                'target_countries' => [],
                'countdown_seconds' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Travel Deal',
                'format' => 'interstitial',
                'placement' => 'redirect',
                'content' => '
                    <div style="text-align:center;padding:20px;background:linear-gradient(135deg,#fa709a 0%,#fee140 100%);color:white;border-radius:8px;">
                        <h2 style="margin:0 0 15px 0;font-size:24px;">✈️ Dream Vacation!</h2>
                        <img src="https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=400&h=200&fit=crop" alt="Travel" style="width:100%;max-width:400px;height:auto;border-radius:8px;margin:15px 0;">
                        <p style="margin:15px 0;font-size:18px;">70% Off Selected Destinations</p>
                        <p style="margin:10px 0;font-size:14px;opacity:0.9;">Book today and save big!</p>
                        <div style="margin:20px 0;">
                            <a href="https://travel.example" style="background:white;color:#fa709a;padding:12px 30px;text-decoration:none;border-radius:25px;font-weight:bold;display:inline-block;">Book Now</a>
                        </div>
                    </div>',
                'target_url' => 'https://travel.example',
                'target_countries' => [],
                'countdown_seconds' => 12,
                'is_active' => true,
            ],
        ];

        // Additional promotions for localhost testing
        $additionalPromotions = [
            [
                'name' => 'Tech Conference 2024',
                'format' => 'banner',
                'placement' => 'header',
                'content' => '<img src="https://images.unsplash.com/photo-1540575137025-b6d2ad8f2c14?w=728&h=90&fit=crop" alt="Tech Conference" style="width:100%;height:auto;border-radius:4px;">',
                'target_url' => 'https://techconf.example',
                'target_countries' => [],
                'countdown_seconds' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Summer Sale',
                'format' => 'interstitial',
                'placement' => 'redirect',
                'content' => '
                    <div style="text-align:center;padding:20px;background:linear-gradient(135deg,#4facfe 0%,#00f2fe 100%);color:white;border-radius:8px;">
                        <h2 style="margin:0 0 15px 0;font-size:24px;">🛍️ Summer Sale!</h2>
                        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=400&h=200&fit=crop" alt="Shopping" style="width:100%;max-width:400px;height:auto;border-radius:8px;margin:15px 0;">
                        <p style="margin:15px 0;font-size:18px;">Up to 60% Off Everything</p>
                        <p style="margin:10px 0;font-size:14px;opacity:0.9;">Limited time offer!</p>
                        <div style="margin:20px 0;">
                            <a href="https://summersale.example" style="background:white;color:#4facfe;padding:12px 30px;text-decoration:none;border-radius:25px;font-weight:bold;display:inline-block;">Shop Now</a>
                        </div>
                    </div>',
                'target_url' => 'https://summersale.example',
                'target_countries' => [],
                'countdown_seconds' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Cloud Services',
                'format' => 'banner',
                'placement' => 'sidebar',
                'content' => '<img src="https://images.unsplash.com/photo-1518684079-3c81559ce8fe?w=300&h=250&fit=crop" alt="Cloud Services" style="width:100%;height:auto;border-radius:4px;">',
                'target_url' => 'https://cloud.example',
                'target_countries' => [],
                'countdown_seconds' => 5,
                'is_active' => true,
            ],
        ];

        // Create all promotions
        foreach ($bannerPromotions as $promotion) {
            Ad::create($promotion);
        }

        foreach ($interstitialPromotions as $promotion) {
            Ad::create($promotion);
        }

        foreach ($additionalPromotions as $promotion) {
            Ad::create($promotion);
        }

        $this->command->info('Created ' . (count($bannerPromotions) + count($interstitialPromotions) + count($additionalPromotions)) . ' sample promotions with real images!');
    }
}
