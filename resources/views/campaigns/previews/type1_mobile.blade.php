<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $campaignTitle }} (Mobile)</title>
    <link href="/css/campaigns/type1_mobile.css" rel="stylesheet" type="text/css">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            background: #eef2f7;
        }

        .campaign-preview-shell {
            margin: 0 auto;
            max-width: 430px;
            padding: 12px 8px;
        }

        .campaign-preview-shell a {
            pointer-events: none;
        }

        .campaign-preview-demo-banner {
            align-items: flex-start;
            background: linear-gradient(125deg, #2a5f92 0%, #5cb2ba 55%, #8ed7c9 100%);
            border-radius: 6px;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-height: 96px;
            padding: 12px;
        }

        .campaign-preview-demo-banner__title {
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            margin: 0;
        }

        .campaign-preview-demo-banner__subtitle {
            font-size: 0.8rem;
            margin: 0;
            opacity: 0.95;
        }

        .campaign-preview-demo-banner__tag {
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 9999px;
            font-size: 0.68rem;
            padding: 4px 10px;
        }
    </style>
</head>
<body>
<div class="campaign-preview-root campaign-preview-shell">
    <div class="hide_menu campaign-type1 campaign-type1--mobile">
        <div class="campaign-type1__inner">
            <div class="campaign-type1__banner shadow-sm">
                <div class="campaign-preview-demo-banner" role="img" aria-label="{{ $campaignTitle }}">
                    <p class="campaign-preview-demo-banner__title">{{ $campaignTitle }}</p>
                    <p class="campaign-preview-demo-banner__subtitle">活動版型示範 Banner（預覽專用）</p>
                    <span class="campaign-preview-demo-banner__tag">MOBILE PREVIEW</span>
                </div>
            </div>

            <section class="campaign-type1__section shadow-sm">
                <header class="campaign-type1__section-head">
                    <h3 class="campaign-type1__title">{{ $primaryTitle }}</h3>
                </header>

                <div class="campaign-type1__intro">
                    <p>{{ $primaryIntro }}</p>
                </div>

                <div class="campaign-type1__mobile-list">
                    @foreach ($primaryItems as $item)
                        <article class="campaign-type1__card campaign-type1__card--mobile">
                            <div class="campaign-type1__image-link">
                                <img
                                    class="campaign-thumb"
                                    src="{{ $item['image_url'] }}"
                                    alt="{{ $item['title'] }}"
                                    title="{{ $item['title'] }}"
                                >
                            </div>
                            <div class="campaign-type1__content">
                                <h4 class="campaign-title line_clamp row_3">{{ $item['title'] }}</h4>
                                <ul class="list_details campaign-type1__summary">
                                    <li class="line_clamp row_1">{{ $item['author'] }}</li>
                                    <li>售價 {{ number_format((float) $item['price']) }}</li>
                                </ul>
                                <p class="campaign-type1__description line_clamp row_3">{{ $item['summary'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="campaign-type1__section shadow-sm">
                <header class="campaign-type1__section-head">
                    <h3 class="campaign-type1__title">{{ $secondaryTitle }}</h3>
                </header>

                <div class="campaign-type1__intro">
                    <p>{{ $secondaryIntro }}</p>
                </div>

                <div class="campaign-type1__mobile-list">
                    @foreach ($secondaryItems as $item)
                        <article class="campaign-type1__card campaign-type1__card--mobile">
                            <div class="campaign-type1__image-link">
                                <img
                                    class="campaign-thumb"
                                    src="{{ $item['image_url'] }}"
                                    alt="{{ $item['title'] }}"
                                    title="{{ $item['title'] }}"
                                >
                            </div>
                            <div class="campaign-type1__content">
                                <h4 class="campaign-title line_clamp row_3">{{ $item['title'] }}</h4>
                                <ul class="list_details campaign-type1__summary">
                                    <li class="line_clamp row_1">{{ $item['author'] }}</li>
                                    <li>售價 {{ number_format((float) $item['price']) }}</li>
                                </ul>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="campaign-type1__section shadow-sm">
                <header class="campaign-type1__section-head">
                    <h3 class="campaign-type1__title">{{ $additionalTitle }}</h3>
                </header>

                <div class="campaign-type1__intro">
                    <p>{{ $additionalIntro }}</p>
                </div>

                <div class="campaign-type1__additional-grid">
                    @foreach ($additionalItems as $item)
                        <article class="campaign-type1__card campaign-type1__card--additional">
                            <div class="campaign-type1__image-link">
                                <img
                                    class="campaign-thumb campaign-thumb--small"
                                    src="{{ $item['image_url'] }}"
                                    alt="{{ $item['title'] }}"
                                    title="{{ $item['title'] }}"
                                >
                            </div>
                            <div class="campaign-type1__content">
                                <h4 class="campaign-title line_clamp row_3">{{ $item['title'] }}</h4>
                                <ul class="list_details campaign-type1__summary">
                                    <li class="line_clamp row_1">{{ $item['author'] }}</li>
                                    <li>售價 {{ number_format((float) $item['price']) }}</li>
                                </ul>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</div>
</body>
</html>
