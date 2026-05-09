<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', shopName())</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; background-color: #f5f5f5; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
    <!-- Wrapper -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f5f5f5;">
        <tr>
            <td align="center" style="padding: 24px 16px;">
                <!-- Container -->
                <table role="presentation" cellpadding="0" cellspacing="0" width="600" style="max-width: 600px; width: 100%; background-color: #ffffff;">

                    <!-- Header -->
                    <tr>
                        <td style="background-color: #000000; padding: 32px 40px; text-align: center;">
                            @if(\App\Models\DisplaySetting::get('email_show_logo', true) && \App\Models\DisplaySetting::get('logo_type', 'text') === 'image' && \App\Models\DisplaySetting::get('logo_image'))
                                <img src="{{ asset('storage/' . \App\Models\DisplaySetting::get('logo_image')) }}" alt="{{ shopName() }}" style="max-height: 48px; max-width: 200px;" />
                            @else
                                <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 900; letter-spacing: 3px; text-transform: uppercase;">
                                    {{ shopName() }}
                                </h1>
                            @endif
                        </td>
                    </tr>

                    <!-- Accent line -->
                    <tr>
                        <td style="height: 4px; background-color: #000000; font-size: 0; line-height: 0;">&nbsp;</td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px;">
                            @yield('content')
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #000000; padding: 32px 40px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="text-align: center; padding-bottom: 16px;">
                                        <span style="color: #ffffff; font-size: 14px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;">
                                            {{ shopName() }}
                                        </span>
                                    </td>
                                </tr>
                                @php
                                    $shopPhone = \App\Models\DisplaySetting::get('shop_phone');
                                    $shopEmail = \App\Models\DisplaySetting::get('shop_email');
                                    $shopAddress = \App\Models\DisplaySetting::get('shop_address');
                                    $footerText = \App\Models\DisplaySetting::get('email_footer_text');
                                @endphp
                                @if($shopPhone || $shopEmail)
                                    <tr>
                                        <td style="text-align: center; padding-bottom: 12px;">
                                            @if($shopPhone)
                                                <a href="tel:{{ $shopPhone }}" style="color: #999999; font-size: 13px; text-decoration: none;">
                                                    {{ $shopPhone }}
                                                </a>
                                            @endif
                                            @if($shopPhone && $shopEmail)
                                                <span style="color: #666666; font-size: 13px; margin: 0 8px;">|</span>
                                            @endif
                                            @if($shopEmail)
                                                <a href="mailto:{{ $shopEmail }}" style="color: #999999; font-size: 13px; text-decoration: none;">
                                                    {{ $shopEmail }}
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                @if($shopAddress)
                                    <tr>
                                        <td style="text-align: center; padding-bottom: 12px;">
                                            <span style="color: #999999; font-size: 12px;">{{ $shopAddress }}</span>
                                        </td>
                                    </tr>
                                @endif
                                @if($footerText)
                                    <tr>
                                        <td style="text-align: center; padding-bottom: 12px;">
                                            <span style="color: #999999; font-size: 12px;">{{ $footerText }}</span>
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="text-align: center; border-top: 1px solid #333333; padding-top: 16px;">
                                        <span style="color: #666666; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">
                                            &copy; {{ date('Y') }} {{ shopName() }}. {{ __('general.all_rights') }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
