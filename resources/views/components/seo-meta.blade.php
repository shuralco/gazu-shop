@props([
    'model' => null,
    'title' => null,
    'description' => null,
    'keywords' => null,
    'canonical' => null,
    'robots' => null,
    'language' => 'uk',
    'pageType' => 'website'
])

@php
    $seoTitle = $title;
    $seoDescription = $description;
    $seoKeywords = $keywords;
    $canonicalUrl = $canonical ?? request()->url();
    $robotsDirective = $robots ?? config('seo.meta.robots', 'index,follow');
    $ogData = [];
    $twitterData = [];
    $structuredData = [];
    $faqSchema = [];

    if ($model && method_exists($model, 'getSeoTitle')) {
        $seoTitle = $seoTitle ?? $model->getSeoTitle($language);
        $seoDescription = $seoDescription ?? $model->getSeoDescription($language);
        $seoKeywords = $seoKeywords ?? $model->getSeoKeywords($language);
        $canonicalUrl = $canonical ?? $model->getCanonicalUrl();
        $robotsDirective = $robots ?? $model->getRobotsDirective();
        $ogData = $model->getOpenGraphData($language);
        $twitterData = $model->getTwitterCardData($language);
        $structuredData = $model->getStructuredData();
        $faqSchema = $model->getFaqSchema();
    }

    $finalTitle = $seoTitle ?? config('app.name');
    $siteName = config('app.name', 'SimpleShop');

    if (!str_contains($finalTitle, $siteName)) {
        $finalTitle .= ' | ' . $siteName;
    }
@endphp

{{-- Basic Meta Tags --}}
<meta charset="{{ config('seo.meta.charset', 'UTF-8') }}">
<meta name="viewport" content="{{ config('seo.meta.viewport', 'width=device-width, initial-scale=1') }}">
<meta name="language" content="{{ $language }}">
<meta name="robots" content="{{ $robotsDirective }}">
<meta name="generator" content="{{ config('seo.meta.generator', 'Laravel 12 + SimpleShop') }}">

{{-- SEO Meta Tags --}}
<title>{{ $finalTitle }}</title>
@if($seoDescription)
<meta name="description" content="{{ $seoDescription }}">
@endif
@if($seoKeywords)
<meta name="keywords" content="{{ $seoKeywords }}">
@endif

{{-- Canonical URL --}}
<link rel="canonical" href="{{ $canonicalUrl }}">

{{-- Open Graph Meta Tags --}}
@if(config('seo.open_graph.enabled', true))
<meta property="og:type" content="{{ $ogData['og:type'] ?? $pageType }}">
<meta property="og:title" content="{{ $ogData['og:title'] ?? $finalTitle }}">
@if(isset($ogData['og:description']) || $seoDescription)
<meta property="og:description" content="{{ $ogData['og:description'] ?? $seoDescription }}">
@endif
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:site_name" content="{{ config('seo.open_graph.site_name', $siteName) }}">
<meta property="og:locale" content="{{ config('seo.open_graph.locale', 'uk_UA') }}">
@if(isset($ogData['og:image']))
<meta property="og:image" content="{{ $ogData['og:image'] }}">
<meta property="og:image:width" content="{{ config('seo.open_graph.image_width', 1200) }}">
<meta property="og:image:height" content="{{ config('seo.open_graph.image_height', 630) }}">
@elseif(config('seo.open_graph.default_image'))
<meta property="og:image" content="{{ asset(config('seo.open_graph.default_image')) }}">
<meta property="og:image:width" content="{{ config('seo.open_graph.image_width', 1200) }}">
<meta property="og:image:height" content="{{ config('seo.open_graph.image_height', 630) }}">
@endif
@endif

{{-- Twitter Card Meta Tags --}}
@if(config('seo.twitter.enabled', true))
<meta name="twitter:card" content="{{ $twitterData['twitter:card'] ?? config('seo.twitter.card', 'summary_large_image') }}">
<meta name="twitter:title" content="{{ $twitterData['twitter:title'] ?? $finalTitle }}">
@if(isset($twitterData['twitter:description']) || $seoDescription)
<meta name="twitter:description" content="{{ $twitterData['twitter:description'] ?? $seoDescription }}">
@endif
@if(isset($twitterData['twitter:image']))
<meta name="twitter:image" content="{{ $twitterData['twitter:image'] }}">
@endif
@if(config('seo.twitter.site'))
<meta name="twitter:site" content="{{ config('seo.twitter.site') }}">
@endif
@if(config('seo.twitter.creator'))
<meta name="twitter:creator" content="{{ config('seo.twitter.creator') }}">
@endif
@endif

{{-- Structured Data (JSON-LD) --}}
@if(config('seo.structured_data.enabled', true))
@if(!empty($structuredData))
<script type="application/ld+json">
{!! json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif

{{-- Organization Structured Data --}}
@if(config('seo.structured_data.organization'))
<script type="application/ld+json">
{!! json_encode(config('seo.structured_data.organization'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif

{{-- Website Structured Data --}}
@if(config('seo.structured_data.website'))
<script type="application/ld+json">
{!! json_encode(config('seo.structured_data.website'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif

{{-- FAQ Schema --}}
@if(!empty($faqSchema))
<script type="application/ld+json">
{!! json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
@endif

{{-- Analytics Scripts --}}
@if(config('seo.analytics.google_analytics'))
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ config('seo.analytics.google_analytics') }}"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '{{ config("seo.analytics.google_analytics") }}');
</script>
@endif

@if(config('seo.analytics.google_tag_manager'))
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{{ config("seo.analytics.google_tag_manager") }}');</script>
@endif

@if(config('seo.analytics.facebook_pixel'))
<!-- Facebook Pixel -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window,document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{{ config("seo.analytics.facebook_pixel") }}');
fbq('track', 'PageView');
</script>
<noscript>
<img height="1" width="1" style="display:none" 
     src="https://www.facebook.com/tr?id={{ config('seo.analytics.facebook_pixel') }}&ev=PageView&noscript=1"/>
</noscript>
@endif

@if(config('seo.analytics.yandex_metrika'))
<!-- Yandex.Metrika -->
<script type="text/javascript">
(function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
(window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

ym({{ config('seo.analytics.yandex_metrika') }}, "init", {
     clickmap:true,
     trackLinks:true,
     accurateTrackBounce:true,
     webvisor:true
});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/{{ config('seo.analytics.yandex_metrika') }}" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
@endif