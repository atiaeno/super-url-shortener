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

        // Additional promotions for various placements
        $additionalPromotions = [
            [
                'name' => 'Tech Summit Banner',
                'content' => '<img src="https://picsum.photos/seed/techsummit/728/90.jpg" alt="Tech Summit 2024">',
                'target_url' => 'https://techsummit.example.com',
                'placement' => 'header',
                'format' => 'banner',
                'is_active' => true,
            ],
            [
                'name' => 'Cloud Hosting Footer',
                'content' => '<img src="https://picsum.photos/seed/cloudhost/728/90.jpg" alt="Cloud Hosting Services">',
                'target_url' => 'https://cloudhost.example.com',
                'placement' => 'footer',
                'format' => 'banner',
                'is_active' => true,
            ],
            [
                'name' => 'Left Side Promotion',
                'content' => '<img src="https://picsum.photos/seed/leftside/300/250.jpg" alt="Left Side Offer">',
                'target_url' => 'https://leftside.example.com',
                'placement' => 'left_side',
                'format' => 'banner',
                'is_active' => true,
            ],
            [
                'name' => 'Right Side Promotion',
                'content' => '<img src="https://picsum.photos/seed/rightside/300/250.jpg" alt="Right Side Offer">',
                'target_url' => 'https://rightside.example.com',
                'placement' => 'right_side',
                'format' => 'banner',
                'is_active' => true,
            ],
            [
                'name' => 'Before Counter Ad',
                'content' => '<img src="https://picsum.photos/seed/beforecounter/400/200.jpg" alt="Before Counter Promotion">',
                'target_url' => 'https://beforecounter.example.com',
                'placement' => 'before_counter',
                'format' => 'banner',
                'is_active' => true,
            ],
            [
                'name' => 'Under Counter Ad',
                'content' => '<img src="https://picsum.photos/seed/undercounter/400/200.jpg" alt="Under Counter Promotion">',
                'target_url' => 'https://undercounter.example.com',
                'placement' => 'under_counter',
                'format' => 'banner',
                'is_active' => true,
            ],
            [
                'name' => 'Above Button Ad',
                'content' => '<img src="https://picsum.photos/seed/abovebutton/300/150.jpg" alt="Above Button Promotion">',
                'target_url' => 'https://abovebutton.example.com',
                'placement' => 'above_button',
                'format' => 'banner',
                'is_active' => true,
            ],
            [
                'name' => 'Under Button Ad',
                'content' => '<img src="https://picsum.photos/seed/underbutton/300/150.jpg" alt="Under Button Promotion">',
                'target_url' => 'https://underbutton.example.com',
                'placement' => 'under_button',
                'format' => 'banner',
                'is_active' => true,
            ],
            [
                'name' => 'Popup Promotion',
                'content' => '<img src="https://picsum.photos/seed/popup/500/300.jpg" alt="Special Popup Offer">',
                'target_url' => 'https://popup.example.com',
                'placement' => 'popup',
                'format' => 'banner',
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
