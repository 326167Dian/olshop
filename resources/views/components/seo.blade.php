{{-- Auto SEO: diisi lewat seo()->... di controller. Lihat app/Services/Seo/SeoManager.php --}}
<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
<link rel="canonical" href="{{ $seo['canonical'] }}">
<meta name="robots" content="{{ $seo['noindex'] ? 'noindex, follow' : 'index, follow, max-image-preview:large' }}">

{{-- Open Graph: preview saat link dibagikan di WhatsApp / Facebook --}}
<meta property="og:type" content="{{ $seo['og_type'] }}">
<meta property="og:site_name" content="{{ $seo['site_name'] }}">
<meta property="og:title" content="{{ $seo['title'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:url" content="{{ $seo['canonical'] }}">
<meta property="og:locale" content="id_ID">
@if ($seo['image'])
<meta property="og:image" content="{{ $seo['image'] }}">
@endif

<meta name="twitter:card" content="{{ $seo['image'] ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seo['title'] }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
@if ($seo['image'])
<meta name="twitter:image" content="{{ $seo['image'] }}">
@endif

@foreach ($seo['schemas'] as $schema)
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endforeach
