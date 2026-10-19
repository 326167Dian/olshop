@php
    // Diambil dari Setting > Input Pixel (fallback META_PIXEL_ID di .env)
    $metaPixelId = \App\Support\MetaPixel::id();
@endphp
@if ($metaPixelId)
    <!-- Meta Pixel Code -->
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', @json($metaPixelId));
        fbq('track', 'PageView');

        {{-- Event dari controller (AddToCart, InitiateCheckout, Purchase, CompleteRegistration) --}}
        @foreach (\App\Support\MetaPixel::pull() as $ev)
            fbq('track', @json($ev['event']), @json((object) ($ev['params'] ?? [])){!! isset($ev['eventID']) ? ', ' . json_encode(['eventID' => $ev['eventID']]) : '' !!});
        @endforeach
    </script>
    <noscript><img height="1" width="1" style="display:none" alt=""
        src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1" /></noscript>
    <!-- End Meta Pixel Code -->
@endif
