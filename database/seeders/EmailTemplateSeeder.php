<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Common Styles for Silverchannel Brand
        // Primary: #0ea5e9 (Sky Blue)
        // Secondary: #eab308 (Gold/Yellow)
        // Dark Mode support included
        $commonStyles = "
            body { margin: 0; padding: 0; background-color: #f4f4f7; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; color: #1f2937; }
            table { border-spacing: 0; border-collapse: collapse; width: 100%; }
            td { padding: 0; }
            img { border: 0; }
            a { text-decoration: none; color: #0ea5e9; }
            
            /* Wrapper & Container */
            .wrapper { width: 100%; table-layout: fixed; background-color: #f4f4f7; padding-bottom: 40px; }
            .webkit { max-width: 600px; background-color: #ffffff; margin: 0 auto; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
            .outer { margin: 0 auto; width: 100%; max-width: 600px; }
            
            /* Header */
            .header { background-color: #111827; padding: 30px; text-align: center; }
            .header-title { color: #ffffff; margin: 0; font-size: 24px; font-weight: bold; }
            .header-subtitle { color: #9ca3af; margin: 10px 0 0 0; font-size: 16px; }
            
            /* Content */
            .content { padding: 30px; }
            
            /* Typography */
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .text-gray { color: #6b7280; }
            .text-dark { color: #111827; }
            .text-primary { color: #0ea5e9; }
            .text-secondary { color: #eab308; }
            .text-sm { font-size: 14px; }
            .text-lg { font-size: 18px; }
            .font-bold { font-weight: bold; }
            
            /* Buttons */
            .btn { display: inline-block; background-color: #0ea5e9; color: #ffffff !important; font-size: 16px; font-weight: bold; text-decoration: none; padding: 12px 30px; border-radius: 6px; text-transform: uppercase; margin-top: 20px; transition: background-color 0.3s; }
            .btn:hover { background-color: #0284c7; }
            
            /* Tables */
            .product-table th { text-align: left; padding: 10px; border-bottom: 2px solid #e5e7eb; color: #4b5563; font-size: 12px; text-transform: uppercase; }
            .product-table td { padding: 15px 10px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
            
            /* Info Boxes */
            .info-box { background-color: #f9fafb; border-radius: 6px; padding: 20px; margin-bottom: 30px; }
            
            /* Footer */
            .footer { padding: 20px; text-align: center; color: #9ca3af; font-size: 12px; }
            .footer a { color: #9ca3af; text-decoration: underline; }
            
            /* Mobile Responsive */
            @media only screen and (max-width: 600px) {
                .content { padding: 20px; }
                .mobile-block { display: block !important; width: 100% !important; margin-bottom: 20px; }
                .mobile-no-padding { padding-right: 0 !important; }
            }
            
            /* Dark Mode */
            @media (prefers-color-scheme: dark) {
                body, .wrapper { background-color: #1f2937 !important; }
                .webkit { background-color: #111827 !important; border: 1px solid #374151; }
                .text-dark { color: #f3f4f6 !important; }
                .text-gray { color: #9ca3af !important; }
                .info-box { background-color: #1f2937 !important; border: 1px solid #374151; }
                .product-table th { border-bottom-color: #374151; color: #9ca3af; }
                .product-table td { border-bottom-color: #374151; color: #e5e7eb; }
            }
        ";

        // 1. Order Confirmation Template
        EmailTemplate::updateOrCreate(
            ['key' => 'order_confirmation'],
            [
                'name' => 'Order Confirmation',
                'subject' => 'Order Confirmation #{{order_number}} - {{app_name}}',
                'body' => '
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
' . $commonStyles . '
</style>
</head>
<body>
    <div class="wrapper">
        <div class="outer">
            <!-- Logo -->
            <div style="padding: 20px 0; text-align: center;">
                <img src="{{logo_url}}" alt="{{app_name}}" width="150" style="display: inline-block;">
            </div>

            <div class="webkit">
                <!-- Header -->
                <div class="header">
                    <h1 class="header-title">Order Confirmed!</h1>
                    <p class="header-subtitle">Thanks for your purchase, {{customer_name}}</p>
                </div>

                <div class="content">
                    <p class="text-dark" style="font-size: 16px; line-height: 1.5; margin-bottom: 20px;">
                        We are getting your order ready to be shipped. We will notify you when it has been sent.
                    </p>
                    
                    <!-- Order Info -->
                    <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 20px;">
                        <tr>
                            <td style="padding-bottom: 10px;">
                                <span class="text-gray text-sm">Order Number:</span><br>
                                <strong class="text-secondary text-lg">#{{order_number}}</strong>
                            </td>
                            <td style="padding-bottom: 10px; text-align: right;">
                                <span class="text-gray text-sm">Order Date:</span><br>
                                <strong class="text-dark" style="font-size: 16px;">{{order_date}}</strong>
                            </td>
                        </tr>
                    </table>

                    <!-- Product List -->
                    <div style="margin-bottom: 20px;">
                        <table class="product-table" width="100%">
                            <thead>
                                <tr>
                                    <th width="60%">Product</th>
                                    <th width="15%" class="text-center">Qty</th>
                                    <th width="25%" class="text-right">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{product_list}}
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 30px;">
                        <tr>
                            <td align="right" style="padding-top: 10px;">
                                <span class="text-gray">Total Amount:</span>
                                <strong class="text-primary" style="font-size: 20px; margin-left: 10px;">{{total_amount}}</strong>
                            </td>
                        </tr>
                    </table>

                    <!-- Payment & Shipping Info -->
                    <div class="info-box">
                        <table width="100%" cellspacing="0" cellpadding="0">
                            <tr>
                                <td width="50%" class="mobile-block" style="vertical-align: top; padding-right: 10px;">
                                    <h3 class="text-dark" style="margin: 0 0 10px 0; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px;">Payment</h3>
                                    <p class="text-gray" style="margin: 0; font-size: 14px; line-height: 1.4;">
                                        Method: <strong class="text-dark">{{payment_method}}</strong><br>
                                        Status: <span style="color: #10b981; font-weight: bold;">Paid</span>
                                    </p>
                                </td>
                                <td width="50%" class="mobile-block" style="vertical-align: top; padding-left: 10px;">
                                    <h3 class="text-dark" style="margin: 0 0 10px 0; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px;">Shipping</h3>
                                    <p class="text-gray" style="margin: 0; font-size: 14px; line-height: 1.4;">
                                        Courier: <strong class="text-dark">{{shipping_courier}}</strong><br>
                                        Est. Delivery: <span class="text-dark">{{shipping_estimation}}</span>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- CTA Button -->
                    <div class="text-center">
                        <a href="{{tracking_url}}" class="btn">Track My Order</a>
                    </div>

                    <!-- Related Products -->
                    <div style="margin-top: 40px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
                        <h3 class="text-dark" style="font-size: 18px; margin-top: 0; margin-bottom: 15px;">You Might Also Like</h3>
                        <table width="100%" cellspacing="0" cellpadding="0">
                            <tr>
                                <td width="33%" class="mobile-block mobile-no-padding" style="padding-right: 10px; vertical-align: top;">
                                    <div style="background-color: #f3f4f6; height: 100px; border-radius: 4px; margin-bottom: 8px;">
                                        <!-- Placeholder for Product Image -->
                                        <img src="https://via.placeholder.com/150?text=Product" alt="Product" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;">
                                    </div>
                                    <p class="text-dark" style="margin: 0; font-size: 14px; font-weight: bold;">Gold Ring 24K</p>
                                    <p class="text-primary" style="margin: 0; font-size: 14px;">Rp 2.500.000</p>
                                </td>
                                <td width="33%" class="mobile-block mobile-no-padding" style="padding-right: 10px; vertical-align: top;">
                                    <div style="background-color: #f3f4f6; height: 100px; border-radius: 4px; margin-bottom: 8px;">
                                        <img src="https://via.placeholder.com/150?text=Product" alt="Product" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;">
                                    </div>
                                    <p class="text-dark" style="margin: 0; font-size: 14px; font-weight: bold;">Silver Necklace</p>
                                    <p class="text-primary" style="margin: 0; font-size: 14px;">Rp 750.000</p>
                                </td>
                                <td width="33%" class="mobile-block" style="vertical-align: top;">
                                    <div style="background-color: #f3f4f6; height: 100px; border-radius: 4px; margin-bottom: 8px;">
                                        <img src="https://via.placeholder.com/150?text=Product" alt="Product" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;">
                                    </div>
                                    <p class="text-dark" style="margin: 0; font-size: 14px; font-weight: bold;">Diamond Earring</p>
                                    <p class="text-primary" style="margin: 0; font-size: 14px;">Rp 5.000.000</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Customer Service -->
                <div style="background-color: #0ea5e9; padding: 30px; text-align: center;">
                    <h3 style="color: #ffffff; margin: 0 0 10px 0;">Need Help?</h3>
                    <p style="color: #ffffff; margin: 0; font-size: 14px;">
                        Contact our support team at <a href="mailto:{{support_email}}" style="color: #ffffff; text-decoration: underline;">{{support_email}}</a> or call us at {{support_phone}}.
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>
                    &copy; {{year}} {{app_name}}. All rights reserved.<br>
                    Jalan Emas Perak No. 123, Jakarta, Indonesia
                </p>
                <p>
                    <a href="#">Privacy Policy</a> | 
                    <a href="#">Terms of Service</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>',
                'variables' => [
                    'logo_url', 'app_name', 'customer_name', 'order_number', 'order_date', 
                    'product_list', 'total_amount', 'payment_method', 'shipping_courier', 
                    'shipping_estimation', 'tracking_url', 'support_email', 'support_phone', 'year'
                ],
                'is_active' => true,
                'type' => 'order_confirmation',
            ]
        );

        // 2. Forgot Password Template
        EmailTemplate::updateOrCreate(
            ['key' => 'forgot_password'],
            [
                'name' => 'Forgot Password',
                'subject' => 'Reset Password Notification - {{app_name}}',
                'body' => '
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
' . $commonStyles . '
</style>
</head>
<body>
    <div class="wrapper">
        <div class="outer">
            <!-- Logo -->
            <div style="padding: 20px 0; text-align: center;">
                <img src="{{logo_url}}" alt="{{app_name}}" width="150" style="display: inline-block;">
            </div>

            <div class="webkit">
                <!-- Header -->
                <div class="header">
                    <h1 class="header-title">Reset Password</h1>
                </div>

                <div class="content text-center">
                    <p class="text-dark" style="font-size: 16px; line-height: 1.5; margin-bottom: 20px;">
                        Hello {{name}},<br>
                        You are receiving this email because we received a password reset request for your account.
                    </p>
                    
                    <div class="text-center">
                        <a href="{{reset_url}}" class="btn">Reset Password</a>
                    </div>

                    <p class="text-gray" style="font-size: 14px; margin-top: 30px;">
                        This password reset link will expire in {{count}} minutes.
                    </p>
                    <p class="text-gray" style="font-size: 14px;">
                        If you did not request a password reset, no further action is required.
                    </p>
                    
                    <div style="margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 20px; font-size: 12px; color: #9ca3af; word-break: break-all;">
                        If you\'re having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:
                        <br>
                        <a href="{{reset_url}}" style="color: #0ea5e9;">{{reset_url}}</a>
                    </div>
                </div>

                <!-- Customer Service -->
                <div class="info-box" style="margin: 0; border-radius: 0 0 8px 8px; border: none;">
                    <p class="text-gray text-center" style="margin: 0; font-size: 14px;">
                        Need help? Contact <a href="mailto:{{support_email}}" class="text-primary">{{support_email}}</a>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>
                    &copy; {{year}} {{app_name}}. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>',
                'variables' => [
                    'logo_url', 'app_name', 'name', 'reset_url', 'count', 'support_email', 'year'
                ],
                'is_active' => true,
                'type' => 'forgot_password',
            ]
        );

        // 3. Welcome Silverchannel Template
        EmailTemplate::updateOrCreate(
            ['key' => 'welcome_silverchannel'],
            [
                'name' => 'Welcome Silverchannel',
                'subject' => 'Welcome to {{app_name}}! Your Account is Ready',
                'body' => '
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
' . $commonStyles . '
</style>
</head>
<body>
    <div class="wrapper">
        <div class="outer">
            <!-- Logo -->
            <div style="padding: 20px 0; text-align: center;">
                <img src="{{logo_url}}" alt="{{app_name}}" width="150" style="display: inline-block;">
            </div>

            <div class="webkit">
                <!-- Header -->
                <div class="header">
                    <h1 class="header-title">Welcome Aboard!</h1>
                    <p class="header-subtitle">We are excited to have you as a Silverchannel partner.</p>
                </div>

                <div class="content">
                    <p class="text-dark" style="font-size: 16px; line-height: 1.5; margin-bottom: 20px;">
                        Hello {{name}},
                    </p>
                    <p class="text-dark" style="font-size: 16px; line-height: 1.5; margin-bottom: 20px;">
                        Congratulations! Your application to join <strong>{{app_name}}</strong> as a Silverchannel distributor has been approved. You now have full access to our catalog, exclusive pricing, and order management system.
                    </p>

                    <div class="info-box">
                        <h3 class="text-dark" style="margin: 0 0 10px 0; font-size: 16px;">What\'s Next?</h3>
                        <ul class="text-gray" style="margin: 0; padding-left: 20px; font-size: 14px; line-height: 1.6;">
                            <li>Log in to your dashboard to view the product catalog.</li>
                            <li>Set up your profile and shipping preferences.</li>
                            <li>Start placing orders and earning commissions!</li>
                        </ul>
                    </div>
                    
                    <div class="text-center">
                        <a href="{{login_url}}" class="btn">Login to Dashboard</a>
                    </div>

                    <p class="text-gray" style="font-size: 14px; margin-top: 30px; text-align: center;">
                        If you have any questions or need assistance getting started, our support team is here to help.
                    </p>
                </div>

                <!-- Customer Service -->
                <div style="background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
                    <p class="text-gray" style="margin: 0; font-size: 14px;">
                        Contact Support: <a href="mailto:{{support_email}}" class="text-primary">{{support_email}}</a>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>
                    &copy; {{year}} {{app_name}}. All rights reserved.<br>
                    Jalan Emas Perak No. 123, Jakarta, Indonesia
                </p>
                <p>
                    <a href="#">Privacy Policy</a> | 
                    <a href="#">Terms of Service</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>',
                'variables' => [
                    'logo_url', 'app_name', 'name', 'login_url', 'support_email', 'year'
                ],
                'is_active' => true,
                'type' => 'welcome_silverchannel',
            ]
        );

        EmailTemplate::updateOrCreate(
            ['key' => 'registration_approved'],
            [
                'name' => 'Registration Approved',
                'subject' => 'Pendaftaran Diterima — Akun Anda Aktif',
                'body' => '
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
' . $commonStyles . '
</style>
</head>
<body>
    <div class="wrapper">
        <div class="outer">
            <div style="padding: 20px 0; text-align: center;">
                <img src="{{logo_url}}" alt="{{app_name}}" width="150" style="display: inline-block;">
            </div>
            <div class="webkit">
                <div class="header">
                    <h1 class="header-title">Pendaftaran Diterima</h1>
                    <p class="header-subtitle">Akun Silverchannel Anda telah aktif.</p>
                </div>
                <div class="content">
                    <p class="text-dark" style="font-size: 16px; line-height: 1.6;">
                        Halo {{name}},<br>
                        Selamat! Pendaftaran Anda sebagai Silverchannel di <strong>{{app_name}}</strong> telah diterima dan akun Anda aktif.
                    </p>
                    <div class="info-box">
                        <table width="100%" cellspacing="0" cellpadding="0">
                            <tr>
                                <td width="50%" class="mobile-block" style="vertical-align: top; padding-right: 10px;">
                                    <h3 class="text-dark" style="margin: 0 0 10px 0; font-size: 16px;">Detail Akun</h3>
                                    <p class="text-gray" style="margin: 0; font-size: 14px; line-height: 1.6;">
                                        ID Silverchannel: <strong class="text-dark">{{silver_channel_id}}</strong><br>
                                        Kode Referral: <strong class="text-dark">{{referral_code}}</strong><br>
                                        No. Order Pendaftaran: <strong class="text-dark">{{order_number}}</strong>
                                    </p>
                                </td>
                                <td width="50%" class="mobile-block" style="vertical-align: top; padding-left: 10px;">
                                    <h3 class="text-dark" style="margin: 0 0 10px 0; font-size: 16px;">Langkah Selanjutnya</h3>
                                    <ul class="text-gray" style="margin: 0; padding-left: 20px; font-size: 14px; line-height: 1.6;">
                                        <li>Login ke dashboard untuk mulai berjualan.</li>
                                        <li>Lengkapi profil dan preferensi pengiriman.</li>
                                        <li>Lihat katalog dan harga khusus Silverchannel.</li>
                                    </ul>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="text-center">
                        <a href="{{login_url}}" class="btn">Masuk Dashboard</a>
                    </div>
                    <p class="text-gray" style="font-size: 14px; margin-top: 30px; text-align: center;">
                        Jika membutuhkan bantuan, hubungi tim support kami: <a href="mailto:{{support_email}}" class="text-primary">{{support_email}}</a>
                    </p>
                </div>
                <div style="background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
                    <p class="text-gray" style="margin: 0; font-size: 14px;">
                        Terima kasih telah bergabung dengan {{app_name}}.
                    </p>
                </div>
            </div>
            <div class="footer">
                <p>&copy; {{year}} {{app_name}}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>',
                'variables' => [
                    'logo_url', 'app_name', 'name', 'login_url', 'support_email', 'year', 'order_number', 'silver_channel_id', 'referral_code'
                ],
                'is_active' => true,
                'type' => 'registration_approved',
            ]
        );

        // 4. Order Registration Template
        EmailTemplate::updateOrCreate(
            ['key' => 'order_registration'],
            [
                'name' => 'Order Registration',
                'subject' => 'Registration Package Order #{{order_number}} - {{app_name}}',
                'body' => '
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
' . $commonStyles . '
</style>
</head>
<body>
    <div class="wrapper">
        <div class="outer">
            <div style="padding: 20px 0; text-align: center;">
                <img src="{{logo_url}}" alt="{{app_name}}" width="150" style="display: inline-block;">
            </div>
            <div class="webkit">
                <div class="header">
                    <h1 class="header-title">Welcome Aboard!</h1>
                    <p class="header-subtitle">Your Registration Package is Confirmed</p>
                </div>
                <div class="content">
                    <p class="text-dark" style="font-size: 16px; line-height: 1.5; margin-bottom: 20px;">
                        Hi {{customer_name}},<br>
                        Thank you for joining us! Your registration package order has been received and is being processed.
                    </p>
                    
                    <div class="info-box">
                         <span class="text-gray text-sm">Order Number:</span><br>
                         <strong class="text-secondary text-lg">#{{order_number}}</strong><br>
                         <span class="text-gray text-sm">Total:</span> <strong class="text-primary">{{total_amount}}</strong>
                    </div>

                    <div class="text-center">
                        <a href="{{login_url}}" class="btn">Login to Dashboard</a>
                    </div>
                </div>
                <div class="footer">
                    <p>&copy; {{year}} {{app_name}}. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>',
                'variables' => [
                    'logo_url', 'app_name', 'customer_name', 'order_number', 'total_amount', 'login_url', 'year'
                ],
                'is_active' => true,
                'type' => 'order_registration',
            ]
        );

        // 5. Marketing Campaign Template (Base)
        EmailTemplate::updateOrCreate(
            ['key' => 'marketing_campaign'],
            [
                'name' => 'Marketing Campaign',
                'subject' => '{{campaign_subject}}',
                'body' => '
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
' . $commonStyles . '
</style>
</head>
<body>
    <div class="wrapper">
        <div class="outer">
            <div style="padding: 20px 0; text-align: center;">
                <img src="{{logo_url}}" alt="{{app_name}}" width="150" style="display: inline-block;">
            </div>
            <div class="webkit">
                <div class="header">
                    <h1 class="header-title">{{campaign_title}}</h1>
                </div>
                <div class="content">
                    <div class="text-dark" style="font-size: 16px; line-height: 1.5; margin-bottom: 20px;">
                        {{campaign_content}}
                    </div>
                    
                    <div class="text-center">
                        <a href="{{cta_url}}" class="btn">{{cta_text}}</a>
                    </div>
                </div>
                <div class="footer">
                    <p>
                        <a href="{{unsubscribe_url}}">Unsubscribe</a>
                    </p>
                    <p>&copy; {{year}} {{app_name}}. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>',
                'variables' => [
                    'logo_url', 'app_name', 'campaign_title', 'campaign_content', 'cta_url', 'cta_text', 'unsubscribe_url', 'year'
                ],
                'is_active' => true,
                'type' => 'marketing_campaign',
            ]
        );

        // Order status templates (waiting verification, paid, packing, shipped, delivered)
        EmailTemplate::updateOrCreate(
            ['key' => 'order_status_waiting_verification'],
            [
                'name' => 'Order Waiting Verification',
                'subject' => 'Order #{{order_number}} Menunggu Verifikasi',
                'body' => '<html><body>Order Anda #{{order_number}} sedang menunggu verifikasi pembayaran. Total: {{total_amount}}.<br>Terima kasih.</body></html>',
                'variables' => ['order_number','total_amount'],
                'is_active' => true,
                'type' => 'order_status_waiting_verification',
            ]
        );

        EmailTemplate::updateOrCreate(
            ['key' => 'order_status_paid'],
            [
                'name' => 'Order Paid',
                'subject' => 'Pembayaran Order #{{order_number}} Diterima',
                'body' => '<html><body>Pembayaran untuk Order #{{order_number}} telah diterima. Total: {{total_amount}}.<br>Invoice terlampir.</body></html>',
                'variables' => ['order_number','total_amount'],
                'is_active' => true,
                'type' => 'order_status_paid',
            ]
        );

        EmailTemplate::updateOrCreate(
            ['key' => 'order_status_packing'],
            [
                'name' => 'Order Packing',
                'subject' => 'Order #{{order_number}} Sedang Dikemas',
                'body' => '<html><body>Order Anda #{{order_number}} sedang dikemas dan akan segera dikirim.</body></html>',
                'variables' => ['order_number'],
                'is_active' => true,
                'type' => 'order_status_packing',
            ]
        );

        EmailTemplate::updateOrCreate(
            ['key' => 'order_status_shipped'],
            [
                'name' => 'Order Shipped',
                'subject' => 'Order #{{order_number}} Telah Dikirim',
                'body' => '<html><body>Order #{{order_number}} telah dikirim dengan kurir {{shipping_courier}}. Cek status di dashboard.</body></html>',
                'variables' => ['order_number','shipping_courier'],
                'is_active' => true,
                'type' => 'order_status_shipped',
            ]
        );

        EmailTemplate::updateOrCreate(
            ['key' => 'order_status_delivered'],
            [
                'name' => 'Order Delivered',
                'subject' => 'Order #{{order_number}} Telah Diterima',
                'body' => '<html><body>Order #{{order_number}} telah diterima. Terima kasih telah berbelanja.</body></html>',
                'variables' => ['order_number'],
                'is_active' => true,
                'type' => 'order_status_delivered',
            ]
        );

        EmailTemplate::updateOrCreate(
            ['key' => 'order_status_cancelled'],
            [
                'name' => 'Order Cancelled',
                'subject' => 'Order #{{order_number}} Dibatalkan',
                'body' => '<html><body>Order #{{order_number}} telah dibatalkan. Jika ini tidak sesuai, silakan hubungi support.</body></html>',
                'variables' => ['order_number'],
                'is_active' => true,
                'type' => 'order_status_cancelled',
            ]
        );

        EmailTemplate::updateOrCreate(
            ['key' => 'order_status_returned'],
            [
                'name' => 'Order Returned',
                'subject' => 'Order #{{order_number}} Dikembalikan',
                'body' => '<html><body>Order #{{order_number}} sedang dalam proses pengembalian. Tim kami akan menghubungi Anda untuk tindak lanjut.</body></html>',
                'variables' => ['order_number'],
                'is_active' => true,
                'type' => 'order_status_returned',
            ]
        );

        EmailTemplate::updateOrCreate(
            ['key' => 'order_status_refunded'],
            [
                'name' => 'Order Refunded',
                'subject' => 'Order #{{order_number}} Dikembalikan Dana',
                'body' => '<html><body>Pengembalian dana untuk Order #{{order_number}} telah diproses. Silakan cek rekening/akun Anda.</body></html>',
                'variables' => ['order_number'],
                'is_active' => true,
                'type' => 'order_status_refunded',
            ]
        );

        // Payment verification result
        EmailTemplate::updateOrCreate(
            ['key' => 'payment_rejected'],
            [
                'name' => 'Payment Rejected',
                'subject' => 'Pembayaran Ditolak untuk Order #{{order_number}}',
                'body' => '<html><body>Pembayaran untuk Order #{{order_number}} ditolak. Alasan: {{reason}}.<br>Silakan unggah ulang bukti atau hubungi admin.</body></html>',
                'variables' => ['order_number','reason'],
                'is_active' => true,
                'type' => 'payment_rejected',
            ]
        );

        // Commissions
        EmailTemplate::updateOrCreate(
            ['key' => 'commission_registration_awarded'],
            [
                'name' => 'Registration Commission Awarded',
                'subject' => 'Komisi Referral: {{referred_name}}',
                'body' => '<html><body>Selamat! Anda menerima komisi pendaftaran dari {{referred_name}} ({{package_name}}) sebesar {{amount}}. Saldo langsung tersedia.</body></html>',
                'variables' => ['referred_name','package_name','amount'],
                'is_active' => true,
                'type' => 'commission_registration_awarded',
            ]
        );

        EmailTemplate::updateOrCreate(
            ['key' => 'commission_transaction_pending'],
            [
                'name' => 'Transaction Commission Pending',
                'subject' => 'Komisi Order #{{order_number}} Menunggu',
                'body' => '<html><body>Komisi transaksi dari pembelian {{buyer_name}} (Order #{{order_number}}) akan tersedia dalam {{holding_days}} hari, pada {{available_date}}.</body></html>',
                'variables' => ['order_number','buyer_name','holding_days','available_date'],
                'is_active' => true,
                'type' => 'commission_transaction_pending',
            ]
        );

        // Payouts
        EmailTemplate::updateOrCreate(
            ['key' => 'payout_requested'],
            [
                'name' => 'Payout Requested',
                'subject' => 'Permintaan Pencairan {{payout_number}}',
                'body' => '<html><body>Permintaan pencairan {{payout_number}} sebesar {{amount}} telah diterima dan akan segera diproses.</body></html>',
                'variables' => ['payout_number','amount'],
                'is_active' => true,
                'type' => 'payout_requested',
            ]
        );

        EmailTemplate::updateOrCreate(
            ['key' => 'payout_processed'],
            [
                'name' => 'Payout Processed',
                'subject' => 'Pencairan {{payout_number}} Diproses',
                'body' => '<html><body>Pencairan {{payout_number}} sebesar {{amount}} sudah diproses. Kwitansi terlampir.</body></html>',
                'variables' => ['payout_number','amount'],
                'is_active' => true,
                'type' => 'payout_processed',
            ]
        );

        EmailTemplate::updateOrCreate(
            ['key' => 'payout_rejected'],
            [
                'name' => 'Payout Rejected',
                'subject' => 'Pencairan {{payout_number}} Ditolak',
                'body' => '<html><body>Pencairan {{payout_number}} ditolak. Alasan: {{reason}}. Silakan ajukan ulang.</body></html>',
                'variables' => ['payout_number','reason'],
                'is_active' => true,
                'type' => 'payout_rejected',
            ]
        );
    }
}
